/**
 * Application constants
 */

// Tab Navigation
export const TAB_INDICES = {
  DASHBOARD: 0,
  CALL_LOGS: 1,
  ANALYTICS: 2,
  SETTINGS: 3,
} as const;

// Background Sync
export const SYNC_INTERVALS = {
  BACKGROUND_FETCH: 15, // 15 minutes
  STATS_UPDATE: 30000, // 30 seconds
  NETWORK_RECONNECT_DELAY: 2000, // 2 seconds
  INITIAL_SYNC_DELAY: 3000, // 3 seconds after onboarding
} as const;

// Retry Configuration
export const RETRY_CONFIG = {
  MAX_IMMEDIATE_ATTEMPTS: 5, // Maximum retry attempts before removal
  API_RETRY_ATTEMPTS: 3, // API call retries with backoff
  API_RETRY_DELAY: 1000, // Initial retry delay (1 second)
  SYNCED_IDS_LIMIT: 2000, // Maximum synced IDs to keep in storage
} as const;

// UI and Animation Timings
export const UI_TIMINGS = {
  CONSENT_CHECK_INTERVAL: 500, // Consent recheck interval (milliseconds)
  NAVIGATION_CHECK_INTERVAL: 100, // Navigation state check (deprecated - now using events)
  QUEUE_WARNING_THRESHOLD: 500, // Show warning when queue exceeds this size
  SPLASH_DURATION: 3000, // Splash screen display duration (ms)
} as const;

// Phone Number Formatting
export const PHONE_FORMAT = {
  MAX_DIGITS: 10, // Maximum digits for mobile number
  COUNTRY_CODE: '+91', // Default country code
} as const;

// Queue Cleanup
export const QUEUE_CLEANUP = {
  MAX_AGE_DAYS: 7, // Remove logs older than this many days
  MAX_ATTEMPTS: 10, // Remove logs with more attempts than this
} as const;

// Timeouts
export const TIMEOUTS = {
  API_REQUEST: 30000, // 30 seconds
  NETWORK_CHECK: 5000, // 5 seconds
} as const;

// Date Validation
export const DATE_LIMITS = {
  MIN_TIMESTAMP: 946684800000, // Jan 1, 2000
  MAX_TIMESTAMP: 4102444800000, // Jan 1, 2100
} as const;

// Legal URLs — update these to public HTTPS URLs before Play Store submission
export const LEGAL_URLS = {
  PRIVACY_POLICY: 'http://192.168.1.9:8000/privacy',
  TERMS_CONDITIONS: 'http://192.168.1.9:8000/terms',
} as const;
