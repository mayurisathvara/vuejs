import React, { useState } from 'react';
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

export const navigationRef = React.createRef<NavigationContainerRef<RootStackParamList>>();

export function navigate<T extends keyof RootStackParamList>(
  name: T,
  params?: RootStackParamList[T]
) {
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
  const { isOnboardingCompleted, isLoading: isOnboardingLoading, hasConsent } = useOnboarding();
  const [showSplash, setShowSplash] = useState(true);
  const { theme } = useTheme();

  const handleSplashFinish = () => {
    setShowSplash(false);
  };

  if (showSplash) {
    return <SplashScreen onAnimationFinish={handleSplashFinish} />;
  }

  if (isLoading || isOnboardingLoading) {
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
          <Stack.Screen
            name="Login"
            component={LoginScreen}
            options={{ gestureEnabled: false }}
          />
        ) : !hasConsent ? (
          <Stack.Screen
            name="Consent"
            component={ConsentScreen}
            options={{ gestureEnabled: false }}
          />
        ) : !isOnboardingCompleted ? (
          <>
            <Stack.Screen
              name="ContactsPermission"
              component={ContactsPermission}
              options={{ gestureEnabled: false }}
            />
            <Stack.Screen
              name="CallLogPermission"
              component={CallLogPermission}
              options={{ gestureEnabled: false }}
            />
            <Stack.Screen
              name="PhoneStatePermission"
              component={PhoneStatePermission}
              options={{ gestureEnabled: false }}
            />
            <Stack.Screen
              name="NotificationPermission"
              component={NotificationPermission}
              options={{ gestureEnabled: false }}
            />
          </>
        ) : (
          <Stack.Screen
            name="Home"
            component={MainTabs}
            options={{ gestureEnabled: false }}
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
