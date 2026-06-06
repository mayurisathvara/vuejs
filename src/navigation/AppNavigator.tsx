import React, { useEffect, useState } from 'react';
import { NavigationContainer, NavigationContainerRef } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { useAuth } from '../contexts/AuthContext';
import { useOnboarding } from '../contexts/OnboardingContext';
import { useTheme } from '../contexts/ThemeContext';
import SplashScreen from '../screens/SplashScreen';
import LoginScreen from '../screens/LoginScreen';
import MainTabs from './MainTabs';
import ContactsPermission from '../screens/Onboarding/ContactsPermission';
import CallLogPermission from '../screens/Onboarding/CallLogPermission';
import NotificationPermission from '../screens/Onboarding/NotificationPermission';
import ConsentScreen from '../screens/Onboarding/ConsentScreen';
import PhoneStatePermission from '../screens/Onboarding/PhoneStatePermission';
import { storageService } from '../services/storage';
import { UI_TIMINGS } from '../constants';

export type RootStackParamList = {
  Splash: undefined;
  Login: undefined;
  Home: undefined;
  Consent: undefined;
  ContactsPermission: undefined;
  CallLogPermission: undefined;
  PhoneStatePermission: undefined;
  NotificationPermission: undefined;
};

const Stack = createStackNavigator<RootStackParamList>();

// Create navigation ref for potential use outside React components
export const navigationRef = React.createRef<NavigationContainerRef<RootStackParamList>>();

export function navigate(name: keyof RootStackParamList, params?: any) {
  if (navigationRef.current?.isReady()) {
    navigationRef.current.navigate(name as never, params as never);
  }
}

const LoadingScreen: React.FC = () => {
  const { theme } = useTheme();
  return (
    <View style={[styles.loadingContainer, { backgroundColor: theme.colors.background }]}>
      <ActivityIndicator size="large" color={theme.colors.primary} />
    </View>
  );
};

const AppNavigator: React.FC = () => {
  const { isAuthenticated, isLoading } = useAuth();
  const { isOnboardingCompleted, isLoading: isOnboardingLoading } = useOnboarding();
  const [hasConsent, setHasConsent] = useState<boolean | null>(null);
  const [showSplash, setShowSplash] = useState(true);
  const { theme } = useTheme();
  const hasNavigatedRef = React.useRef(false); // FIX: Track if initial navigation completed

  // Check consent status on mount and when auth changes
  useEffect(() => {
    const checkConsent = async () => {
      const consented = await storageService.getUserConsent();
      setHasConsent(consented);
    };
    checkConsent();
  }, [isAuthenticated]);

  // Re-check consent when returning from consent screen
  useEffect(() => {
    if (isAuthenticated && hasConsent === false) {
      const recheckConsent = setInterval(async () => {
        const consented = await storageService.getUserConsent();
        if (consented) {
          setHasConsent(true);
        }
      }, UI_TIMINGS.CONSENT_CHECK_INTERVAL);
      return () => clearInterval(recheckConsent);
    }
  }, [isAuthenticated, hasConsent]);

  const handleSplashFinish = () => {
    if (__DEV__) {
      console.log('[AppNavigator] Splash finished');
    }
    setShowSplash(false);
  };

  // Debug logging
  useEffect(() => {
    if (__DEV__) {
      console.log('[AppNavigator] State:', {
        showSplash,
        isLoading,
        isOnboardingLoading,
        isAuthenticated,
        hasConsent,
        isOnboardingCompleted,
      });
    }
  }, [showSplash, isLoading, isOnboardingLoading, isAuthenticated, hasConsent, isOnboardingCompleted]);

  // CRITICAL FIX: Programmatically reset navigation on auth state changes
  // This ensures proper navigation after login/logout without requiring app restart
  // FIX: Only reset navigation when auth state actually changes, not on every render
  useEffect(() => {
    // Skip during initial load or when showing splash
    if (showSplash || isLoading || isOnboardingLoading || hasConsent === null) {
      return;
    }

    // Wait for navigation to be ready
    if (!navigationRef.current?.isReady()) {
      return;
    }

    // Determine target route based on current state
    let targetRoute: keyof RootStackParamList;
    
    if (!isAuthenticated) {
      targetRoute = 'Login';
    } else if (!hasConsent) {
      targetRoute = 'Consent';
    } else if (!isOnboardingCompleted) {
      targetRoute = 'ContactsPermission';
    } else {
      targetRoute = 'Home';
    }

    // FIX: Get current route to avoid unnecessary resets
    const currentRoute = navigationRef.current.getCurrentRoute()?.name;
    
    // Only reset if we're on a different route or this is the first navigation after auth change
    if (currentRoute !== targetRoute || !hasNavigatedRef.current) {
      if (__DEV__) {
        console.log(`[AppNavigator] Resetting navigation from ${currentRoute} to: ${targetRoute}`);
      }
      
      navigationRef.current.reset({
        index: 0,
        routes: [{ name: targetRoute }],
      });
      
      hasNavigatedRef.current = true;
    }
  }, [isAuthenticated, hasConsent, isOnboardingCompleted, showSplash, isLoading, isOnboardingLoading]);

  if (showSplash) {
    return <SplashScreen onAnimationFinish={handleSplashFinish} />;
  }

  if (isLoading || isOnboardingLoading || hasConsent === null) {
    if (__DEV__) {
      console.log('[AppNavigator] Showing loading screen');
    }
    return <LoadingScreen />;
  }

  return (
    <NavigationContainer ref={navigationRef}>
      <Stack.Navigator
        screenOptions={{
          headerShown: false,
          cardStyle: { backgroundColor: theme.colors.background },
        }}
      >
        {!isAuthenticated ? (
          // Not authenticated - show login
          <Stack.Screen 
            name="Login" 
            component={LoginScreen}
            options={{
              gestureEnabled: false,
            }}
          />
        ) : !hasConsent ? (
          // Authenticated but no consent - show consent screen
          <Stack.Screen 
            name="Consent" 
            component={ConsentScreen}
            options={{
              gestureEnabled: false,
            }}
          />
        ) : !isOnboardingCompleted ? (
          // Authenticated with consent but onboarding not complete - show permissions
          <>
            <Stack.Screen 
              name="ContactsPermission" 
              component={ContactsPermission}
              options={{
                gestureEnabled: false,
              }}
            />
            <Stack.Screen 
              name="CallLogPermission" 
              component={CallLogPermission}
              options={{
                gestureEnabled: false,
              }}
            />
            <Stack.Screen 
              name="PhoneStatePermission" 
              component={PhoneStatePermission}
              options={{
                gestureEnabled: false,
              }}
            />
            <Stack.Screen 
              name="NotificationPermission" 
              component={NotificationPermission}
              options={{
                gestureEnabled: false,
              }}
            />
          </>
        ) : (
          // Fully authenticated and onboarded - show main app
          <Stack.Screen 
            name="Home" 
            component={MainTabs}
            options={{
              gestureEnabled: false,
            }}
          />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
};

const styles = StyleSheet.create({
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default AppNavigator;
