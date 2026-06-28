/**
 * Headless JS Task for Call Log Sync
 * This runs when triggered by native Android service (CallLogSyncService)
 */
import PushNotification from 'react-native-push-notification';
import { Platform, PermissionsAndroid } from 'react-native';

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

const CallLogSyncTask = async (_taskData) => {
  if (__DEV__) console.log('[CallLogSyncTask] Starting headless sync...');

  try {
    const { storageService } = require('../services/storage');
    const token = await storageService.getAuthToken();
    const userData = await storageService.getUserData();

    if (!token || !userData) {
      if (__DEV__) console.log('[CallLogSyncTask] User not authenticated, skipping sync');
      return;
    }

    const { callLogSyncService } = require('../services/callLogSync');

    // Process any new call logs from device
    const processedCount = await callLogSyncService.processNewCallLogs();
    if (__DEV__) console.log(`[CallLogSyncTask] Processed ${processedCount} new logs`);

    // Always sync unsynced queue — not just when processedCount > 0,
    // so previously queued-but-unsynced logs are not skipped.
    const syncResult = await callLogSyncService.syncUnsyncedLogs();
    if (__DEV__) console.log(`[CallLogSyncTask] Sync result:`, syncResult);

    if (syncResult.success && syncResult.syncedCount > 0) {
      const canNotify = await hasNotificationPermission();
      if (canNotify) {
        PushNotification.localNotification({
          channelId: 'call-log-sync',
          title: 'Call Log Synced',
          message: `Successfully uploaded ${syncResult.syncedCount} call log${syncResult.syncedCount > 1 ? 's' : ''}`,
          playSound: true,
          soundName: 'default',
          priority: 'high',
          visibility: 'public',
        });
      }
    } else if (syncResult.error) {
      if (syncResult.error.includes('internet') || syncResult.error.includes('network')) {
        const canNotify = await hasNotificationPermission();
        if (canNotify) {
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

    if (__DEV__) console.log('[CallLogSyncTask] Headless sync completed');
  } catch (error) {
    if (__DEV__) console.error('[CallLogSyncTask] Error:', error);
  }
};

export default CallLogSyncTask;
