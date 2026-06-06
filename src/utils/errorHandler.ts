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
 * Get user-friendly error message based on HTTP status code
 */
export const getErrorMessage = (error: any): ErrorResponse => {
  const status = error?.response?.status;
  const apiMessage = error?.response?.data?.message;
  
  // 401 Unauthorized - Token expired or invalid
  if (status === 401) {
    // Check if this is a login error (credentials invalid) vs token expired
    const isLoginError = apiMessage?.toLowerCase().includes('invalid') || 
                         apiMessage?.toLowerCase().includes('unauthorized') ||
                         apiMessage?.toLowerCase().includes('credentials');
    
    if (isLoginError) {
      return {
        title: 'Login Failed',
        message: 'Invalid mobile number or app login code. Please check and try again.',
        shouldShowToUser: true, // Show for login errors
      };
    }
    
    return {
      title: 'Session Expired',
      message: 'Your session has expired. Please login again.',
      shouldShowToUser: false, // Don't show - auto redirect to login
    };
  }
  
  // 403 Forbidden - No permission
  if (status === 403) {
    return {
      title: 'Access Denied',
      message: 'You do not have permission to access this resource.',
      shouldShowToUser: true,
    };
  }
  
  // 404 Not Found
  if (status === 404) {
    return {
      title: 'Not Found',
      message: 'The requested data could not be found.',
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
      message: 'Please wait a moment before trying again.',
      shouldShowToUser: true,
    };
  }
  
  // 500 Internal Server Error
  if (status === 500) {
    return {
      title: 'Server Error',
      message: 'Something went wrong on our end. Please try again later.',
      shouldShowToUser: true,
    };
  }
  
  // 502 Bad Gateway
  if (status === 502) {
    return {
      title: 'Service Unavailable',
      message: 'The service is temporarily unavailable. Please try again later.',
      shouldShowToUser: true,
    };
  }
  
  // 503 Service Unavailable
  if (status === 503) {
    return {
      title: 'Service Unavailable',
      message: 'The service is under maintenance. Please try again later.',
      shouldShowToUser: true,
    };
  }
  
  // Network Error (no response)
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
  
  // Generic error with API message
  if (apiMessage) {
    return {
      title: 'Error',
      message: apiMessage,
      shouldShowToUser: true,
    };
  }
  
  // Fallback generic error
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
    console.error(`[${context}] Error:`, {
      status: error?.response?.status,
      message: error?.message,
      data: error?.response?.data,
      code: error?.code,
    });
  }
};
