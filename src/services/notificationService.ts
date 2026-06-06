import PushNotification from 'react-native-push-notification';
import { Platform, PermissionsAndroid } from 'react-native';

class NotificationService {
  private hasPermission: boolean = false;

  /**
   * Initialize push notification configuration
   */
  configure(): void {
    PushNotification.configure({
      // Called when a remote or local notification is opened or received
      onNotification: function (notification: any) {
        console.log('NOTIFICATION:', notification);
      },

      // Should the initial notification be popped automatically
      popInitialNotification: true,

      // Permissions (iOS only)
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
          console.log('Notification permission:', granted ? 'Granted' : 'Not granted');
          return granted;
        } catch (error) {
          console.error('Error checking notification permission:', error);
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
      this.hasPermission = true; // Assume granted, or check using different method
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
        (created) => console.log(`Channel created: ${created}`)
      );
    }
  }

  /**
   * Show local notification (checks permission first)
   */
  async showNotification(title: string, message: string, channelId: string = 'call-log-sync'): Promise<void> {
    // Re-check permission before showing notification
    const hasPermission = await this.checkPermission();
    
    if (!hasPermission) {
      console.warn('⚠️ Cannot show notification: Permission not granted');
      return;
    }

    console.log('📲 Showing notification:', { title, message });
    
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
