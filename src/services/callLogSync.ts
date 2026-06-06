import { PermissionsAndroid, Platform } from 'react-native';
import CallLogs from 'react-native-call-log';
import NetInfo from '@react-native-community/netinfo';
import Toast from 'react-native-toast-message';
import { callLogStorage } from './callLogStorage';
import { callLogSyncAPI } from './api';
import { storageService } from './storage';
import PushNotification from 'react-native-push-notification';
import { DATE_LIMITS, RETRY_CONFIG, UI_TIMINGS, QUEUE_CLEANUP } from '../constants';
import { generateCompactCallLogId } from '../utils/uniqueIdGenerator';
import {
  RawCallLog,
  CallLogData,
  CallType,
  CallStatus,
  ContactStatus,
  HangupBy,
} from '../types';

class CallLogSyncService {
  private isSyncing = false;

  /**
   * Request READ_CALL_LOG permission (Android)
   */
  async requestCallLogPermission(): Promise<boolean> {
    if (Platform.OS !== 'android') {
      return false;
    }

    try {
      const granted = await PermissionsAndroid.request(
        PermissionsAndroid.PERMISSIONS.READ_CALL_LOG,
        {
          title: 'Call Log Permission',
          message: 'This app needs access to your call logs to sync them.',
          buttonNeutral: 'Ask Me Later',
          buttonNegative: 'Cancel',
          buttonPositive: 'OK',
        }
      );
      return granted === PermissionsAndroid.RESULTS.GRANTED;
    } catch (error) {
      console.error('Error requesting call log permission:', error);
      return false;
    }
  }

  /**
   * Check if we have network connectivity
   */
  async checkNetworkConnection(): Promise<boolean> {
    try {
      const netInfo = await NetInfo.fetch();
      const isConnected = netInfo.isConnected ?? false;
      const isInternetReachable = netInfo.isInternetReachable;
      
      console.log('Network status:', {
        isConnected,
        isInternetReachable,
        type: netInfo.type,
        details: netInfo.details,
      });
      
      // Consider connected if isConnected is true and isInternetReachable is not explicitly false
      // This handles null/undefined cases where internet reachability is unknown
      return isConnected && isInternetReachable !== false;
    } catch (error) {
      if (__DEV__) {
        console.error('Error checking network:', error);
      }
      // On error, assume disconnected to prevent failed sync attempts
      return false;
    }
  }

  /**
   * Read call logs from device
   * @param fromTimestamp - Optional timestamp to read logs from
   * @param checkOnly - If true, only checks permission without requesting (for headless mode)
   */
  async readCallLogs(fromTimestamp?: number, checkOnly: boolean = false): Promise<RawCallLog[]> {
    try {
      // In headless mode (checkOnly=true), only check if permission exists
      // Don't try to request permission as there's no Activity context
      const hasPermission = checkOnly 
        ? await PermissionsAndroid.check(PermissionsAndroid.PERMISSIONS.READ_CALL_LOG)
        : await this.requestCallLogPermission();
        
      if (!hasPermission) {
        console.warn('Call log permission not granted');
        return [];
      }

      // Get logs from the last processed timestamp or last 7 days
      const timestamp = fromTimestamp || (await callLogStorage.getLastProcessedTimestamp());
      
      const filter = {
        minTimestamp: timestamp,
      };

      const callLogs = await CallLogs.load(-1, filter); // -1 means load all matching logs
      
      console.log(`Read ${callLogs.length} call logs from device`);
      
      return callLogs.map((log: any) => {
        // Validate and log the raw data
        if (!log.timestamp || isNaN(log.timestamp)) {
          console.warn('Invalid timestamp in raw call log:', log);
        }
        
        return {
          phoneNumber: log.phoneNumber || 'Unknown',
          type: log.type, // 'INCOMING', 'OUTGOING', 'MISSED'
          dateTime: log.timestamp,
          duration: log.duration || 0, // in seconds
          name: log.name || null,
          timestamp: log.timestamp,
        };
      });
    } catch (error) {
      console.error('Error reading call logs:', error);
      return [];
    }
  }

