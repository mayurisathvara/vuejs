import PushNotification from 'react-native-push-notification';
import { Platform, PermissionsAndroid } from 'react-native';

class NotificationService {
  private hasPermission: boolean = false;

  /**
   * Initialize push notification configuration
   */
  configure(): void {
    PushNotification.configure({
      onNotification: function (_notification: any) {
        // Notification received or opened — no action needed
      },
      popInitialNotification: true,
      requestPermissions: Platform.OS === 'ios',
    });

    // Create notification channel for Android
    this.createChannels();

    // Check notification permission
    this.checkPermission();
  }

  /**
   * Check if notification permission is granted
   */
  async checkPermission(): Promise<boolean> {
    if (Platform.OS === 'android') {
      if (Platform.Version >= 33) {
        // Android 13+ requires POST_NOTIFICATIONS permission
        try {
          const granted = await PermissionsAndroid.check(
            PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS
          );
          this.hasPermission = granted;
          return granted;
        } catch (error) {
          if (__DEV__) console.error('Error checking notification permission:', error);
          this.hasPermission = false;
          return false;
        }
      } else {
        // Android < 13 doesn't require runtime permission
        this.hasPermission = true;
        return true;
      }
    } else {
      // iOS
      this.hasPermission = true;
      return true;
    }
  }

  /**
   * Create notification channels for Android O+
   */
  createChannels(): void {
    if (Platform.OS === 'android') {
      PushNotification.createChannel(
        {
          channelId: 'call-log-sync',
          channelName: 'Call Log Sync',
          channelDescription: 'Notifications for call log synchronization',
          playSound: false,
          soundName: 'default',
          importance: 4, // IMPORTANCE_HIGH
          vibrate: false,
        },
        (_created) => {}
      );
    }
  }

  /**
   * Show local notification (checks permission first)
   */
  async showNotification(title: string, message: string, channelId: string = 'call-log-sync'): Promise<void> {
    const hasPermission = await this.checkPermission();

    if (!hasPermission) {
      if (__DEV__) console.warn('Cannot show notification: Permission not granted');
      return;
    }

    PushNotification.localNotification({
      channelId,
      title,
      message,
      playSound: false,
      vibrate: false,
      priority: 'high',
    });
  }

  /**
   * Cancel all notifications
   */
  cancelAllNotifications(): void {
    PushNotification.cancelAllLocalNotifications();
  }
}

export const notificationService = new NotificationService();
