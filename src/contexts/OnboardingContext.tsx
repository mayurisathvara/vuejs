import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { storageService } from '../services/storage';

interface OnboardingContextType {
  isOnboardingCompleted: boolean;
  isLoading: boolean;
  completeOnboarding: () => Promise<void>;
  resetOnboarding: () => Promise<void>;
}

const OnboardingContext = createContext<OnboardingContextType | undefined>(undefined);

interface OnboardingProviderProps {
  children: ReactNode;
}

const ONBOARDING_KEY = 'onboarding_completed';

export const OnboardingProvider: React.FC<OnboardingProviderProps> = ({ children }) => {
  const [isOnboardingCompleted, setIsOnboardingCompleted] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Check onboarding status on mount
  const checkOnboardingStatus = React.useCallback(async () => {
    try {
      setIsLoading(true);
      const completed = await storageService.getItem(ONBOARDING_KEY);
      setIsOnboardingCompleted(completed === 'true');
    } catch (error) {
      if (__DEV__) {
        console.error('Error checking onboarding status:', error);
      }
      setIsOnboardingCompleted(false);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Complete onboarding
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

  // Reset onboarding (for testing or logout)
  const resetOnboarding = async () => {
    try {
      // Clear both onboarding completion and user consent
      await storageService.removeItem(ONBOARDING_KEY);
      await storageService.removeItem('user_consent');
      setIsOnboardingCompleted(false);
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
    completeOnboarding,
    resetOnboarding,
  };

  return (
    <OnboardingContext.Provider value={contextValue}>
      {children}
    </OnboardingContext.Provider>
  );
};

// Custom hook to use onboarding context
export const useOnboarding = (): OnboardingContextType => {
  const context = useContext(OnboardingContext);
  if (context === undefined) {
    throw new Error('useOnboarding must be used within an OnboardingProvider');
  }
  return context;
};
