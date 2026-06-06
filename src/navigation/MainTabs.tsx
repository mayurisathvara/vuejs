import React, { useState, useEffect } from 'react';
import { StyleSheet, Platform, BackHandler, Alert } from 'react-native';
import { BottomNavigation } from 'react-native-paper';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import HomeScreen from '../screens/HomeScreen';
import CallLogsScreen from '../screens/CallLogsScreen';
import AnalyticsScreen from '../screens/AnalyticsScreen';
import SettingsStack from './SettingsStack';
import { useTheme } from '../contexts/ThemeContext';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { TAB_INDICES } from '../constants';
import { appEvents, APP_EVENTS } from '../utils/eventEmitter';

const DashboardRoute = () => <HomeScreen />;
const CallLogsRoute = () => <CallLogsScreen />;
const AnalyticsRoute = () => <AnalyticsScreen />;
const SettingsRoute = () => <SettingsStack />;

const MainTabs: React.FC = () => {
  const { theme, isDark } = useTheme();
  const [index, setIndex] = useState<number>(TAB_INDICES.DASHBOARD);
  const [tabHistory, setTabHistory] = useState<number[]>([TAB_INDICES.DASHBOARD]);

  // Listen for navigation events from other screens
  useEffect(() => {
    const handleNavigateToCallLogs = () => {
      setIndex(TAB_INDICES.CALL_LOGS);
      setTabHistory(prev => [...prev, TAB_INDICES.CALL_LOGS]);
    };

    // Subscribe to navigation events
    const unsubscribe = appEvents.on(APP_EVENTS.NAVIGATE_TO_CALL_LOGS, handleNavigateToCallLogs);

    return () => {
      unsubscribe();
    };
  }, []);

  // Handle tab changes and update history
  const handleIndexChange = (newIndex: number) => {
    setIndex(newIndex);
    setTabHistory(prev => [...prev, newIndex]);
  };

  // Handle Android back button
  useEffect(() => {
    const backHandler = BackHandler.addEventListener('hardwareBackPress', () => {
      if (tabHistory.length > 1) {
        // Navigate to previous tab in history
        const newHistory = [...tabHistory];
        newHistory.pop(); // Remove current tab
        const previousIndex = newHistory[newHistory.length - 1];
        
        setTabHistory(newHistory);
        setIndex(previousIndex);
        return true; // Prevent default back behavior
      } else {
        // No history - show exit confirmation
        Alert.alert(
          'Exit App',
          'Are you sure you want to exit?',
          [
            {
              text: 'Cancel',
              style: 'cancel',
            },
            {
              text: 'Exit',
              style: 'destructive',
              onPress: () => BackHandler.exitApp(),
            },
          ]
        );
        return true; // Prevent default back behavior
      }
    });

    return () => backHandler.remove();
  }, [tabHistory]);

  const [routes] = useState<Array<any>>([
    { key: 'dashboard', title: 'Dashboard', focusedIcon: 'view-dashboard', icon: 'view-dashboard-outline' },
    { key: 'calllogs', title: 'Call Logs', focusedIcon: 'phone', icon: 'phone-outline' },
    { key: 'analytics', title: 'Analytics', focusedIcon: 'chart-line', icon: 'chart-line' },
    { key: 'settings', title: 'Settings', focusedIcon: 'cog', icon: 'cog-outline' },
  ]);

  const renderScene = BottomNavigation.SceneMap({
    dashboard: DashboardRoute,
    calllogs: CallLogsRoute,
    analytics: AnalyticsRoute,
    settings: SettingsRoute,
  });

  const ACTIVE_COLOR = '#FF5722';
  const INACTIVE_COLOR = '#9CA3AF';

  const renderIcon = ({ route, focused }: { route: any; focused: boolean }) => {
    const name = focused ? (route.focusedIcon || route.icon) : route.icon;
    return (
      <MaterialCommunityIcons
        name={name}
        size={24}
        color={focused ? ACTIVE_COLOR : INACTIVE_COLOR}
        style={{ marginBottom: -2 }}
      />
    );
  };

  return (
    <BottomNavigation
      navigationState={{ index, routes }}
      onIndexChange={handleIndexChange}
      renderScene={renderScene}
      shifting={false}
      labeled
      barStyle={[
        styles.bar,
        {
          backgroundColor: isDark ? theme.colors.surface : '#FAFAFA',
          borderTopWidth: 1,
          borderTopColor: isDark ? 'rgba(255,255,255,0.08)' : '#E5E7EB',
          elevation: 0,
          shadowColor: 'transparent',
          paddingTop: 10,
          paddingBottom: Platform.OS === 'ios' ? 30 : 10,
          height: Platform.OS === 'ios' ? 88 : 68,
          justifyContent: 'center',
          alignItems: 'center',
        },
      ]}
      activeColor={ACTIVE_COLOR}
      inactiveColor={INACTIVE_COLOR}
      renderIcon={renderIcon}
      compact={false}
      activeIndicatorStyle={styles.activeIndicator}
      sceneAnimationEnabled={false}
    />
  );
};

const styles = StyleSheet.create({
  bar: {
    marginHorizontal: 0,
    marginBottom: 0,
    borderRadius: 0,
  },
  label: {
    fontSize: 10.5,
    fontWeight: '500',
    marginTop: 4,
    letterSpacing: 0,
    marginBottom: 2,
  },
  activeIndicator: {
    backgroundColor: 'transparent',
  },
});

export default MainTabs;