  /**
   * Convert raw call log to API format
   * @throws Error if timestamp is invalid (caller should handle)
   */
  private async convertToCallLogData(rawLog: RawCallLog, userId: string): Promise<CallLogData> {
    // Get timestamp with validation
    let timestamp = rawLog.timestamp || rawLog.dateTime;
    
    // Validate timestamp exists
    if (!timestamp) {
      throw new Error('Missing timestamp in call log');
    }
    
    // Convert string timestamp to number if needed
    if (typeof timestamp === 'string') {
      timestamp = parseInt(timestamp, 10);
    }
    
    // Validate timestamp is a valid number
    if (typeof timestamp !== 'number' || isNaN(timestamp)) {
      throw new Error(`Invalid timestamp type: ${typeof timestamp}`);
    }
    
    // Check if timestamp is in reasonable range (between year 2000 and 2100)
    if (timestamp < DATE_LIMITS.MIN_TIMESTAMP || timestamp > DATE_LIMITS.MAX_TIMESTAMP) {
      throw new Error(`Timestamp out of range: ${timestamp} (${new Date(timestamp).toISOString()})`);
    }
    
    // Determine call type code for unique ID generation
    let typeCode: 'IN' | 'OUT' | 'MISS';
    if (rawLog.type === 'INCOMING' || rawLog.type === '1') {
      typeCode = 'IN';
    } else if (rawLog.type === 'OUTGOING' || rawLog.type === '2') {
      typeCode = 'OUT';
    } else {
      typeCode = 'MISS';
    }
    
    // Generate compact unique ID
    // Format: CL[YYMMDD][HHMMSS][HASH][RND]
    // Example: CL260128143052A7B9m2p8
    const uniqueId = generateCompactCallLogId(userId, timestamp, rawLog.phoneNumber, typeCode);
    
    if (__DEV__) {
      console.log('Generated unique ID:', {
        uniqueId,
        phoneNumber: rawLog.phoneNumber,
        timestamp,
        date: new Date(timestamp).toISOString(),
        type: typeCode,
      });
    }

    // Determine call type and status
    let callType: CallType;
    let callStatus: CallStatus;
    
    if (rawLog.type === 'INCOMING' || rawLog.type === '1') {
      callType = 'inbound';
      callStatus = rawLog.duration > 0 ? 'Answered' : 'Missed';
    } else if (rawLog.type === 'OUTGOING' || rawLog.type === '2') {
      callType = 'outbound';
      callStatus = rawLog.duration > 0 ? 'Answered' : 'No Answer';
    } else if (rawLog.type === 'MISSED' || rawLog.type === '3') {
      callType = 'inbound';
      callStatus = 'Missed';
    } else {
      // Default fallback
      callType = 'inbound';
      callStatus = 'Missed';
    }

    // Format date time - validate and create Date object
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) {
      throw new Error(`Invalid timestamp: ${timestamp}`);
    }
    const dateTime = this.formatDateTime(date);

    // Determine contact status and name
    const contactStatus: ContactStatus = rawLog.name ? 'Saved' : 'Not Saved';
    const name = rawLog.name || 'Unknown';

    // Calculate durations
    const totalDuration = rawLog.duration.toString();
    const conversationDuration = rawLog.duration > 0 ? rawLog.duration.toString() : '0';
    const ringDuration = rawLog.duration > 0 ? '5' : '0'; // Estimate ring duration

    // Determine who hung up (simplified logic)
    const hangupBy: HangupBy = callStatus === 'Missed' || callStatus === 'No Answer' 
      ? 'remote' 
      : 'user';

