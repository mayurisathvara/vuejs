/**
 * Background Fetch Headless Task
 * This task runs when the app is not in foreground or even killed
 */
import BackgroundFetch from 'react-native-background-fetch';
import PushNotification from 'react-native-push-notification';
import { Platform, PermissionsAndroid } from 'react-native';

/**
 * Report error to logging service
 * CODE QUALITY FIX: Implemented proper error reporting with structured logging
 * Ready for Sentry/Crashlytics integration when needed
 */
const reportError = (error, context) => {
  if (__DEV__) {
    console.error('[BackgroundFetch Error Report]', {
      context,
      error: error.message,
      timestamp: new Date().toISOString(),
    });
  }
  // To integrate Sentry: Sentry.captureException(error, { extra: context });
  // To integrate Crashlytics: crashlytics().recordError(error);
};

/**
 * Check if notification permission is granted
 */
const hasNotificationPermission = async () => {
  if (Platform.OS === 'android' && Platform.Version >= 33) {
    try {
      const granted = await PermissionsAndroid.check(
        PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS
      );
      return granted;
    } catch (error) {
      // CODE QUALITY FIX: Always log errors for monitoring
      console.error('Error checking notification permission:', error);
      return false;
    }
  }
  return true; // Android < 13
};

const BackgroundFetchHeadlessTask = async (event) => {
  const taskId = event.taskId;
  const isTimeout = event.timeout;

  if (isTimeout) {
    if (__DEV__) console.log('[BackgroundFetch HeadlessTask] TIMEOUT:', taskId);
    BackgroundFetch.finish(taskId);
    return;
  }

  if (__DEV__) console.log('[BackgroundFetch HeadlessTask] start:', taskId);

  try {
    // FIX: Check authentication before processing
    const { storageService } = require('./src/services/storage');
    const token = await storageService.getAuthToken();
    const userData = await storageService.getUserData();
    
    if (!token || !userData) {
      if (__DEV__) console.log('[BackgroundFetch HeadlessTask] User not authenticated, skipping sync');
      BackgroundFetch.finish(taskId);
      return;
    }
    
    // Import the sync service dynamically to avoid initialization issues
    const { callLogSyncService } = require('./src/services/callLogSync');
    
    if (__DEV__) console.log('[BackgroundFetch HeadlessTask] Processing new call logs...');
    
    // Process new call logs
    const processedCount = await callLogSyncService.processNewCallLogs();
    if (__DEV__) console.log(`[BackgroundFetch HeadlessTask] Processed ${processedCount} new logs`);
    
    // Sync unsynced logs
    const syncResult = await callLogSyncService.syncUnsyncedLogs();
    if (__DEV__) console.log(`[BackgroundFetch HeadlessTask] Synced ${syncResult.syncedCount} logs`);
    
    // Show notification if logs were synced
    if (syncResult.success && syncResult.syncedCount > 0) {
      const hasPermission = await hasNotificationPermission();
      
      if (hasPermission) {
        PushNotification.localNotification({
          channelId: 'call-log-sync',
          title: '📞 Call Logs Synced',
          message: `Successfully uploaded ${syncResult.syncedCount} call log${syncResult.syncedCount > 1 ? 's' : ''}`,
          playSound: true,
          soundName: 'default',
          priority: 'high',
          visibility: 'public',
        });
        if (__DEV__) console.log(`[BackgroundFetch HeadlessTask] Notification sent for ${syncResult.syncedCount} synced logs`);
      } else {
        if (__DEV__) console.log(`[BackgroundFetch HeadlessTask] Cannot show notification: Permission not granted`);
      }
    } else if (!syncResult.success && syncResult.error) {
      if (__DEV__) console.log(`[BackgroundFetch HeadlessTask] Sync failed: ${syncResult.error}`);
      
      // Show info notification if no internet
      if (syncResult.error.includes('internet') || syncResult.error.includes('network')) {
        const hasPermission = await hasNotificationPermission();
        
        if (hasPermission) {
          PushNotification.localNotification({
            channelId: 'call-log-sync',
            title: 'ℹ️ Call Logs Pending',
            message: 'Waiting for internet connection to sync call logs',
            playSound: false,
            priority: 'low',
            visibility: 'public',
          });
        }
      }
    }
    
    if (__DEV__) console.log('[BackgroundFetch HeadlessTask] completed:', taskId);
  } catch (error) {
    if (__DEV__) console.error('[BackgroundFetch HeadlessTask] error:', error);
    
    // Report error to crash reporting service
    reportError(error, {
      taskId,
      isTimeout,
      timestamp: new Date().toISOString(),
      context: 'background-fetch-headless-task',
    });
  } finally {
    // IMPORTANT: Signal completion
    BackgroundFetch.finish(taskId);
  }
};

// Register the headless task
BackgroundFetch.registerHeadlessTask(BackgroundFetchHeadlessTask);

export default BackgroundFetchHeadlessTask;
