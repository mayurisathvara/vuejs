/**
 * Headless Task for Periodic Call Log Sync (AlarmManager)
 *
 * Executed by AlarmManager every 15 minutes, even when the app is completely killed.
 */
import { callLogSyncService } from './src/services/callLogSync';

const PeriodicSyncTask = async () => {
  if (__DEV__) console.log('[PeriodicSyncTask] Starting periodic sync via AlarmManager...');

  try {
    await callLogSyncService.backgroundSync();
    if (__DEV__) console.log('[PeriodicSyncTask] Sync completed successfully');
  } catch (error) {
    if (__DEV__) console.error('[PeriodicSyncTask] Error during sync:', error);
  }
};

export default PeriodicSyncTask;
