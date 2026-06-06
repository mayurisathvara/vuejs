/**
 * Headless JS Task for Call Log Sync
 * This runs when triggered by native Android service
 */
import PushNotification from 'react-native-push-notification';

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
      
      // Show notification based on result
      if (syncResult.success && syncResult.syncedCount > 0) {
        PushNotification.localNotification({
          channelId: 'call-log-sync',
          title: '📞 Call Log Synced',
          message: `Successfully uploaded ${syncResult.syncedCount} call log${syncResult.syncedCount > 1 ? 's' : ''}`,
          playSound: true,
          soundName: 'default',
          priority: 'high',
          visibility: 'public',
        });
        console.log(`[CallLogSyncTask] ✅ Notification sent for ${syncResult.syncedCount} logs`);
      } else if (syncResult.error) {
        console.log(`[CallLogSyncTask] ⚠️ Sync failed: ${syncResult.error}`);
        
        // Show notification if no internet
        if (syncResult.error.includes('internet') || syncResult.error.includes('network')) {
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
    
    console.log('[CallLogSyncTask] Headless sync completed');
  } catch (error) {
    console.error('[CallLogSyncTask] Error:', error);
  }
};

export default CallLogSyncTask;
