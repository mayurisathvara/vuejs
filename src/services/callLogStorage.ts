import AsyncStorage from '@react-native-async-storage/async-storage';
import { UnsyncedCallLog, CallLogData } from '../types';
import { RETRY_CONFIG } from '../constants';

const STORAGE_KEYS = {
  UNSYNCED_LOGS: '@call_logs:unsynced',
  SYNCED_LOG_IDS: '@call_logs:synced_ids',
  LAST_SYNC_TIME: '@call_logs:last_sync_time',
  LAST_PROCESSED_TIMESTAMP: '@call_logs:last_processed',
} as const;

class CallLogStorageService {
  /**
   * Save an unsynced call log to local storage
   * Uses atomic-like operation to minimize race conditions
   * Implements retry mechanism for resilience
   */
  async addUnsyncedLog(callLogData: CallLogData): Promise<void> {
    const maxRetries = 3;
    let lastError: Error | null = null;

    for (let attempt = 0; attempt < maxRetries; attempt++) {
      try {
        // Get current logs with fresh read to minimize race window
        const logs = await this.getUnsyncedLogs();
        
        const newLog: UnsyncedCallLog = {
          id: callLogData.unique_id,
          data: callLogData,
          attempts: 0,
          lastAttempt: 0,
          createdAt: Date.now(),
        };

        // Check for duplicates in unsynced queue
        const existsInQueue = logs.some(log => log.id === newLog.id);
        if (existsInQueue) {
          if (__DEV__) {
            console.log(`⏭️ Log ${newLog.id} already in sync queue, skipping`);
          }
          return;
        }
        
        // Check if already synced (prevents re-adding previously synced logs)
        const syncedIds = await this.getSyncedLogIds();
        const alreadySynced = syncedIds.includes(newLog.id);
        if (alreadySynced) {
          if (__DEV__) {
            console.log(`✅ Log ${newLog.id} was already synced, skipping`);
          }
          return;
        }

        // Add to queue with retry on write failure
        logs.push(newLog);
        await AsyncStorage.setItem(STORAGE_KEYS.UNSYNCED_LOGS, JSON.stringify(logs));
        if (__DEV__) {
          console.log(`✅ Added log ${newLog.id} to sync queue (queue size: ${logs.length})`);
        }
        return; // Success - exit retry loop
      } catch (error) {
        lastError = error as Error;
        if (__DEV__) {
          console.error(`Error adding unsynced log (attempt ${attempt + 1}/${maxRetries}):`, error);
        }
        
        // Wait before retry with exponential backoff
        if (attempt < maxRetries - 1) {
          await new Promise(resolve => setTimeout(resolve, 100 * Math.pow(2, attempt)));
        }
      }
    }
    
    // All retries failed
    if (__DEV__) {
      console.error('Failed to add unsynced log after all retries');
    }
    throw lastError || new Error('Failed to add unsynced log');
  }

  /**
   * Get all unsynced call logs
   */
  async getUnsyncedLogs(): Promise<UnsyncedCallLog[]> {
    try {
      const logsJson = await AsyncStorage.getItem(STORAGE_KEYS.UNSYNCED_LOGS);
      if (!logsJson) return [];
      
      const logs: UnsyncedCallLog[] = JSON.parse(logsJson);
      return logs;
    } catch (error) {
      if (__DEV__) {
        console.error('Error getting unsynced logs:', error);
      }
      return [];
    }
  }

  /**
   * Remove a synced log from storage
   */
  async removeUnsyncedLog(logId: string): Promise<void> {
    try {
      const logs = await this.getUnsyncedLogs();
      const filteredLogs = logs.filter(log => log.id !== logId);
      await AsyncStorage.setItem(STORAGE_KEYS.UNSYNCED_LOGS, JSON.stringify(filteredLogs));
    } catch (error) {
      console.error('Error removing unsynced log:', error);
      throw error;
    }
  }

