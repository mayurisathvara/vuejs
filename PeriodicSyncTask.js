/**
 * Headless Task for Periodic Call Log Sync (AlarmManager)
 * 
 * This task is executed by AlarmManager every 15 minutes, even when the app is completely killed.
 * It runs as a background headless task without requiring the UI to be active.
 */

import { callLogSyncService } from './src/services/callLogSync';

/**
 * Headless task handler for periodic sync triggered by AlarmManager
 * @returns {Promise<void>}
 */
const PeriodicSyncTask = async () => {
  console.log('[PeriodicSyncTask] Starting periodic sync via AlarmManager...');
  
  try {
    // Process and sync call logs
    // Note: backgroundSync() returns void, not a result object
    await callLogSyncService.backgroundSync();
    
    console.log('[PeriodicSyncTask] Sync completed successfully');
  } catch (error) {
    console.error('[PeriodicSyncTask] Error during sync:', error);
  }
};

export default PeriodicSyncTask;
