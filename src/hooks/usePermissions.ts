import { useState, useEffect, useCallback } from 'react';
import { Platform, Linking, AppState, AppStateStatus } from 'react-native';
import { 
  PERMISSIONS, 
  Permission, 
  PermissionStatus, 
  request, 
  check, 
  openSettings,
  checkNotifications,
  requestNotifications
} from 'react-native-permissions';

export interface PermissionState {
  contacts: PermissionStatus;
  callLog: PermissionStatus;
  phoneState: PermissionStatus;
  notifications: PermissionStatus;
}

export interface UsePermissionsReturn {
  permissions: PermissionState;
  isLoading: boolean;
  requestPermission: (permission: keyof PermissionState) => Promise<PermissionStatus>;
  checkAllPermissions: () => Promise<void>;
  openAppSettings: () => Promise<void>;
}

const PERMISSION_MAP: Record<Exclude<keyof PermissionState, 'notifications'>, Permission> = {
  contacts: PERMISSIONS.ANDROID.READ_CONTACTS,
  callLog: PERMISSIONS.ANDROID.READ_CALL_LOG,
  phoneState: PERMISSIONS.ANDROID.READ_PHONE_STATE,
  // notifications are handled separately using checkNotifications/requestNotifications
};

export const usePermissions = (): UsePermissionsReturn => {
  const [permissions, setPermissions] = useState<PermissionState>({
    contacts: 'unavailable',
    callLog: 'unavailable',
    phoneState: 'unavailable',
    notifications: 'unavailable',
  });
  const [isLoading, setIsLoading] = useState(true);

  const checkPermission = useCallback(async (permissionKey: keyof PermissionState): Promise<PermissionStatus> => {
    // Special handling for notifications
    if (permissionKey === 'notifications') {
      // For notifications on Android < 13, treat as granted
      if (Platform.OS === 'android' && Platform.Version < 33) {
        return 'granted';
      }
      
      try {
        const { status } = await checkNotifications();
        return status;
      } catch (error) {
        if (__DEV__) {
          console.error('Error checking notifications permission:', error);
        }
        return 'unavailable';
      }
    }

    // Handle other permissions normally
    const permission = PERMISSION_MAP[permissionKey as Exclude<keyof PermissionState, 'notifications'>];
    if (!permission) {
      if (__DEV__) {
        console.warn(`Unknown permission type: ${permissionKey}`);
      }
      return 'unavailable';
    }

    try {
      const status = await check(permission);
      return status;
    } catch (error) {
      if (__DEV__) {
        console.error(`Error checking ${permissionKey} permission:`, error);
      }
      return 'unavailable';
    }
  }, []);

  const checkAllPermissions = useCallback(async () => {
    setIsLoading(true);
    try {
      const [contacts, callLog, phoneState, notifications] = await Promise.all([
        checkPermission('contacts'),
        checkPermission('callLog'),
        checkPermission('phoneState'),
        checkPermission('notifications'),
      ]);

      setPermissions({
        contacts,
        callLog,
        phoneState,
        notifications,
      });
    } catch (error) {
      if (__DEV__) {
        console.error('Error checking permissions:', error);
      }
    } finally {
      setIsLoading(false);
    }
  }, [checkPermission]);

  const requestPermission = useCallback(async (permissionKey: keyof PermissionState): Promise<PermissionStatus> => {
    // Special handling for notifications
    if (permissionKey === 'notifications') {
      // For notifications on Android < 13, return granted immediately
      if (Platform.OS === 'android' && Platform.Version < 33) {
        setPermissions(prev => ({ ...prev, notifications: 'granted' }));
        return 'granted';
      }

      try {
        const { status } = await requestNotifications(['alert', 'sound', 'badge']);
        setPermissions(prev => ({ ...prev, notifications: status }));
        return status;
      } catch (error) {
        if (__DEV__) {
          console.error('Error requesting notifications permission:', error);
        }
        return 'unavailable';
      }
    }

    // Handle other permissions normally
    const permission = PERMISSION_MAP[permissionKey as Exclude<keyof PermissionState, 'notifications'>];
    if (!permission) {
      if (__DEV__) {
        console.warn(`Unknown permission type: ${permissionKey}`);
      }
      return 'unavailable';
    }

    try {
      const status = await request(permission);
      setPermissions(prev => ({ ...prev, [permissionKey]: status }));
      return status;
    } catch (error) {
      if (__DEV__) {
        console.error(`Error requesting ${permissionKey} permission:`, error);
      }
      return 'unavailable';
    }
  }, []);

  const openAppSettings = useCallback(async () => {
    try {
      await openSettings();
    } catch (error) {
      if (__DEV__) {
        console.error('Error opening app settings:', error);
      }
      // Fallback to general settings
      await Linking.openSettings();
    }
  }, []);

  // Check permissions on mount
  useEffect(() => {
    checkAllPermissions();
  }, [checkAllPermissions]);

  // Listen for app state changes to recheck permissions
  useEffect(() => {
    const handleAppStateChange = (nextAppState: AppStateStatus) => {
      if (nextAppState === 'active') {
        checkAllPermissions();
      }
    };

    const subscription = AppState.addEventListener('change', handleAppStateChange);
    return () => subscription?.remove();
  }, [checkAllPermissions]);

  return {
    permissions,
    isLoading,
    requestPermission,
    checkAllPermissions,
    openAppSettings,
  };
};