  /**
   * Remove multiple synced logs from storage
   * Implements retry mechanism to prevent data loss from write failures
   */
  async removeSyncedLogs(logIds: string[]): Promise<void> {
    const maxRetries = 3;
    let lastError: Error | null = null;

    for (let attempt = 0; attempt < maxRetries; attempt++) {
      try {
        const logs = await this.getUnsyncedLogs();
        const filteredLogs = logs.filter(log => !logIds.includes(log.id));
        await AsyncStorage.setItem(STORAGE_KEYS.UNSYNCED_LOGS, JSON.stringify(filteredLogs));
        return; // Success
      } catch (error) {
        lastError = error as Error;
        if (__DEV__) {
          console.error(`Error removing synced logs (attempt ${attempt + 1}/${maxRetries}):`, error);
        }
        
        // Wait before retry with exponential backoff
        if (attempt < maxRetries - 1) {
          await new Promise(resolve => setTimeout(resolve, 100 * Math.pow(2, attempt)));
        }
      }
    }
    
    if (__DEV__) {
      console.error('Failed to remove synced logs after all retries');
    }
    throw lastError || new Error('Failed to remove synced logs');
  }

  /**
   * Update attempt count for a failed sync
   * Implements retry mechanism for resilience
   */
  async updateLogAttempt(logId: string): Promise<void> {
    const maxRetries = 3;
    let lastError: Error | null = null;

    for (let attempt = 0; attempt < maxRetries; attempt++) {
      try {
        const logs = await this.getUnsyncedLogs();
        const updatedLogs = logs.map(log => {
          if (log.id === logId) {
            return {
              ...log,
              attempts: log.attempts + 1,
              lastAttempt: Date.now(),
            };
          }
          return log;
        });
        await AsyncStorage.setItem(STORAGE_KEYS.UNSYNCED_LOGS, JSON.stringify(updatedLogs));
        return; // Success
      } catch (error) {
        lastError = error as Error;
        if (__DEV__) {
          console.error(`Error updating log attempt (attempt ${attempt + 1}/${maxRetries}):`, error);
        }
        
        // Wait before retry with exponential backoff
        if (attempt < maxRetries - 1) {
          await new Promise(resolve => setTimeout(resolve, 100 * Math.pow(2, attempt)));
        }
      }
    }
    
    if (__DEV__) {
      console.error('Failed to update log attempt after all retries');
    }
    throw lastError || new Error('Failed to update log attempt');
  }

  /**
   * Get count of unsynced logs
   */
  async getUnsyncedCount(): Promise<number> {
    try {
      const logs = await this.getUnsyncedLogs();
      return logs.length;
    } catch (error) {
      if (__DEV__) {
        console.error('Error getting unsynced count:', error);
      }
      return 0;
    }
  }

