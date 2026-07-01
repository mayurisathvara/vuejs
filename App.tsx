import React from 'react';
import 'react-native-gesture-handler';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { DeviceEventEmitter } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import Toast from 'react-native-toast-message';
import { toastConfig } from './src/components/ToastConfig';
import BackgroundFetch from 'react-native-background-fetch';
import { AuthProvider } from './src/contexts/AuthContext';
import { OnboardingProvider } from './src/contexts/OnboardingContext';
import { ThemeProvider } from './src/contexts/ThemeContext';
import { NetworkProvider } from './src/contexts/NetworkContext';
import { CallLogProvider } from './src/contexts/CallLogContext';
import NetworkWrapper from './src/components/NetworkWrapper';
import AppNavigator from './src/navigation/AppNavigator';
import ErrorBoundary from './src/components/ErrorBoundary';
import { notificationService } from './src/services/notificationService';
import { callLogSyncService } from './src/services/callLogSync';

const App: React.FC = () => {
  React.useEffect(() => {
    // Ensure vector icon fonts are loaded early so icons render reliably across platforms
    if (MaterialCommunityIcons && typeof MaterialCommunityIcons.loadFont === 'function') {
      MaterialCommunityIcons.loadFont();
    }

    // Initialize push notifications
    notificationService.configure();

    // Configure BackgroundFetch for 15-minute periodic sync
    BackgroundFetch.configure(
      {
        minimumFetchInterval: 15, // 15 minutes
        stopOnTerminate: false,   // Continue after app killed
        startOnBoot: true,        // Start after reboot
        enableHeadless: true,     // Enable headless execution
        requiredNetworkType: BackgroundFetch.NETWORK_TYPE_ANY,
        requiresCharging: false,
        requiresDeviceIdle: false,
        requiresBatteryNotLow: false,
        requiresStorageNotLow: false,
      },
      async (taskId) => {
        // This runs when app is in foreground/background (not killed)
        if (__DEV__) console.log('[BackgroundFetch] In-app task:', taskId);
        try {
          await callLogSyncService.processNewCallLogs();
          await callLogSyncService.syncUnsyncedLogs();
          if (__DEV__) console.log('[BackgroundFetch] Task completed:', taskId);
        } catch (error: unknown) {
          if (__DEV__) {
            console.error('[BackgroundFetch] In-app error:', taskId, error);
          }
        }
        BackgroundFetch.finish(taskId);
      },
      (taskId) => {
        if (__DEV__) console.log('[BackgroundFetch] TIMEOUT:', taskId);
        BackgroundFetch.finish(taskId);
      }
    ).then(status => {
      if (__DEV__) console.log('[BackgroundFetch] Configured, status:', status);
    }).catch(error => {
      if (__DEV__) console.error('[BackgroundFetch] Config error:', error);
    });

    // Listen for WorkManager sync triggers from native side
    const subscription = DeviceEventEmitter.addListener(
      'CallLogSyncTrigger',
      async () => {
        if (__DEV__) console.log('[WorkManager] Triggered call log sync');
        try {
          await callLogSyncService.processNewCallLogs();
          await callLogSyncService.syncUnsyncedLogs();
          if (__DEV__) console.log('[WorkManager] Sync completed');
        } catch (error: unknown) {
          if (__DEV__) {
            console.error('[WorkManager] Sync error:', error);
          }
        }
      }
    );

    return () => {
      // Cleanup: Stop BackgroundFetch and remove listeners
      subscription.remove();
      BackgroundFetch.stop().catch(err => {
        console.log('[BackgroundFetch] Stop error (may not be running):', err);
      });
    };
  }, []);
  
  return (
    <ErrorBoundary>
      <GestureHandlerRootView style={{ flex: 1 }}>
        <ThemeProvider>
          <NetworkProvider>
            <AuthProvider>
              <OnboardingProvider>
                <CallLogProvider>
                  <NetworkWrapper>
                    <AppNavigator />
                  </NetworkWrapper>
                  <Toast config={toastConfig} />
                </CallLogProvider>
              </OnboardingProvider>
            </AuthProvider>
          </NetworkProvider>
        </ThemeProvider>
      </GestureHandlerRootView>
    </ErrorBoundary>
  );
};

export default App;
