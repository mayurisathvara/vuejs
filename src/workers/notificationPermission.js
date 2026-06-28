import { Platform, PermissionsAndroid } from 'react-native';

/**
 * Returns true if the app has permission to show local notifications.
 * On Android < 13 (API < 33) this is always true (no runtime permission needed).
 */
const hasNotificationPermission = async () => {
  if (Platform.OS === 'android' && Platform.Version >= 33) {
    try {
      return await PermissionsAndroid.check(
        PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS
      );
    } catch {
      return false;
    }
  }
  return true;
};

export default hasNotificationPermission;