  /**
   * Clear all unsynced logs (use with caution)
   */
  async clearAllUnsyncedLogs(): Promise<void> {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.UNSYNCED_LOGS, JSON.stringify([]));
    } catch (error) {
      if (__DEV__) {
        console.error('Error clearing unsynced logs:', error);
      }
      throw error;
    }
  }

  /**
   * Save last sync time
   */
  async setLastSyncTime(timestamp: number): Promise<void> {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.LAST_SYNC_TIME, timestamp.toString());
    } catch (error) {
      console.error('Error setting last sync time:', error);
    }
  }

  /**
   * Get last sync time
   */
  async getLastSyncTime(): Promise<number | null> {
    try {
      const timestamp = await AsyncStorage.getItem(STORAGE_KEYS.LAST_SYNC_TIME);
      return timestamp ? parseInt(timestamp, 10) : null;
    } catch (error) {
      if (__DEV__) {
        console.error('Error getting last sync time:', error);
      }
      return null;
    }
  }

  /**
   * Save last processed call log timestamp (to avoid re-processing old logs)
   */
  async setLastProcessedTimestamp(timestamp: number): Promise<void> {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.LAST_PROCESSED_TIMESTAMP, timestamp.toString());
    } catch (error) {
      if (__DEV__) {
        console.error('Error setting last processed timestamp:', error);
      }
    }
  }

  /**
   * Get last processed call log timestamp
   */
  async getLastProcessedTimestamp(): Promise<number> {
    try {
      const timestamp = await AsyncStorage.getItem(STORAGE_KEYS.LAST_PROCESSED_TIMESTAMP);
      // Default to 24 hours ago if no timestamp exists
      return timestamp ? parseInt(timestamp, 10) : Date.now() - (24 * 60 * 60 * 1000);
    } catch (error) {
      if (__DEV__) {
        console.error('Error getting last processed timestamp:', error);
      }
      return Date.now() - (24 * 60 * 60 * 1000);
    }
  }

  /**
   * Add synced log IDs to prevent re-syncing
   * Implements automatic cleanup to prevent unlimited growth
   */
  async addSyncedLogIds(logIds: string[]): Promise<void> {
    try {
      const existingIds = await this.getSyncedLogIds();
      const updatedIds = [...new Set([...existingIds, ...logIds])];
      
      // Enforce limit to prevent storage bloat
      if (updatedIds.length > RETRY_CONFIG.SYNCED_IDS_LIMIT) {
        // Keep only the most recent IDs (FIFO cleanup)
        const idsToKeep = updatedIds.slice(-RETRY_CONFIG.SYNCED_IDS_LIMIT);
        await AsyncStorage.setItem(STORAGE_KEYS.SYNCED_LOG_IDS, JSON.stringify(idsToKeep));
        
        if (__DEV__) {
          console.log(`🗑️ Cleaned up ${updatedIds.length - idsToKeep.length} old synced IDs (limit: ${RETRY_CONFIG.SYNCED_IDS_LIMIT})`);
        }
      } else {
        await AsyncStorage.setItem(STORAGE_KEYS.SYNCED_LOG_IDS, JSON.stringify(updatedIds));
        if (__DEV__) {
          console.log(`📝 Tracked ${logIds.length} synced log IDs (total: ${updatedIds.length})`);
        }
      }
    } catch (error) {
      if (__DEV__) {
        console.error('Error adding synced log IDs:', error);
      }
      // Don't throw - this is not critical for app functionality
    }
  }

  /**
   * Get list of already synced log IDs
   */
  async getSyncedLogIds(): Promise<string[]> {
    try {
      const idsJson = await AsyncStorage.getItem(STORAGE_KEYS.SYNCED_LOG_IDS);
      if (!idsJson) return [];
      
      const ids: string[] = JSON.parse(idsJson);
      return ids;
    } catch (error) {
      if (__DEV__) {
        console.error('Error getting synced log IDs:', error);
      }
      return [];
    }
  }

  /**
   * Clear synced log IDs (for testing/reset)
   */
  async clearSyncedLogIds(): Promise<void> {
    try {
      await AsyncStorage.removeItem(STORAGE_KEYS.SYNCED_LOG_IDS);
    } catch (error) {
      console.error('Error clearing synced log IDs:', error);
    }
  }

  /**
   * Clear all call log storage — call on logout or user switch to avoid
   * one user's timestamps/synced-IDs leaking into the next session.
   */
  async clearAllData(): Promise<void> {
    try {
      await AsyncStorage.multiRemove([
        STORAGE_KEYS.UNSYNCED_LOGS,
        STORAGE_KEYS.SYNCED_LOG_IDS,
        STORAGE_KEYS.LAST_SYNC_TIME,
        STORAGE_KEYS.LAST_PROCESSED_TIMESTAMP,
      ]);
    } catch (error) {
      if (__DEV__) {
        console.error('Error clearing all call log data:', error);
      }
      throw error;
    }
  }
}

export const callLogStorage = new CallLogStorageService();
