import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { LoginRequest, LoginResponse, CallLogPushRequest, CallLogPushResponse } from '../types';
import { appEvents, APP_EVENTS } from '../utils/eventEmitter';
import { RETRY_CONFIG } from '../constants';

// API Configuration - can be overridden by environment variables
const API_CONFIG = {
  BASE_URL: process.env.API_BASE_URL || 'https://novauix.xyz/public/api',
  TIMEOUT: 30000,
};

const BASE_URL = API_CONFIG.BASE_URL;

// Helper function to retry API calls
const retryWithBackoff = async <T>(
  fn: () => Promise<T>,
  retries = RETRY_CONFIG.API_RETRY_ATTEMPTS,
  delay = RETRY_CONFIG.API_RETRY_DELAY
): Promise<T> => {
  try {
    return await fn();
  } catch (error: any) {
    // Don't retry on:
    // - 401/403: Auth errors
    // - 422: Validation errors (duplicate unique_id, bad data)
    // - 400: Bad request errors
    const noRetryStatuses = [400, 401, 403, 422];
    if (retries === 0 || noRetryStatuses.includes(error.response?.status)) {
      throw error;
    }
    
    if (__DEV__) {
      console.log(`⚠️ Request failed, retrying in ${delay}ms... (${retries} retries left)`);
    }
    await new Promise(resolve => setTimeout(resolve, delay));
    return retryWithBackoff(fn, retries - 1, delay * 2);
  }
};

// Create axios instance
const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 30000, // 30 seconds timeout
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle auth errors
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid, clear storage
      await AsyncStorage.multiRemove(['auth_token', 'user_mobile', 'user_data']);
      // Emit token expired event instead of using global variable
      appEvents.emit(APP_EVENTS.TOKEN_EXPIRED);
    }
    return Promise.reject(error);
  }
);

export const authAPI = {
  login: async (credentials: LoginRequest): Promise<LoginResponse> => {
    const response = await apiClient.post<LoginResponse>('/v1/app/login', credentials);
    return response.data;
  },

  logout: async (): Promise<void> => {
    try {
      await apiClient.post('/v1/app/logout');
    } catch (error) {
      if (__DEV__) {
        console.error('Logout API error:', error);
      }
      // Continue with local logout even if API call fails
    }
  },
};

export const callLogSyncAPI = {
  /**
   * Push a single call log to the API
   */
  pushCallLog: async (callLog: CallLogPushRequest): Promise<CallLogPushResponse> => {
    try {
      console.log('📡 Pushing call log to API:', {
        url: `${BASE_URL}/v1/app/call-logs/push`,
        unique_id: callLog.unique_id,
        caller_number: callLog.caller_number,
      });
      
      const response = await retryWithBackoff(
        () => apiClient.post('/v1/app/call-logs/push', callLog),
        RETRY_CONFIG.API_RETRY_ATTEMPTS,
        RETRY_CONFIG.API_RETRY_DELAY
      );
      
      console.log('✅ API Response:', response.data);
      
      // API returns: { "message": "Data received and logged." }
      // Convert to our expected format
      return {
        success: true,
        message: response.data?.message || 'Success',
        data: response.data,
      };
    } catch (error: any) {
      console.error('❌ Error pushing call log after retries:', {
        message: error.message,
        code: error.code,
        response: error.response?.data,
        status: error.response?.status,
        url: error.config?.url,
      });
      
      // Don't throw on 422 (duplicate) - let caller handle it
      // This prevents the retry logic from retrying duplicates
      if (error.response?.status === 422) {
        console.log('⚠️ Duplicate unique_id (422) - not retrying');
      }
      
      throw error;
    }
  },

  /**
   * Push multiple call logs in a batch
   */
  pushCallLogsBatch: async (callLogs: CallLogPushRequest[]): Promise<CallLogPushResponse> => {
    try {
      // Push each log individually (can be optimized to batch API if backend supports it)
      const results = await Promise.allSettled(
        callLogs.map(log => apiClient.post('/v1/app/call-logs/push', log))
      );

      const successCount = results.filter(r => r.status === 'fulfilled').length;
      const failedCount = results.filter(r => r.status === 'rejected').length;

      if (failedCount > 0) {
        console.warn(`Batch push: ${successCount} succeeded, ${failedCount} failed`);
      }

      return {
        success: successCount > 0,
        message: `Successfully pushed ${successCount} out of ${callLogs.length} call logs`,
        data: { successCount, failedCount, total: callLogs.length },
      };
    } catch (error: any) {
      console.error('Error pushing call logs batch:', error);
      throw error;
    }
  },
};

