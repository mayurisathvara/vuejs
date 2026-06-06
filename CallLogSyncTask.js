/**
 * Headless JS Task for Call Log Sync
 * This runs when triggered by native Android service
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

const CallLogSyncTask = async (taskData) => {
  console.log('[CallLogSyncTask] Starting headless sync...');

  try {
    // FIX: Check authentication before processing
    const { storageService } = require('./src/services/storage');
    const token = await storageService.getAuthToken();
    const userData = await storageService.getUserData();
    
    if (!token || !userData) {
      console.log('[CallLogSyncTask] User not authenticated, skipping sync');
      return;
    }
    
    // Import services dynamically
    const { callLogSyncService } = require('./src/services/callLogSync');
    
    // Process new call logs
    console.log('[CallLogSyncTask] Processing new call logs...');
    const processedCount = await callLogSyncService.processNewCallLogs();
    console.log(`[CallLogSyncTask] Processed ${processedCount} new logs`);
    
    if (processedCount > 0) {
      // Sync unsynced logs
      console.log('[CallLogSyncTask] Syncing to server...');
      const syncResult = await callLogSyncService.syncUnsyncedLogs();
      console.log(`[CallLogSyncTask] Sync result:`, syncResult);
      
      if (syncResult.success && syncResult.syncedCount > 0) {
        const canNotify = await hasNotificationPermission();
        if (canNotify) {
          PushNotification.localNotification({
            channelId: 'call-log-sync',
            title: '📞 Call Log Synced',
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
    
    console.log('[CallLogSyncTask] Headless sync completed');
  } catch (error) {
    console.error('[CallLogSyncTask] Error:', error);
  }
};

export default CallLogSyncTask;
