import { NativeModules, NativeEventEmitter, Platform, AppState, PermissionsAndroid } from 'react-native';
import PushNotification from 'react-native-push-notification';
import { callLogSyncService } from './callLogSync';

class CallStateListener {
  private subscription: any = null;
  private isListening = false;
  private lastCheckTime = 0;
  private checkInterval = 3000; // Check every 3 seconds

  /**
   * Check if notification permission is granted
   */
  private async hasNotificationPermission(): Promise<boolean> {
    if (Platform.OS === 'android' && Platform.Version >= 33) {
      try {
        const granted = await PermissionsAndroid.check(
          PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS
        );
        return granted;
      } catch (error) {
        console.error('Error checking notification permission:', error);
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
      console.log('[CallStateListener] Already listening');
      return;
    }

    if (Platform.OS !== 'android') {
      console.log('[CallStateListener] Only supported on Android');
      return;
    }

    try {
      console.log('[CallStateListener] 🎧 Starting call state listener...');
      this.isListening = true;
      this.lastCheckTime = Date.now();

      // Set up a timer to check for new calls periodically (every 3 seconds)
      this.subscription = setInterval(async () => {
        try {
          const now = Date.now();
          
          // Skip if last check was too recent (prevent duplicate checks)
          if (now - this.lastCheckTime < this.checkInterval) {
            return;
          }
          
          this.lastCheckTime = now;
          
          // Process any new call logs
          const processedCount = await callLogSyncService.processNewCallLogs();
          
          if (processedCount > 0) {
            console.log(`[CallStateListener] 📞 Detected ${processedCount} new call(s), syncing immediately...`);
            
            // Small delay to ensure call log is fully written
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Immediately sync the new logs
            const result = await callLogSyncService.syncUnsyncedLogs();
            
            if (result.success && result.syncedCount > 0) {
              console.log(`[CallStateListener] ✅ Successfully synced ${result.syncedCount} call log(s) after call ended`);
              
              // Show notification even if app is in background
              const appState = AppState.currentState;
              if (appState !== 'active') {
                const hasPermission = await this.hasNotificationPermission();
                
                if (hasPermission) {
                  PushNotification.localNotification({
                    channelId: 'call-log-sync',
                    title: '📞 Call Log Synced',
                    message: `Call log uploaded successfully`,
                    playSound: true,
                    soundName: 'default',
                    priority: 'high',
                    visibility: 'public',
                  });
                  console.log('[CallStateListener] 📲 Notification sent (app in background)');
                } else {
                  console.warn('[CallStateListener] ⚠️ Cannot show notification: Permission not granted');
                }
              }
            } else if (result.error) {
              console.log(`[CallStateListener] ⚠️ Sync postponed: ${result.error}`);
              
              // Show notification if no internet and app is in background
              const appState = AppState.currentState;
              if (appState !== 'active' && result.error.includes('internet')) {
                const hasPermission = await this.hasNotificationPermission();
                
                if (hasPermission) {
                  PushNotification.localNotification({
                    channelId: 'call-log-sync',
                    title: 'ℹ️ No Internet',
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
          console.error('[CallStateListener] Error checking for new calls:', error);
        }
      }, this.checkInterval);

      console.log('[CallStateListener] ✅ Call state listener started (checking every 3 seconds)');
    } catch (error) {
      console.error('[CallStateListener] Error starting listener:', error);
      this.isListening = false;
    }
  }

  /**
   * Stop listening for call state changes
   */
  stopListening() {
    if (!this.isListening) {
      return;
    }

    try {
      if (this.subscription) {
        clearInterval(this.subscription);
        this.subscription = null;
      }

      this.isListening = false;
      console.log('[CallStateListener] 🛑 Call state listener stopped');
    } catch (error) {
      console.error('[CallStateListener] Error stopping listener:', error);
    }
  }
}

export const callStateListener = new CallStateListener();