    return {
      unique_id: uniqueId,
      user_id: userId,
      date_time: dateTime,
      call_status: callStatus,
      caller_number: rawLog.phoneNumber,
      call_type: callType,
      caller_duration: totalDuration,
      conversation_duration: conversationDuration,
      ring_duration: ringDuration,
      contact_status: contactStatus,
      name,
      hangup_by: hangupBy,
    };
  }

  /**
   * Format date to YYYY-MM-DD HH:mm:ss
   */
  private formatDateTime(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
  }

  /**
   * Process new call logs and save to offline storage
   * @param headlessMode - If true, running in background without Activity context
   */
  async processNewCallLogs(headlessMode: boolean = false): Promise<number> {
    try {
      console.log('📱 ========== PROCESSING NEW CALL LOGS ==========');
      
      const userData = await storageService.getUserData();
      if (!userData || !userData.id) {
        console.warn('❌ User not authenticated, skipping call log processing');
        return 0;
      }

      const userId = userData.id.toString();
      console.log('✅ User ID:', userId);
      
      const lastProcessed = await callLogStorage.getLastProcessedTimestamp();
      console.log('📅 Last processed timestamp:', lastProcessed, lastProcessed ? new Date(lastProcessed).toISOString() : 'Never');
      
      // Read new call logs since last processed time
      // In headless mode, only check permission without requesting
      const rawLogs = await this.readCallLogs(lastProcessed, headlessMode);
      console.log(`📥 Read ${rawLogs.length} call logs from device`);
      
      if (rawLogs.length === 0) {
        console.log('✅ No new call logs to process');
        return 0;
      }

      console.log('📋 First call log sample:', {
        phoneNumber: rawLogs[0].phoneNumber,
        type: rawLogs[0].type,
        timestamp: rawLogs[0].timestamp,
        duration: rawLogs[0].duration,
      });

      console.log(` Processing ${rawLogs.length} new call logs...`);

      // Convert and save to offline storage
      let savedCount = 0;
      let skippedCount = 0;
      
      // Get existing unsynced logs to avoid adding duplicates to the queue
      const existingLogs = await callLogStorage.getUnsyncedLogs();
      const existingIds = new Set(existingLogs.map(log => log.id));
      
      // Get already synced IDs to prevent re-syncing (prevents 422 duplicate errors)
      const syncedIds = await callLogStorage.getSyncedLogIds();
      const syncedIdsSet = new Set(syncedIds);
      console.log(`📋 Tracking ${syncedIds.length} previously synced log IDs`);
      
      // WARNING: Check queue size to prevent runaway growth
      if (existingLogs.length > UI_TIMINGS.QUEUE_WARNING_THRESHOLD) {
        if (__DEV__) {
          console.warn(`⚠️ WARNING: Unsynced queue has ${existingLogs.length} items! Implementing cleanup...`);
        }
        
        // Remove logs that have failed too many times (older than 7 days or attempts > 10)
        const maxAgeMs = QUEUE_CLEANUP.MAX_AGE_DAYS * 24 * 60 * 60 * 1000;
        const cutoffTime = Date.now() - maxAgeMs;
        const logsToRemove: string[] = [];
        
        existingLogs.forEach(log => {
          if (log.attempts > QUEUE_CLEANUP.MAX_ATTEMPTS || log.createdAt < cutoffTime) {
            logsToRemove.push(log.id);
          }
        });
        
        if (logsToRemove.length > 0) {
          await callLogStorage.removeSyncedLogs(logsToRemove);
          if (__DEV__) {
            console.log(`🗑️ Cleaned up ${logsToRemove.length} stale logs from queue`);
          }
        }
        
        // LOW PRIORITY BUG FIX: Only show toast in foreground (not in headless/background mode)
        if (!headlessMode) {
          Toast.show({
            type: 'warning',
            text1: 'Sync Queue Large',
            text2: `${existingLogs.length - logsToRemove.length} logs pending. Cleaned ${logsToRemove.length} old items.`,
            position: 'bottom',
            visibilityTime: 5000,
          });
        }
      }
      
      for (const rawLog of rawLogs) {
        try {
          const callLogData = await this.convertToCallLogData(rawLog, userId);
          
          // Check if already synced to server (prevents 422 duplicate errors)
          if (syncedIdsSet.has(callLogData.unique_id)) {
            console.log(`✅ Skipping - already synced to server: ${callLogData.unique_id}`);
            skippedCount++;
            continue;
          }
          
          // Check if already in unsynced queue (prevents queue bloat)
          if (existingIds.has(callLogData.unique_id)) {
            console.log(`⏭️ Skipping - already in queue: ${callLogData.unique_id}`);
            skippedCount++;
            continue;
          }
          
          // Log the converted data for debugging
          console.log(`💾 Saving log ${savedCount + 1}/${rawLogs.length}:`, {
            unique_id: callLogData.unique_id,
            date_time: callLogData.date_time,
            caller_number: callLogData.caller_number,
            call_type: callLogData.call_type,
            call_status: callLogData.call_status,
          });
          
          await callLogStorage.addUnsyncedLog(callLogData);
          savedCount++;
        } catch (error: any) {
          console.error('❌ Error converting call log (skipping):', {
            error: error.message,
            phoneNumber: rawLog.phoneNumber,
            timestamp: rawLog.timestamp,
          });
          skippedCount++;
          // Skip this invalid log instead of crashing
        }
      }

      console.log(`📊 Summary: Saved ${savedCount}, Skipped ${skippedCount}, Total ${rawLogs.length}`);

      // Update last processed timestamp
      // IMPORTANT: Add 1ms to avoid re-reading the same call in next query
      // Since minTimestamp uses >= comparison, we need to ensure we only get newer calls
      if (rawLogs.length > 0) {
        const timestamps = rawLogs.map(log => {
          const ts = log.timestamp || log.dateTime;
          return typeof ts === 'string' ? parseInt(ts, 10) : ts;
        });
        const latestTimestamp = Math.max(...timestamps);
        
        // CRITICAL: Only update if new timestamp is NEWER than stored one
        // This prevents timestamp from going backwards which would cause re-processing
        const currentStoredTimestamp = await callLogStorage.getLastProcessedTimestamp();
        const nextTimestamp = latestTimestamp + 1;
        
        if (nextTimestamp > currentStoredTimestamp) {
          await callLogStorage.setLastProcessedTimestamp(nextTimestamp);
          console.log(`✅ Updated last processed timestamp: ${nextTimestamp} (+1ms from latest: ${latestTimestamp})`);
        } else {
          console.log(`⚠️ Skipped timestamp update: ${nextTimestamp} <= current ${currentStoredTimestamp}`);
        }
      }

      console.log(`Saved ${savedCount} call logs to offline storage`);
      return savedCount;
    } catch (error) {
      console.error('Error processing new call logs:', error);
      return 0;
    }
  }

  /**
   * Sync unsynced logs to the API
   */
  async syncUnsyncedLogs(): Promise<{ success: boolean; syncedCount: number; error?: string }> {
    if (this.isSyncing) {
      console.log('⏭️ Sync already in progress, skipping...');
      return { success: false, syncedCount: 0, error: 'Sync already in progress' };
    }

    try {
      this.isSyncing = true;
      console.log('🔄 Starting sync process...');

      // Check if we have call log permission
      if (Platform.OS === 'android') {
        const hasPermission = await PermissionsAndroid.check(
          PermissionsAndroid.PERMISSIONS.READ_CALL_LOG
        );
        if (!hasPermission) {
          console.warn('❌ Call log permission not granted, skipping sync');
          return { success: false, syncedCount: 0, error: 'Call log permission not granted' };
        }
      }

      // Check network connection
      const isConnected = await this.checkNetworkConnection();
      if (!isConnected) {
        console.warn('❌ No network connection detected, sync postponed');
        Toast.show({
          type: 'info',
          text1: 'No Internet',
          text2: 'Call logs will sync automatically when internet is available',
          position: 'bottom',
          visibilityTime: 3000,
        });
        return { success: false, syncedCount: 0, error: 'No internet. Will retry upload automatically.' };
      }
      
      console.log('✅ Network connection verified');

      // Check if user is authenticated
      const token = await storageService.getAuthToken();
      if (!token) {
        console.warn('❌ User not authenticated, skipping sync');
        return { success: false, syncedCount: 0, error: 'Not authenticated' };
      }
      
      console.log('✅ User authenticated');

      // Get unsynced logs
      const unsyncedLogs = await callLogStorage.getUnsyncedLogs();
      if (unsyncedLogs.length === 0) {
        console.log('✅ No unsynced logs to push');
        return { success: true, syncedCount: 0 };
      }

      console.log(`📤 Syncing ${unsyncedLogs.length} unsynced logs...`);

      let syncedCount = 0;
      const syncedIds: string[] = [];

      // Push each log individually
      for (let i = 0; i < unsyncedLogs.length; i++) {
        const log = unsyncedLogs[i];
        try {
          if (__DEV__) {
            console.log(`📡 Pushing log ${i + 1}/${unsyncedLogs.length}:`, {
              id: log.id,
              unique_id: log.data.unique_id,
              caller_number: log.data.caller_number,
              call_type: log.data.call_type,
            });
          }
          
          const result = await callLogSyncAPI.pushCallLog(log.data);
          
          if (__DEV__) {
            console.log(`📨 API Response:`, result);
          }
          
          if (result.success) {
            syncedIds.push(log.id);
            syncedCount++;
            if (__DEV__) {
              console.log(`✅ Successfully synced log ${i + 1}/${unsyncedLogs.length} (${log.id})`);
            }
          } else {
            if (__DEV__) {
              console.warn(`⚠️ Failed to sync log ${i + 1}/${unsyncedLogs.length} (${log.id}): ${result.message}`);
            }
            await callLogStorage.updateLogAttempt(log.id);
          }
        } catch (error: any) {
          if (__DEV__) {
            console.error(`❌ Error syncing log ${log.id}:`, {
              message: error.message,
              status: error.response?.status,
              data: error.response?.data,
            });
          }
          
          // MEDIUM PRIORITY BUG FIX: Distinguish between retryable and permanent errors
          const status = error.response?.status;
          const isPermanentError = status && (status === 400 || status === 422);
          
          // Handle permanent errors (validation/duplicate) - remove from queue immediately
          if (isPermanentError) {
            const errorMessage = error.response?.data?.message || '';
            if (__DEV__) {
              console.log(`🗑️ Permanent error (${status}) for log ${log.id}: ${errorMessage}`);
              console.log('Removing from queue to prevent infinite retry');
            }
            
            // If it's a duplicate (422), count as synced since it exists on server
            if (status === 422 && (errorMessage.includes('unique id has already been taken') || 
                errorMessage.includes('duplicate') || errorMessage.includes('already exists'))) {
              syncedCount++;
            }
            
            syncedIds.push(log.id); // Remove from queue
            continue; // Skip attempt update and move to next log
          }
          
          // For retryable errors (network, 5xx), update attempt count
          await callLogStorage.updateLogAttempt(log.id);
          
          // Remove logs with too many failed retry attempts
          if (log.attempts >= RETRY_CONFIG.MAX_IMMEDIATE_ATTEMPTS) {
            if (__DEV__) {
              console.warn(`⚠️ Removing log ${log.id} after ${log.attempts} failed retry attempts`);
            }
            syncedIds.push(log.id);
          }
        }
      }

      // Remove successfully synced logs from queue
      if (syncedIds.length > 0) {
        await callLogStorage.removeSyncedLogs(syncedIds);
        // Track synced IDs to prevent future duplicate attempts
        await callLogStorage.addSyncedLogIds(syncedIds);
        console.log(`✅ Sync complete! Successfully synced ${syncedCount}/${unsyncedLogs.length} logs`);
        console.log(`📊 Removed ${syncedIds.length} logs from queue`);
        
        // Show success toast
        Toast.show({
          type: 'success',
          text1: 'Call Logs Synced',
          text2: `Successfully uploaded ${syncedCount} call log${syncedCount > 1 ? 's' : ''}`,
          position: 'bottom',
          visibilityTime: 3000,
        });
      } else {
        console.log(`⚠️ Sync complete but no logs were successfully synced (0/${unsyncedLogs.length})`);
      }

      // Update last sync time
      await callLogStorage.setLastSyncTime(Date.now());

      // Show notification on success
      if (syncedCount > 0) {
        this.showSyncNotification(syncedCount);
      }

      console.log(`Successfully synced ${syncedCount} call logs`);
      return { success: true, syncedCount };
    } catch (error: any) {
      console.error('Error syncing call logs:', error);
      return { success: false, syncedCount: 0, error: error.message };
    } finally {
      this.isSyncing = false;
    }
  }

  /**
   * Manual sync: Process new logs + sync unsynced logs
   */
  async manualSync(): Promise<{ success: boolean; processedCount: number; syncedCount: number; error?: string }> {
    try {
      console.log('Starting manual sync...');

      // Step 1: Process new call logs
      const processedCount = await this.processNewCallLogs();

      // Step 2: Sync all unsynced logs
      const syncResult = await this.syncUnsyncedLogs();

      return {
        success: syncResult.success,
        processedCount,
        syncedCount: syncResult.syncedCount,
        error: syncResult.error,
      };
    } catch (error: any) {
      console.error('Error in manual sync:', error);
      return {
        success: false,
        processedCount: 0,
        syncedCount: 0,
        error: error.message,
      };
    }
  }

  /**
   * Background sync (called by background fetch or AlarmManager)
   * Runs in headless mode - no UI or permission requests allowed
   */
  async backgroundSync(): Promise<void> {
    try {
      console.log('[BackgroundSync] Starting background sync...');
      
      // Check if we already have permission (don't request in headless mode)
      if (Platform.OS === 'android') {
        const hasPermission = await PermissionsAndroid.check(
          PermissionsAndroid.PERMISSIONS.READ_CALL_LOG
        );
        
        if (!hasPermission) {
          console.log('[BackgroundSync] No call log permission - skipping sync');
          return;
        }
      }
      
      // Process new logs (headless mode = true, won't request permissions)
      const processedCount = await this.processNewCallLogs(true);
      console.log(`[BackgroundSync] Processed ${processedCount} new call logs`);
      
      // Sync unsynced logs
      const syncResult = await this.syncUnsyncedLogs();
      console.log(`[BackgroundSync] Synced ${syncResult.syncedCount} call logs`);
      
      console.log('[BackgroundSync] Background sync completed successfully');
    } catch (error) {
      console.error('[BackgroundSync] Error in background sync:', error);
    }
  }

  /**
   * Show success notification (checks permission first)
   */
  private async showSyncNotification(count: number): Promise<void> {
    try {
      // Check if notification permission is granted (Android 13+ requires explicit permission)
      if (Platform.OS === 'android' && Platform.Version >= 33) {
        const granted = await PermissionsAndroid.check(
          PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS
        );
        
        if (!granted) {
          console.warn('⚠️ Cannot show notification: POST_NOTIFICATIONS permission not granted');
          return;
        }
      }
      // For Android < 13, notifications work by default (no runtime permission needed)
      
      console.log('📲 Showing sync notification for', count, 'logs');
      
      PushNotification.localNotification({
        channelId: 'call-log-sync',
        title: '✅ Call Logs Synced',
        message: `Successfully synced ${count} call log${count > 1 ? 's' : ''}`,
        playSound: false,
        vibrate: false,
        priority: 'high',
        visibility: 'public',
      });
    } catch (error) {
      console.error('Error showing notification:', error);
    }
  }

  /**
   * Get sync statistics
   */
  async getSyncStats(): Promise<{
    pendingCount: number;
    lastSyncTime: number | null;
  }> {
    const pendingCount = await callLogStorage.getUnsyncedCount();
    const lastSyncTime = await callLogStorage.getLastSyncTime();
    
    return {
      pendingCount,
      lastSyncTime,
    };
  }
}

export const callLogSyncService = new CallLogSyncService();
