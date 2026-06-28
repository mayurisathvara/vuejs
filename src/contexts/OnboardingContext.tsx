import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { storageService } from '../services/storage';

interface OnboardingContextType {
  isOnboardingCompleted: boolean;
  isLoading: boolean;
  hasConsent: boolean;
  completeOnboarding: () => Promise<void>;
  resetOnboarding: () => Promise<void>;
  updateConsent: (value: boolean) => Promise<void>;
}

const OnboardingContext = createContext<OnboardingContextType | undefined>(undefined);

interface OnboardingProviderProps {
  children: ReactNode;
}

const ONBOARDING_KEY = 'onboarding_completed';

export const OnboardingProvider: React.FC<OnboardingProviderProps> = ({ children }) => {
  const [isOnboardingCompleted, setIsOnboardingCompleted] = useState(false);
  const [hasConsent, setHasConsent] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  const checkOnboardingStatus = React.useCallback(async () => {
    try {
      setIsLoading(true);
      const [completed, consented] = await Promise.all([
        storageService.getItem(ONBOARDING_KEY),
        storageService.getUserConsent(),
      ]);
      setIsOnboardingCompleted(completed === 'true');
      setHasConsent(!!consented);
    } catch (error) {
      if (__DEV__) {
        console.error('Error checking onboarding status:', error);
      }
      setIsOnboardingCompleted(false);
      setHasConsent(false);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const completeOnboarding = async () => {
    try {
      await storageService.setItem(ONBOARDING_KEY, 'true');
      setIsOnboardingCompleted(true);
    } catch (error) {
      if (__DEV__) {
        console.error('Error completing onboarding:', error);
      }
    }
  };

  const updateConsent = async (value: boolean) => {
    try {
      await storageService.setUserConsent(value);
      setHasConsent(value);
    } catch (error) {
      if (__DEV__) {
        console.error('Error updating consent:', error);
      }
      throw error;
    }
  };

  const resetOnboarding = async () => {
    try {
      await storageService.removeItem(ONBOARDING_KEY);
      await storageService.removeItem('user_consent');
      setIsOnboardingCompleted(false);
      setHasConsent(false);
      if (__DEV__) {
        console.log('[OnboardingContext] Onboarding and consent reset');
      }
    } catch (error) {
      if (__DEV__) {
        console.error('Error resetting onboarding:', error);
      }
    }
  };

  useEffect(() => {
    checkOnboardingStatus();
  }, [checkOnboardingStatus]);

  const contextValue: OnboardingContextType = {
    isOnboardingCompleted,
    isLoading,
    hasConsent,
    completeOnboarding,
    resetOnboarding,
    updateConsent,
  };

  return (
    <OnboardingContext.Provider value={contextValue}>
      {children}
    </OnboardingContext.Provider>
  );
};

export const useOnboarding = (): OnboardingContextType => {
  const context = useContext(OnboardingContext);
  if (context === undefined) {
    throw new Error('useOnboarding must be used within an OnboardingProvider');
  }
  return context;
};
