import React, { createContext, useContext, useState, useEffect, ReactNode, useCallback } from 'react';
import { AppState, AppStateStatus } from 'react-native';
import NetInfo from '@react-native-community/netinfo';
import { callLogSyncService } from '../services/callLogSync';
import { callStateListener } from '../services/callStateListener';
import { CallLogSyncState } from '../types';
import { useAuth } from './AuthContext';
import { useOnboarding } from './OnboardingContext';
import { SYNC_INTERVALS } from '../constants';

interface CallLogContextType extends CallLogSyncState {
  manualSync: () => Promise<void>;
}

const CallLogContext = createContext<CallLogContextType | undefined>(undefined);

interface CallLogProviderProps {
  children: ReactNode;
}

export const CallLogProvider: React.FC<CallLogProviderProps> = ({ children }) => {
  const { isAuthenticated } = useAuth();
  const { isOnboardingCompleted } = useOnboarding();
  const [syncState, setSyncState] = useState<CallLogSyncState>({
    isSyncing: false,
    lastSyncTime: null,
    pendingCount: 0,
    error: null,
  });

  const isSyncingRef = React.useRef(false);
  const wasConnectedRef = React.useRef<boolean | null>(null);

  React.useEffect(() => {
    isSyncingRef.current = syncState.isSyncing;
  }, [syncState.isSyncing]);

  const updateSyncStats = useCallback(async () => {
    try {
      const stats = await callLogSyncService.getSyncStats();
      setSyncState(prev => ({
        ...prev,
        lastSyncTime: stats.lastSyncTime,
        pendingCount: stats.pendingCount,
      }));
    } catch (error) {
      if (__DEV__) {
        console.error('Error updating sync stats:', error);
      }
    }
  }, []);

  const manualSync = useCallback(async () => {
    if (!isAuthenticated) {
      setSyncState(prev => ({ ...prev, error: 'Not authenticated' }));
      return;
    }

    if (syncState.isSyncing) return;

    try {
      setSyncState(prev => ({ ...prev, isSyncing: true, error: null }));

      const result = await callLogSyncService.manualSync();

      await updateSyncStats();
      setSyncState(prev => ({
        ...prev,
        isSyncing: false,
        error: result.success ? null : result.error || 'Sync failed',
      }));
    } catch (err: unknown) {
      const errorMessage = err instanceof Error ? err.message : 'Sync failed';
      if (__DEV__) {
        console.error('Manual sync error:', err);
      }
      setSyncState(prev => ({ ...prev, isSyncing: false, error: errorMessage }));
      throw err;
    }
  }, [isAuthenticated, syncState.isSyncing, updateSyncStats]);

  // Sync unsynced queue on network reconnection
  useEffect(() => {
    if (!isAuthenticated || !isOnboardingCompleted) return;

    const unsubscribe = NetInfo.addEventListener(state => {
      const isNowConnected = !!state.isConnected;
      const wasConnected = wasConnectedRef.current;
      wasConnectedRef.current = isNowConnected;

      const reconnected = isNowConnected && wasConnected === false;
      if (!reconnected || isSyncingRef.current) return;

      setTimeout(async () => {
        try {
          const syncResult = await callLogSyncService.syncUnsyncedLogs();
          await updateSyncStats();

          if (syncResult.success) {
            setSyncState(prev => ({ ...prev, error: null }));
          } else if (syncResult.error) {
            setSyncState(prev => ({ ...prev, error: syncResult.error || null }));
          }
        } catch (err: unknown) {
          const msg = err instanceof Error ? err.message : 'Auto-sync failed';
          setSyncState(prev => ({ ...prev, error: msg }));
        }
      }, SYNC_INTERVALS.NETWORK_RECONNECT_DELAY);
    });

    return () => unsubscribe();
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats]);

  // Process new call logs whenever the app comes to the foreground
  useEffect(() => {
    if (!isAuthenticated || !isOnboardingCompleted) return;

    const handleAppStateChange = (nextAppState: AppStateStatus) => {
      if (nextAppState === 'active') {
        callLogSyncService.processNewCallLogs().then(() => updateSyncStats());
      }
    };

    const subscription = AppState.addEventListener('change', handleAppStateChange);
    return () => subscription.remove();
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats]);

  // Start/stop the call state listener and run initial sync
  useEffect(() => {
    if (!isAuthenticated || !isOnboardingCompleted) {
      callStateListener.stopListening();
      return;
    }

    updateSyncStats();
    callStateListener.startListening();

    const timer = setTimeout(async () => {
      try {
        await callLogSyncService.processNewCallLogs();
        await callLogSyncService.syncUnsyncedLogs();
        await updateSyncStats();
      } catch (error) {
        if (__DEV__) {
          console.error('Initial sync error:', error);
        }
      }
    }, SYNC_INTERVALS.INITIAL_SYNC_DELAY);

    return () => {
      clearTimeout(timer);
      callStateListener.stopListening();
    };
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats]);

  return (
    <CallLogContext.Provider value={{ ...syncState, manualSync }}>
      {children}
    </CallLogContext.Provider>
  );
};

export const useCallLogSync = (): CallLogContextType => {
  const context = useContext(CallLogContext);
  if (context === undefined) {
    throw new Error('useCallLogSync must be used within a CallLogProvider');
  }
  return context;
};

export default CallLogProvider;
