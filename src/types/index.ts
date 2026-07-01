export interface Department {
  id: number;
  name: string;
  organization_id: number;
  created_at: string;
  updated_at: string;
}

export interface Organization {
  id: number;
  name: string;
  email: string;
  mobile: string;
  app_login_code: string;
  description: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface Sim {
  id: number;
  mobile: string;
  name: string;
  organization_id: number;
  department_id: number;
  status: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  organization: Organization;
  department: Department;
}

export interface User {
  id: number;
  name: string;
  mobile: string;
  department_name?: string;
}

export interface LoginRequest {
  mobile: string;
  app_login_code: string;
}

export interface LoginResponse {
  access_token: string;
  token_type: string;
  sim: Sim;
}

export interface AuthState {
  isAuthenticated: boolean;
  isLoading: boolean;
  user: User | null;
  token: string | null;
  mobile: string | null;
}

export interface ApiError {
  message: string;
  status?: number;
  code?: string;
}

// Call Log Types
export type CallType = 'inbound' | 'outbound';
export type CallStatus = 'Answered' | 'Missed' | 'No Answer';
export type ContactStatus = 'Saved' | 'Not Saved';
export type HangupBy = 'user' | 'remote';

export interface RawCallLog {
  phoneNumber: string;
  type: string; // 'INCOMING', 'OUTGOING', 'MISSED'
  dateTime: number | string; // timestamp in milliseconds (can be string from native)
  duration: number; // in seconds
  name?: string;
  timestamp: number | string; // can be string from native library
}

export interface CallLogData {
  unique_id: string;
  user_id: string;
  date_time: string; // formatted: "YYYY-MM-DD HH:mm:ss"
  call_status: CallStatus;
  caller_number: string;
  call_type: CallType;
  caller_duration: string; // total duration in seconds
  conversation_duration: string; // actual talk time
  ring_duration: string; // ring time before answer
  contact_status: ContactStatus;
  name: string;
  hangup_by: HangupBy;
}

export interface CallLogPushRequest {
  unique_id: string;
  user_id: string;
  date_time: string;
  call_status: CallStatus;
  caller_number: string;
  call_type: CallType;
  caller_duration: string;
  conversation_duration: string;
  ring_duration: string;
  contact_status: ContactStatus;
  name: string;
  hangup_by: HangupBy;
}

export interface CallLogPushResponse {
  success: boolean;
  message: string;
  data?: Record<string, unknown>;
}

// Generic API response wrappers
export interface ApiResponse<T> {
  status: boolean;
  data: T;
}

export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

// Dashboard API types
export interface DashboardData {
  summary: {
    total_calls: { value: number; change: string };
    answer_rate: { value: string; change: string };
    avg_duration: { value: string; change: string };
  };
  outbound: {
    answered: number;
    no_answer: number;
    total: number;
    change: string;
  };
  inbound: {
    answered: number;
    missed: number;
    total: number;
    change: string;
  };
  alerts: {
    missed_calls: { value: number };
  };
}

// Call log list item (API list response format)
export interface CallLogListItem {
  id: number;
  unique_id: string;
  time: string;
  date_time: string;
  call_type: string;
  call_status: string;
  caller_number: string;
  caller_duration: string;
  contact_name: string | null;
}

// Analytics API types
export interface DailyVolumeDay {
  date: string;
  count: number;
}

export interface DailyCallVolumeData {
  days: DailyVolumeDay[];
  avg_per_day: number;
}

export interface MissedCallsData {
  total_missed: number;
  returned_calls: number;
  callback_pending: number;
}

export interface UnsyncedCallLog {
  id: string;
  data: CallLogData;
  attempts: number;
  lastAttempt: number;
  createdAt: number;
}

export interface CallLogSyncState {
  isSyncing: boolean;
  lastSyncTime: number | null;
  pendingCount: number;
  error: string | null;
}
