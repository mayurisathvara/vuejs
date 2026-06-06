import React, { useState, useEffect } from 'react';
import { View, StyleSheet, SafeAreaView, StatusBar, ScrollView, ActivityIndicator, Text, RefreshControl } from 'react-native';
import { useTheme } from '../contexts/ThemeContext';
import AppHeader from '../components/AppHeader';
import DateRangeFilter from '../components/DateRangeFilter';
import DailyCallVolume from '../components/analytics/DailyCallVolume';
import PeakCallHours from '../components/analytics/PeakCallHours';
import MissedCallsChart from '../components/analytics/MissedCallsChart';
import { analyticsAPI } from '../services/api';
import Toast from 'react-native-toast-message';
import { formatDate } from '../utils/date';
import { getErrorMessage, logError } from '../utils/errorHandler';

const AnalyticsScreen: React.FC = () => {
  const { theme } = useTheme();
  const [dateRangeLabel, setDateRangeLabel] = useState('Today');
  const [dailyVolumeData, setDailyVolumeData] = useState<any>(null);
  const [peakHoursData, setPeakHoursData] = useState<any>(null);
  const [missedCallsData, setMissedCallsData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [avgPerDay, setAvgPerDay] = useState(0);
  const [currentDateRange, setCurrentDateRange] = useState<{ start: Date; end: Date }>({
    start: new Date(),
    end: new Date(),
  });

  const fetchDailyCallVolume = async () => {
    try {
      const response = await analyticsAPI.fetchDailyCallVolume();
      
      if (response.status && response.data) {
        // Transform API data to chart format
        const labels = response.data.days.map((day: any) => {
          const date = new Date(day.date);
          return date.toLocaleDateString('en-US', { weekday: 'short' }); // Mon, Tue, Wed...
        });
        
        const values = response.data.days.map((day: any) => day.count);
        
        setDailyVolumeData({ labels, values });
        setAvgPerDay(response.data.avg_per_day);
      }
    } catch (err: any) {
      logError('AnalyticsScreen.fetchDailyCallVolume', err);
      
      const errorResponse = getErrorMessage(err);
      
      if (errorResponse.shouldShowToUser) {
        Toast.show({
          type: 'error',
          text1: errorResponse.title,
          text2: errorResponse.message,
          position: 'bottom',
          visibilityTime: 3000,
        });
      }
    }
  };

  const fetchPeakHours = async (startDate: Date, endDate: Date) => {
    try {
      const startDateStr = formatDate(startDate);
      const endDateStr = formatDate(endDate);
      
      const response = await analyticsAPI.fetchPeakHours(startDateStr, endDateStr);
      
      if (response.status && response.data) {
        setPeakHoursData(response.data);
      }
    } catch (err: any) {
      logError('AnalyticsScreen.fetchPeakHours', err);
      
      const errorResponse = getErrorMessage(err);
      
      if (errorResponse.shouldShowToUser) {
        Toast.show({
          type: 'error',
          text1: errorResponse.title,
          text2: errorResponse.message,
          position: 'bottom',
          visibilityTime: 3000,
        });
      }
    }
  };

  const fetchMissedCalls = async (startDate: Date, endDate: Date) => {
    try {
      const startDateStr = formatDate(startDate);
      const endDateStr = formatDate(endDate);
      
      const response = await analyticsAPI.fetchMissedCalls(startDateStr, endDateStr);
      
      if (response.status && response.data) {
        setMissedCallsData(response.data);
      }
    } catch (err: any) {
      logError('AnalyticsScreen.fetchMissedCalls', err);
      
      const errorResponse = getErrorMessage(err);
      
      if (errorResponse.shouldShowToUser) {
        Toast.show({
          type: 'error',
          text1: errorResponse.title,
          text2: errorResponse.message,
          position: 'bottom',
          visibilityTime: 3000,
        });
      }
    }
  };

  const fetchAllAnalytics = async (startDate: Date, endDate: Date) => {
    setIsLoading(true);
    await Promise.all([
      fetchDailyCallVolume(),
      fetchPeakHours(startDate, endDate),
      fetchMissedCalls(startDate, endDate),
    ]);
    setIsLoading(false);
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await Promise.all([
      fetchDailyCallVolume(),
      fetchPeakHours(currentDateRange.start, currentDateRange.end),
      fetchMissedCalls(currentDateRange.start, currentDateRange.end),
    ]);
    setRefreshing(false);
    Toast.show({
      type: 'success',
      text1: 'Analytics Refreshed',
      text2: 'Data updated successfully',
      position: 'bottom',
      visibilityTime: 2000,
    });
  };

  useEffect(() => {
    const today = new Date();
    fetchAllAnalytics(today, today);
  }, []);

  const handleDateRangeApply = (startDate: Date, endDate: Date, label: string) => {
    setDateRangeLabel(label);
    setCurrentDateRange({ start: startDate, end: endDate });
    // Fetch date-range dependent analytics
    fetchPeakHours(startDate, endDate);
    fetchMissedCalls(startDate, endDate);
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <View style={styles.headerContainer}>
        <AppHeader title="Analytics" />
        
        {/* Date Range Filter */}
        <DateRangeFilter onApply={handleDateRangeApply} selectedLabel={dateRangeLabel} />
      </View>
      
      <ScrollView 
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContainer}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            colors={['#FF7A3D']}
            tintColor="#FF7A3D"
            progressBackgroundColor={theme.colorScheme === 'dark' ? '#2A2A2A' : '#FFFFFF'}
          />
        }
      >
        {isLoading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#FF7A3D" />
            <Text style={[styles.loadingText, { color: theme.colors.textSecondary }]}>
              Loading analytics...
            </Text>
          </View>
        ) : (
          <>
            {/* Missed Calls Chart */}
            {missedCallsData && (
              <MissedCallsChart data={missedCallsData} />
            )}

            {/* Daily Call Volume */}
            {dailyVolumeData && (
              <DailyCallVolume data={dailyVolumeData} avgPerDay={avgPerDay} />
            )}

            {/* Peak Call Hours */}
            {peakHoursData && (
              <PeakCallHours data={peakHoursData} />
            )}
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: { 
    flex: 1 
  },
  headerContainer: {
    paddingHorizontal: 16,
  },
  scrollView: {
    flex: 1,
  },
  scrollContainer: {
    paddingHorizontal: 16,
    paddingBottom: 24,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
  },
  loadingText: {
    marginTop: 16,
    fontSize: 14,
    fontWeight: '500',
  },
});

export default AnalyticsScreen;
