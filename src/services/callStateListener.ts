import { AppState, PermissionsAndroid, Platform } from 'react-native';
import PushNotification from 'react-native-push-notification';
import { callLogSyncService } from './callLogSync';

class CallStateListener {
  private subscription: ReturnType<typeof setInterval> | null = null;
  private isListening = false;
  private isProcessing = false; // guard against concurrent interval ticks
  private readonly checkInterval = 3000;

  /**
   * Check if notification permission is granted
   */
  private async hasNotificationPermission(): Promise<boolean> {
    if (Platform.OS === 'android' && Platform.Version >= 33) {
      try {
        return await PermissionsAndroid.check(
          PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS
        );
      } catch {
        return false;
      }
    }
    return true; // Android < 13 or iOS
  }

  /**
   * Start listening for call state changes
   */
  startListening() {
    if (this.isListening) {
      if (__DEV__) console.log('[CallStateListener] Already listening');
      return;
    }

    if (Platform.OS !== 'android') {
      if (__DEV__) console.log('[CallStateListener] Only supported on Android');
      return;
    }

    try {
      if (__DEV__) console.log('[CallStateListener] Starting call state listener...');
      this.isListening = true;

      this.subscription = setInterval(async () => {
        if (this.isProcessing) return;
        this.isProcessing = true;
        try {
          const processedCount = await callLogSyncService.processNewCallLogs();

          if (processedCount > 0) {
            if (__DEV__) {
              console.log(`[CallStateListener] Detected ${processedCount} new call(s), syncing immediately...`);
            }

            // Small delay to ensure call log is fully written to device
            await new Promise(resolve => setTimeout(resolve, 1000));

            const result = await callLogSyncService.syncUnsyncedLogs();

            if (result.success && result.syncedCount > 0) {
              if (__DEV__) {
                console.log(`[CallStateListener] Synced ${result.syncedCount} call log(s) after call ended`);
              }

              // Show notification only when app is in background
              const appState = AppState.currentState;
              if (appState !== 'active') {
                const hasPermission = await this.hasNotificationPermission();
                if (hasPermission) {
                  PushNotification.localNotification({
                    channelId: 'call-log-sync',
                    title: 'Call Log Synced',
                    message: 'Call log uploaded successfully',
                    playSound: true,
                    soundName: 'default',
                    priority: 'high',
                    visibility: 'public',
                  });
                }
              }
            } else if (result.error) {
              if (__DEV__) {
                console.log(`[CallStateListener] Sync postponed: ${result.error}`);
              }

              // Show offline notification when app is in background
              const appState = AppState.currentState;
              if (appState !== 'active' && result.error.includes('internet')) {
                const hasPermission = await this.hasNotificationPermission();
                if (hasPermission) {
                  PushNotification.localNotification({
                    channelId: 'call-log-sync',
                    title: 'No Internet',
                    message: 'Call log will sync when internet is available',
                    playSound: false,
                    priority: 'low',
                    visibility: 'public',
                  });
                }
              }
            }
          }
        } catch (error) {
          if (__DEV__) console.error('[CallStateListener] Error checking for new calls:', error);
        } finally {
          this.isProcessing = false;
        }
      }, this.checkInterval);

      if (__DEV__) console.log('[CallStateListener] Call state listener started (checking every 3 seconds)');
    } catch (error) {
      if (__DEV__) console.error('[CallStateListener] Error starting listener:', error);
      this.isListening = false;
    }
  }

  /**
   * Stop listening for call state changes
   */
  stopListening() {
    if (!this.isListening) return;

    try {
      if (this.subscription) {
        clearInterval(this.subscription);
        this.subscription = null;
      }
      this.isListening = false;
      this.isProcessing = false;
      if (__DEV__) console.log('[CallStateListener] Call state listener stopped');
    } catch (error) {
      if (__DEV__) console.error('[CallStateListener] Error stopping listener:', error);
    }
  }
}

export const callStateListener = new CallStateListener();
