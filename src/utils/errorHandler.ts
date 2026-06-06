/**
 * Centralized Error Handler Utility
 * Provides user-friendly error messages for different HTTP status codes
 */

export interface ApiError {
  status?: number;
  message?: string;
  data?: any;
}

export interface ErrorResponse {
  title: string;
  message: string;
  shouldShowToUser: boolean;
}

/**
 * Get user-friendly error message based on HTTP status code.
 * API response message always takes priority over generic fallbacks.
 */
export const getErrorMessage = (error: any): ErrorResponse => {
  const status = error?.response?.status;
  const apiMessage = error?.response?.data?.message;

  // Network errors (no HTTP response at all)
  if (error?.code === 'ECONNABORTED' || error?.message?.includes('timeout')) {
    return {
      title: 'Request Timeout',
      message: 'The request took too long. Please check your connection and try again.',
      shouldShowToUser: true,
    };
  }

  if (error?.code === 'ERR_NETWORK' || !error?.response) {
    return {
      title: 'Network Error',
      message: 'Please check your internet connection and try again.',
      shouldShowToUser: true,
    };
  }

  // 401 Unauthorized
  if (status === 401) {
    const isLoginError = apiMessage?.toLowerCase().includes('invalid') ||
                         apiMessage?.toLowerCase().includes('unauthorized') ||
                         apiMessage?.toLowerCase().includes('credentials');

    if (isLoginError) {
      return {
        title: 'Login Failed',
        message: apiMessage || 'Invalid mobile number or app login code. Please check and try again.',
        shouldShowToUser: true,
      };
    }

    return {
      title: 'Session Expired',
      message: apiMessage || 'Your session has expired. Please login again.',
      shouldShowToUser: false, // auto-redirect to login, no alert needed
    };
  }

  // 403 Forbidden
  if (status === 403) {
    return {
      title: 'Access Denied',
      message: apiMessage || 'You do not have permission to access this resource.',
      shouldShowToUser: true,
    };
  }

  // 404 Not Found
  if (status === 404) {
    return {
      title: 'Not Found',
      message: apiMessage || 'The requested data could not be found.',
      shouldShowToUser: true,
    };
  }

  // 422 Validation Error
  if (status === 422) {
    return {
      title: 'Validation Error',
      message: apiMessage || 'Please check your input and try again.',
      shouldShowToUser: true,
    };
  }

  // 429 Too Many Requests
  if (status === 429) {
    return {
      title: 'Too Many Requests',
      message: apiMessage || 'Please wait a moment before trying again.',
      shouldShowToUser: true,
    };
  }

  // 500 Internal Server Error
  if (status === 500) {
    return {
      title: 'Server Error',
      message: apiMessage || 'Something went wrong on our end. Please try again later.',
      shouldShowToUser: true,
    };
  }

  // 502 Bad Gateway
  if (status === 502) {
    return {
      title: 'Service Unavailable',
      message: apiMessage || 'The service is temporarily unavailable. Please try again later.',
      shouldShowToUser: true,
    };
  }

  // 503 Service Unavailable
  if (status === 503) {
    return {
      title: 'Service Unavailable',
      message: apiMessage || 'The service is under maintenance. Please try again later.',
      shouldShowToUser: true,
    };
  }

  // Any other HTTP error with an API message
  if (apiMessage) {
    return {
      title: 'Error',
      message: apiMessage,
      shouldShowToUser: true,
    };
  }

  // Fallback
  return {
    title: 'Something went wrong',
    message: 'An unexpected error occurred. Please try again.',
    shouldShowToUser: true,
  };
};

/**
 * Log error for debugging (only in dev mode)
 */
export const logError = (context: string, error: any) => {
  if (__DEV__) {
    console.warn(`[${context}] Error:`, {
      status: error?.response?.status,
      message: error?.message,
      data: error?.response?.data,
      code: error?.code,
    });
  }
};