export const dashboardAPI = {
  /**
   * Fetch dashboard data for a specific date range
   */
  fetchDashboard: async (startDate: string, endDate: string): Promise<any> => {
    try {
      console.log('📊 Fetching dashboard data:', { startDate, endDate });
      
      const response = await apiClient.get('/v1/app/dashboard', {
        params: {
          start_date: startDate,
          end_date: endDate,
        },
      });
      
      console.log('✅ Dashboard data received:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('❌ Error fetching dashboard data:', {
        message: error.message,
        status: error.response?.status,
        data: error.response?.data,
      });
      throw error;
    }
  },
};

export const callLogsAPI = {
  /**
   * Fetch call logs with filters and pagination
   */
  fetchCallLogs: async (
    startDate: string,
    endDate: string,
    filterType: string = 'all',
    page: number = 1
  ): Promise<any> => {
    try {
      console.log('📞 Fetching call logs:', { startDate, endDate, filterType, page });
      
      const params: any = {
        start_date: startDate,
        end_date: endDate,
        page: page,
      };

      // Only add filter_type if it's not 'all'
      if (filterType !== 'all') {
        params.filter_type = filterType;
      }
      
      const response = await apiClient.get('/v1/app/call-logs', { params });
      
      console.log('✅ Call logs received:', response.data.data.total, 'records');
      return response.data;
    } catch (error: any) {
      console.error('❌ Error fetching call logs:', {
        message: error.message,
        status: error.response?.status,
        data: error.response?.data,
      });
      throw error;
    }
  },
};

export const analyticsAPI = {
  /**
   * Fetch daily call volume for last 7 days
   */
  fetchDailyCallVolume: async (): Promise<any> => {
    try {
      console.log('📊 Fetching daily call volume...');
      
      const response = await apiClient.get('/v1/app/analytics/daily-call-volume');
      
      console.log('✅ Daily call volume received:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('❌ Error fetching daily call volume:', {
        message: error.message,
        status: error.response?.status,
        data: error.response?.data,
      });
      throw error;
    }
  },

  /**
   * Fetch peak call hours for a date range
   */
  fetchPeakHours: async (startDate: string, endDate: string): Promise<any> => {
    try {
      console.log('📊 Fetching peak hours:', { startDate, endDate });
      
      const response = await apiClient.get('/v1/app/analytics/peak-hours', {
        params: {
          start_date: startDate,
          end_date: endDate,
        },
      });
      
      console.log('✅ Peak hours received:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('❌ Error fetching peak hours:', {
        message: error.message,
        status: error.response?.status,
        data: error.response?.data,
      });
      throw error;
    }
  },

  /**
   * Fetch missed calls analytics for a date range
   */
  fetchMissedCalls: async (startDate: string, endDate: string): Promise<any> => {
    try {
      console.log('📊 Fetching missed calls:', { startDate, endDate });
      
      const response = await apiClient.get('/v1/app/analytics/missed-calls', {
        params: {
          start_date: startDate,
          end_date: endDate,
        },
      });
      
      console.log('✅ Missed calls received:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('❌ Error fetching missed calls:', {
        message: error.message,
        status: error.response?.status,
        data: error.response?.data,
      });
      throw error;
    }
  },
};

export default apiClient;
