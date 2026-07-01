import React, { createContext, useContext, useEffect, useState, ReactNode, useRef } from 'react';
import { Alert } from 'react-native';
import { authAPI, dashboardAPI } from '../services/api';
import { storageService } from '../services/storage';
import { callLogStorage } from '../services/callLogStorage';
import { LoginRequest, AuthState } from '../types';
import { appEvents, APP_EVENTS } from '../utils/eventEmitter';
import { getErrorMessage, logError } from '../utils/errorHandler';


interface AuthContextType extends AuthState {
  login: (credentials: LoginRequest) => Promise<boolean>;
  logout: () => Promise<void>;
  checkAuthStatus: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [authState, setAuthState] = useState<AuthState>({
    isAuthenticated: false,
    isLoading: true,
    user: null,
    token: null,
    mobile: null,
  });
  
  const isLoggingOut = useRef(false);

  // Check authentication status on app start
  const checkAuthStatus = async () => {
    try {
      setAuthState(prev => ({ ...prev, isLoading: true }));
      
      const token = await storageService.getAuthToken();
      const mobile = await storageService.getUserMobile();
      const userData = await storageService.getUserData();

      if (token && mobile && userData) {
        // Validate token by making a test API call to dashboard
        try {
          const today = new Date().toISOString().split('T')[0];
          await dashboardAPI.fetchDashboard(today, today);

          // Token is valid
          setAuthState({
            isAuthenticated: true,
            isLoading: false,
            user: userData,
            token,
            mobile,
          });
        } catch (err: unknown) {
          const status = (err as { response?: { status?: number } }).response?.status;
          // Only clear auth on definitive auth errors (401/403).
          // Network errors (no response) mean the device is offline —
          // clearing auth here would log out users whenever they open
          // the app without internet, which is wrong.
          const isAuthError = status === 401 || status === 403;

          if (isAuthError) {
            if (__DEV__) {
              console.log('Token validation failed (auth error):', status);
            }
            await storageService.clearAuthData();
            setAuthState({
              isAuthenticated: false,
              isLoading: false,
              user: null,
              token: null,
              mobile: null,
            });
          } else {
            // Network error or server error — keep the user logged in
            if (__DEV__) {
              console.log('Token validation skipped (offline/server error):', (err as { code?: string }).code, status);
            }
            setAuthState({
              isAuthenticated: true,
              isLoading: false,
              user: userData,
              token,
              mobile,
            });
          }
        }
      } else {
        setAuthState({
          isAuthenticated: false,
          isLoading: false,
          user: null,
          token: null,
          mobile: null,
        });
      }
    } catch (error) {
      if (__DEV__) {
        console.error('Error checking auth status:', error);
      }
      await storageService.clearAuthData();
      setAuthState({
        isAuthenticated: false,
        isLoading: false,
        user: null,
        token: null,
        mobile: null,
      });
    }
  };

  // Login function
  const login = async (credentials: LoginRequest): Promise<boolean> => {
    try {
      setAuthState(prev => ({ ...prev, isLoading: true }));

      const response = await authAPI.login(credentials);
      
      // Extract user data from sim response
      const userData = {
        id: response.sim.id,
        name: response.sim.name,
        mobile: response.sim.mobile,
        department_name: response.sim.department?.name || 'Not Assigned',
      };
      
      // Store authentication data
      await storageService.storeAuthData(
        response.access_token,
        response.sim.mobile,
        userData
      );

      setAuthState({
        isAuthenticated: true,
        isLoading: false,
        user: userData,
        token: response.access_token,
        mobile: response.sim.mobile,
      });

      return true;
    } catch (err: unknown) {
      logError('AuthContext.login', err);
      const errorResponse = getErrorMessage(err);
      Alert.alert(errorResponse.title, errorResponse.message);
      setAuthState(prev => ({ ...prev, isLoading: false }));
      return false;
    }
  };

  // Logout function
  const logout = async () => {
    try {
      // Call logout API to revoke token
      await authAPI.logout();
      
      try {
        await callLogStorage.clearAllData();
        if (__DEV__) {
          console.log('[AuthContext] Cleared all call log data on logout');
        }
      } catch (error: unknown) {
        if (__DEV__) {
          console.error('[AuthContext] Error clearing call log data:', error);
        }
      }
      
      // Clear local storage
      await storageService.clearAuthData();
      
      setAuthState({
        isAuthenticated: false,
        isLoading: false,
        user: null,
        token: null,
        mobile: null,
      });
    } catch (error) {
      if (__DEV__) {
        console.error('Logout error:', error);
      }
      Alert.alert('Error', 'Failed to logout. Please try again.');
    }
  };

  useEffect(() => {
    checkAuthStatus();
  }, []);

  // Listen for token expiration events from API interceptor
  useEffect(() => {
    const handleTokenExpired = async () => {
      if (isLoggingOut.current) {
        return; // Already logging out
      }
      
      isLoggingOut.current = true;
      
      if (__DEV__) {
        console.log('[AuthContext] Token expired, logging out...');
      }
      
      // Clear storage and update state
      try {
        await storageService.clearAuthData();
        
        await callLogStorage.clearAllData();
        if (__DEV__) {
          console.log('[AuthContext] Cleared all call log data on token expiration');
        }
      } catch (error: unknown) {
        if (__DEV__) {
          console.error('[AuthContext] Error clearing auth data:', error);
        }
      }
      
      setAuthState({
        isAuthenticated: false,
        isLoading: false,
        user: null,
        token: null,
        mobile: null,
      });
      
      // Reset flag after state update
      setTimeout(() => {
        isLoggingOut.current = false;
      }, 500);
    };

    // Subscribe to token expiration events
    const unsubscribe = appEvents.on(APP_EVENTS.TOKEN_EXPIRED, handleTokenExpired);

    return () => {
      // Ensure flag is reset on unmount
      isLoggingOut.current = false;
      unsubscribe();
    };
  }, []);

  const contextValue: AuthContextType = {
    ...authState,
    login,
    logout,
    checkAuthStatus,
  };

  return (
    <AuthContext.Provider value={contextValue}>
      {children}
    </AuthContext.Provider>
  );
};

// Custom hook to use auth context
export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
