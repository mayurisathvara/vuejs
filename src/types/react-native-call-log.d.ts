declare module 'react-native-call-log' {
  export interface CallLog {
    phoneNumber: string;
    type: string;
    timestamp: number;
    duration: number;
    name?: string;
  }

  export interface CallLogFilter {
    minTimestamp?: number;
    maxTimestamp?: number;
  }

  export default class CallLogs {
    static load(limit: number, filter?: CallLogFilter): Promise<CallLog[]>;
  }
}
