import AsyncStorage from '@react-native-async-storage/async-storage';

export const STORAGE_KEYS = {
  AUTH_TOKEN: 'auth_token',
  USER_MOBILE: 'user_mobile',
  USER_DATA: 'user_data',
  USER_CONSENT: 'user_consent',
} as const;

export const storageService = {
  // Store authentication data
  storeAuthData: async (token: string, mobile: string, userData: any) => {
    try {
      await AsyncStorage.multiSet([
        [STORAGE_KEYS.AUTH_TOKEN, token],
        [STORAGE_KEYS.USER_MOBILE, mobile],
        [STORAGE_KEYS.USER_DATA, JSON.stringify(userData)],
      ]);
    } catch (error) {
      console.error('Error storing auth data:', error);
      throw error;
    }
  },

  // Get authentication token
  getAuthToken: async (): Promise<string | null> => {
    try {
      return await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
    } catch (error) {
      console.error('Error getting auth token:', error);
      return null;
    }
  },

  // Get user mobile
  getUserMobile: async (): Promise<string | null> => {
    try {
      return await AsyncStorage.getItem(STORAGE_KEYS.USER_MOBILE);
    } catch (error) {
      console.error('Error getting user mobile:', error);
      return null;
    }
  },

  // Get user data
  getUserData: async (): Promise<any | null> => {
    try {
      const userData = await AsyncStorage.getItem(STORAGE_KEYS.USER_DATA);
      return userData ? JSON.parse(userData) : null;
    } catch (error) {
      console.error('Error getting user data:', error);
      return null;
    }
  },

  // Clear all authentication data
  clearAuthData: async () => {
    try {
      await AsyncStorage.multiRemove([
        STORAGE_KEYS.AUTH_TOKEN,
        STORAGE_KEYS.USER_MOBILE,
        STORAGE_KEYS.USER_DATA,
      ]);
    } catch (error) {
      console.error('Error clearing auth data:', error);
      throw error;
    }
  },

  // Check if user is authenticated
  isAuthenticated: async (): Promise<boolean> => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
      return !!token;
    } catch (error) {
      console.error('Error checking authentication:', error);
      return false;
    }
  },

  // Generic storage methods
  setItem: async (key: string, value: string) => {
    try {
      await AsyncStorage.setItem(key, value);
    } catch (error) {
      console.error('Error setting item:', error);
      throw error;
    }
  },

  getItem: async (key: string): Promise<string | null> => {
    try {
      return await AsyncStorage.getItem(key);
    } catch (error) {
      console.error('Error getting item:', error);
      return null;
    }
  },

  removeItem: async (key: string) => {
    try {
      await AsyncStorage.removeItem(key);
    } catch (error) {
      console.error('Error removing item:', error);
      throw error;
    }
  },

  // Consent helpers
  setUserConsent: async (consented: boolean) => {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.USER_CONSENT, consented ? 'true' : 'false');
    } catch (error) {
      console.error('Error setting user consent:', error);
      throw error;
    }
  },

  getUserConsent: async (): Promise<boolean> => {
    try {
      const value = await AsyncStorage.getItem(STORAGE_KEYS.USER_CONSENT);
      return value === 'true';
    } catch (error) {
      console.error('Error getting user consent:', error);
      return false;
    }
  },
};
