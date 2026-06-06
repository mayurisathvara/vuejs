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
  data?: any;
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
