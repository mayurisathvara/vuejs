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
  startAutoSync: () => void;
  stopAutoSync: () => void;
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
  const isMountedRef = React.useRef(true); // LOW PRIORITY BUG FIX: Track mounted state

  // Keep ref in sync with state
  React.useEffect(() => {
    isSyncingRef.current = syncState.isSyncing;
  }, [syncState.isSyncing]);

  // LOW PRIORITY BUG FIX: Set mounted flag and cleanup on unmount
  React.useEffect(() => {
    isMountedRef.current = true;
    return () => {
      isMountedRef.current = false;
    };
  }, []);

  // Update sync stats periodically
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

  // Manual sync function
  const manualSync = useCallback(async () => {
    if (!isAuthenticated) {
      setSyncState(prev => ({ ...prev, error: 'Not authenticated' }));
      return;
    }

    if (syncState.isSyncing) {
      if (__DEV__) {
        console.log('Sync already in progress');
      }
      return;
    }

    try {
      setSyncState(prev => ({ ...prev, isSyncing: true, error: null }));

      const result = await callLogSyncService.manualSync();

      if (result.success) {
        await updateSyncStats();
        setSyncState(prev => ({
          ...prev,
          isSyncing: false,
          error: null,
        }));
      } else {
        setSyncState(prev => ({
          ...prev,
          isSyncing: false,
          error: result.error || 'Sync failed',
        }));
      }
    } catch (error: any) {
      const errorMessage = error?.message || 'Sync failed';
      if (__DEV__) {
        console.error('Manual sync error:', {
          message: errorMessage,
          code: error?.code,
          response: error?.response?.data,
        });
      }
      
      // Update state with error for UI feedback
      setSyncState(prev => ({
        ...prev,
        isSyncing: false,
        error: errorMessage,
      }));
      // MEDIUM PRIORITY BUG FIX: Throw error so caller can handle it properly
      throw error;
    }
  }, [isAuthenticated, syncState.isSyncing, updateSyncStats]);

  // Auto sync on network reconnection
  useEffect(() => {
    if (!isAuthenticated || !isOnboardingCompleted) return;

    if (__DEV__) {
      console.log('[CallLogContext] Setting up network listener...');
    }
    
    const unsubscribe = NetInfo.addEventListener(state => {
      if (__DEV__) {
        console.log('[Network Status]', {
          isConnected: state.isConnected,
          isInternetReachable: state.isInternetReachable,
          type: state.type,
          isSyncing: isSyncingRef.current,
        });
      }
      
      if (state.isConnected && !isSyncingRef.current) {
        if (__DEV__) {
          console.log('✅ Network connected, triggering auto-sync...');
        }
        // Delay sync to ensure stable connection
        setTimeout(async () => {
          // LOW PRIORITY BUG FIX: Check if still mounted before setState
          if (!isMountedRef.current) {
            if (__DEV__) {
              console.log('Component unmounted, skipping setState');
            }
            return;
          }
          
          try {
            const syncResult = await callLogSyncService.syncUnsyncedLogs();
            await updateSyncStats();
            
            // Update error state based on result (check mounted again)
            if (!isMountedRef.current) return;
            
            if (!syncResult.success && syncResult.error) {
              setSyncState(prev => ({
                ...prev,
                error: syncResult.error || null,
              }));
            } else if (syncResult.success) {
              // Clear any previous errors on successful sync
              setSyncState(prev => ({
                ...prev,
                error: null,
              }));
            }
          } catch (error: any) {
            if (__DEV__) {
              console.error('Auto-sync error:', error);
            }
            // Check mounted before setState
            if (!isMountedRef.current) return;
            
            const errorMessage = error?.message || 'Auto-sync failed';
            setSyncState(prev => ({
              ...prev,
              error: errorMessage,
            }));
          }
        }, SYNC_INTERVALS.NETWORK_RECONNECT_DELAY);
      }
    });

    return () => {
      if (__DEV__) {
        console.log('[CallLogContext] Removing network listener');
      }
      unsubscribe();
    };
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats]);

  // Monitor app state changes
  useEffect(() => {
    if (!isAuthenticated || !isOnboardingCompleted) return;

    const handleAppStateChange = (nextAppState: AppStateStatus) => {
      if (nextAppState === 'active') {
        if (__DEV__) {
          console.log('App became active, processing new call logs...');
        }
        // Process new call logs when app becomes active
        callLogSyncService.processNewCallLogs().then(() => {
          updateSyncStats();
        });
      }
    };

    const subscription = AppState.addEventListener('change', handleAppStateChange);

    return () => {
      subscription.remove();
    };
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats]);

  // Start auto sync
  // NOTE: BackgroundFetch is configured via headless task in backgroundFetch.js
  // We don't need to call configure() here as it would override the headless task
  const startAutoSync = useCallback(() => {
    if (__DEV__) {
      console.log('[CallLogContext] Auto-sync is managed by headless task');
    }
  }, []);

  // Stop auto sync
  // NOTE: We don't actually stop the headless task as it should always run
  const stopAutoSync = useCallback(() => {
    if (__DEV__) {
      console.log('[CallLogContext] Auto-sync state updated');
    }
  }, []);

  // Initialize on mount if authenticated AND onboarding completed
  useEffect(() => {
    if (isAuthenticated && isOnboardingCompleted) {
      if (__DEV__) {
        console.log('[CallLogContext] User authenticated and onboarding completed, initializing auto-sync...');
      }
      updateSyncStats();
      startAutoSync();
      
      // Start call state listener for immediate sync after calls
      callStateListener.startListening();
      
      // Immediately trigger an initial sync
      setTimeout(async () => {
        if (__DEV__) {
          console.log('[CallLogContext] Running initial sync...');
        }
        try {
          await callLogSyncService.processNewCallLogs();
          await callLogSyncService.syncUnsyncedLogs();
          await updateSyncStats();
        } catch (error) {
          if (__DEV__) {
            console.error('Initial sync error:', error);
          }
        }
      }, SYNC_INTERVALS.INITIAL_SYNC_DELAY); // Wait after onboarding completion
    } else {
      if (__DEV__) {
        console.log('[CallLogContext] User not authenticated or onboarding not completed, stopping auto-sync');
      }
      stopAutoSync();
      callStateListener.stopListening();
    }
    
    // Cleanup on unmount
    return () => {
      callStateListener.stopListening();
    };
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats, startAutoSync, stopAutoSync]);

  // Update stats every 30 seconds when app is active
  useEffect(() => {
    if (!isAuthenticated || !isOnboardingCompleted) return;

    const interval = setInterval(() => {
      updateSyncStats();
    }, SYNC_INTERVALS.STATS_UPDATE);

    return () => clearInterval(interval);
  }, [isAuthenticated, isOnboardingCompleted, updateSyncStats]);

  const value: CallLogContextType = {
    ...syncState,
    manualSync,
    startAutoSync,
    stopAutoSync,
  };

  return (
    <CallLogContext.Provider value={value}>
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
