/**
 * Logger utility for conditional logging based on environment
 * In production, only errors are logged. In development, all levels are logged.
 */

const isDevelopment = __DEV__;

export const logger = {
  /**
   * General logging - development only
   */
  log: (...args: any[]) => {
    if (isDevelopment) {
      console.log(...args);
    }
  },
  
  /**
   * Info logging - development only
   */
  info: (...args: any[]) => {
    if (isDevelopment) {
      console.info(...args);
    }
  },
  
  /**
   * Warning logging - development only
   */
  warn: (...args: any[]) => {
    if (isDevelopment) {
      console.warn(...args);
    }
  },
  
  /**
   * Error logging - always logged (development and production)
   * Critical for debugging production issues
   */
  error: (...args: any[]) => {
    console.error(...args);
  },
  
  /**
   * Debug logging - development only
   */
  debug: (...args: any[]) => {
    if (isDevelopment) {
      console.debug(...args);
    }
  },
};

/**
 * Helper to check if running in development mode
 */
export const isDevMode = (): boolean => isDevelopment;
