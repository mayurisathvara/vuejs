import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {
  LoginRequest,
  LoginResponse,
  CallLogPushRequest,
  CallLogPushResponse,
  ApiResponse,
  PaginatedData,
  DashboardData,
  CallLogListItem,
  DailyCallVolumeData,
  MissedCallsData,
} from '../types';
import { appEvents, APP_EVENTS } from '../utils/eventEmitter';
import { RETRY_CONFIG } from '../constants';
import { API_BASE_URL } from '../config';

const BASE_URL = API_BASE_URL;

type AxiosErrorLike = {
  message?: string;
  code?: string;
  response?: { status?: number; data?: { message?: string } };
  config?: { url?: string };
};

// Helper function to retry API calls
const retryWithBackoff = async <T>(
  fn: () => Promise<T>,
  retries: number = RETRY_CONFIG.API_RETRY_ATTEMPTS,
  delay: number = RETRY_CONFIG.API_RETRY_DELAY
): Promise<T> => {
  try {
    return await fn();
  } catch (err: unknown) {
    const e = err as AxiosErrorLike;
    const noRetryStatuses = [400, 401, 403, 422];
    const status = e.response?.status;
    if (retries === 0 || (status !== undefined && noRetryStatuses.includes(status))) {
      throw err;
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
    } catch (error: unknown) {
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
      if (__DEV__) {
        console.log('📡 Pushing call log to API:', {
          url: `${BASE_URL}/v1/app/call-logs/push`,
          unique_id: callLog.unique_id,
        });
      }

      const response = await retryWithBackoff(
        () => apiClient.post('/v1/app/call-logs/push', callLog),
        RETRY_CONFIG.API_RETRY_ATTEMPTS,
        RETRY_CONFIG.API_RETRY_DELAY
      );

      if (__DEV__) {
        console.log('✅ API Response:', response.data);
      }

      // API returns: { "message": "Data received and logged." }
      // Convert to our expected format
      return {
        success: true,
        message: (response.data as { message?: string })?.message || 'Success',
        data: response.data as Record<string, unknown>,
      };
    } catch (err: unknown) {
      const e = err as AxiosErrorLike;
      if (__DEV__) {
        console.error('❌ Error pushing call log after retries:', {
          message: e.message,
          code: e.code,
          status: e.response?.status,
          url: e.config?.url,
        });
        if (e.response?.status === 422) {
          console.log('⚠️ Duplicate unique_id (422) - not retrying');
        }
      }
      throw err;
    }
  },

};

export const dashboardAPI = {
  /**
   * Fetch dashboard data for a specific date range
   */
  fetchDashboard: async (startDate: string, endDate: string): Promise<ApiResponse<DashboardData>> => {
    try {
      if (__DEV__) {
        console.log('📊 Fetching dashboard data:', { startDate, endDate });
      }

      const response = await apiClient.get<ApiResponse<DashboardData>>('/v1/app/dashboard', {
        params: {
          start_date: startDate,
          end_date: endDate,
        },
      });

      if (__DEV__) {
        console.log('✅ Dashboard data received');
      }
      return response.data;
    } catch (err: unknown) {
      const e = err as AxiosErrorLike;
      if (__DEV__) {
        console.error('❌ Error fetching dashboard data:', {
          message: e.message,
          status: e.response?.status,
        });
      }
      throw err;
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
  ): Promise<ApiResponse<PaginatedData<CallLogListItem>>> => {
    try {
      if (__DEV__) {
        console.log('📞 Fetching call logs:', { startDate, endDate, filterType, page });
      }

      const params: Record<string, string | number> = {
        start_date: startDate,
        end_date: endDate,
        page: page,
      };

      // Only add filter_type if it's not 'all'
      if (filterType !== 'all') {
        params.filter_type = filterType;
      }

      const response = await apiClient.get<ApiResponse<PaginatedData<CallLogListItem>>>(
        '/v1/app/call-logs',
        { params }
      );

      if (__DEV__) {
        console.log('✅ Call logs received:', response.data.data?.total, 'records');
      }
      return response.data;
    } catch (err: unknown) {
      const e = err as AxiosErrorLike;
      if (__DEV__) {
        console.error('❌ Error fetching call logs:', {
          message: e.message,
          status: e.response?.status,
        });
      }
      throw err;
    }
  },
};

export const analyticsAPI = {
  /**
   * Fetch daily call volume for last 7 days
   */
  fetchDailyCallVolume: async (): Promise<ApiResponse<DailyCallVolumeData>> => {
    try {
      if (__DEV__) {
        console.log('📊 Fetching daily call volume...');
      }

      const response = await apiClient.get<ApiResponse<DailyCallVolumeData>>(
        '/v1/app/analytics/daily-call-volume'
      );

      if (__DEV__) {
        console.log('✅ Daily call volume received');
      }
      return response.data;
    } catch (err: unknown) {
      const e = err as AxiosErrorLike;
      if (__DEV__) {
        console.error('❌ Error fetching daily call volume:', {
          message: e.message,
          status: e.response?.status,
        });
      }
      throw err;
    }
  },

  /**
   * Fetch peak call hours for a date range
   */
  fetchPeakHours: async (startDate: string, endDate: string): Promise<ApiResponse<Record<string, unknown>>> => {
    try {
      if (__DEV__) {
        console.log('📊 Fetching peak hours:', { startDate, endDate });
      }

      const response = await apiClient.get<ApiResponse<Record<string, unknown>>>(
        '/v1/app/analytics/peak-hours',
        {
          params: {
            start_date: startDate,
            end_date: endDate,
          },
        }
      );

      if (__DEV__) {
        console.log('✅ Peak hours received');
      }
      return response.data;
    } catch (err: unknown) {
      const e = err as AxiosErrorLike;
      if (__DEV__) {
        console.error('❌ Error fetching peak hours:', {
          message: e.message,
          status: e.response?.status,
        });
      }
      throw err;
    }
  },

  /**
   * Fetch missed calls analytics for a date range
   */
  fetchMissedCalls: async (startDate: string, endDate: string): Promise<ApiResponse<MissedCallsData>> => {
    try {
      if (__DEV__) {
        console.log('📊 Fetching missed calls:', { startDate, endDate });
      }

      const response = await apiClient.get<ApiResponse<MissedCallsData>>(
        '/v1/app/analytics/missed-calls',
        {
          params: {
            start_date: startDate,
            end_date: endDate,
          },
        }
      );

      if (__DEV__) {
        console.log('✅ Missed calls received');
      }
      return response.data;
    } catch (err: unknown) {
      const e = err as AxiosErrorLike;
      if (__DEV__) {
        console.error('❌ Error fetching missed calls:', {
          message: e.message,
          status: e.response?.status,
        });
      }
      throw err;
    }
  },
};

export default apiClient;
