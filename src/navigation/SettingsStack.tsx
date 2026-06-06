import React from 'react';
import { createStackNavigator } from '@react-navigation/stack';
import SettingsScreen from '../screens/SettingsScreen';
import ProfileScreen from '../screens/Settings/ProfileScreen';
import PrivacyScreen from '../screens/Settings/PrivacyScreen';
import HelpSupportScreen from '../screens/Settings/HelpSupportScreen';
import AboutScreen from '../screens/Settings/AboutScreen';
import WebViewScreen from '../screens/WebViewScreen';
import { useTheme } from '../contexts/ThemeContext';

export type SettingsStackParamList = {
  SettingsMain: undefined;
  Profile: undefined;
  Privacy: undefined;
  HelpSupport: undefined;
  About: undefined;
  WebView: { url: string; title: string };
};

const Stack = createStackNavigator<SettingsStackParamList>();

const SettingsStack: React.FC = () => {
  const { theme } = useTheme();

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: theme.colors.background,
          elevation: 0,
          shadowOpacity: 0,
          borderBottomWidth: 0,
        },
        headerTintColor: theme.colors.textPrimary,
        cardStyle: {
          backgroundColor: theme.colors.background,
        },
      }}
    >
      <Stack.Screen 
        name="SettingsMain" 
        component={SettingsScreen}
        options={{ headerShown: false }}
      />
      <Stack.Screen 
        name="Profile" 
        component={ProfileScreen}
        options={{ title: 'Profile' }}
      />
      <Stack.Screen 
        name="Privacy" 
        component={PrivacyScreen}
        options={{ title: 'Privacy' }}
      />
      <Stack.Screen 
        name="HelpSupport" 
        component={HelpSupportScreen}
        options={{ title: 'Help & Support' }}
      />
      <Stack.Screen
        name="About"
        component={AboutScreen}
        options={{ title: 'About' }}
      />
      <Stack.Screen
        name="WebView"
        component={WebViewScreen}
        options={({ route }) => ({ title: route.params.title })}
      />
    </Stack.Navigator>
  );
};

export default SettingsStack;
