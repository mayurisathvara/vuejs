import React, { useState, useEffect } from 'react';
import {
  SafeAreaView,
  Text,
  StyleSheet,
  View,
  StatusBar,
  ScrollView,
  Dimensions,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../contexts/ThemeContext';
import { useCallLogSync } from '../contexts/CallLogContext';
import AppHeader from '../components/AppHeader';
import DateRangeFilter from '../components/DateRangeFilter';
import Toast from 'react-native-toast-message';
import { dashboardAPI } from '../services/api';
import { formatDate } from '../utils/date';
import { appEvents, APP_EVENTS } from '../utils/eventEmitter';
import { getErrorMessage, logError } from '../utils/errorHandler';

const { width } = Dimensions.get('window');

interface DashboardData {
  summary: {
    total_calls: { value: number; change: string };
    answer_rate: { value: string; change: string };
    avg_duration: { value: string; change: string };
  };
  outbound: {
    answered: number;
    no_answer: number;
    total: number;
    change: string;
  };
  inbound: {
    answered: number;
    missed: number;
    total: number;
    change: string;
  };
  alerts: {
    missed_calls: { value: number };
  };
}

const HomeScreen: React.FC = () => {
  const { theme } = useTheme();
  const { lastSyncTime, isSyncing, manualSync, error } = useCallLogSync();
  const [dateRangeLabel, setDateRangeLabel] = useState('Today');
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [currentDateRange, setCurrentDateRange] = useState<{ start: Date; end: Date }>({
    start: new Date(),
    end: new Date()
  });

  const handleMissedCallsClick = async () => {
    try {
      appEvents.emit(APP_EVENTS.NAVIGATE_TO_CALL_LOGS, { filter: 'Missed' });
    } catch (error) {
      if (__DEV__) console.error('Error emitting navigate event:', error);
    }
  };

  const fetchDashboard = async (startDate: Date, endDate: Date, isPullRefresh = false) => {
    if (isPullRefresh) {
      setRefreshing(true);
    } else {
      setIsLoading(true);
    }
    
    try {
      const startDateStr = formatDate(startDate);
      const endDateStr = formatDate(endDate);
      
      const response = await dashboardAPI.fetchDashboard(startDateStr, endDateStr);
      
      if (response.status && response.data) {
        setDashboardData(response.data);
        if (isPullRefresh) {
          Toast.show({
            type: 'success',
            text1: 'Dashboard Refreshed',
            text2: 'Data updated successfully',
            position: 'bottom',
            visibilityTime: 2000,
          });
        }
      }
    } catch (err: any) {
      logError('HomeScreen.fetchDashboard', err);
      
      const errorResponse = getErrorMessage(err);
      
      // Only show toast if error should be shown to user
      if (errorResponse.shouldShowToUser) {
        Toast.show({
          type: 'error',
          text1: errorResponse.title,
          text2: errorResponse.message,
          position: 'bottom',
          visibilityTime: 3000,
        });
      }
    } finally {
      if (isPullRefresh) {
        setRefreshing(false);
      } else {
        setIsLoading(false);
      }
    }
  };

  const handleDateRangeApply = (startDate: Date, endDate: Date, label: string) => {
    setDateRangeLabel(label);
    setCurrentDateRange({ start: startDate, end: endDate });
    fetchDashboard(startDate, endDate);
  };

  const handleRefresh = () => {
    fetchDashboard(currentDateRange.start, currentDateRange.end, true);
  };

  // Initial load - fetch today's data
  useEffect(() => {
    const today = new Date();
    fetchDashboard(today, today);
  }, []);

  const handleSync = async () => {
    try {
      await manualSync();
      Toast.show({
        type: 'success',
        text1: 'Sync Complete',
        text2: 'Call logs synced successfully',
        position: 'bottom',
      });
    } catch (err: any) {
      Toast.show({
        type: 'error',
        text1: 'Sync Failed',
        text2: err?.message || 'Failed to sync call logs',
        position: 'bottom',
      });
    }
  };

  const formatLastSync = () => {
    if (!lastSyncTime) return 'Never synced';
    
    const now = Date.now();
    const diff = now - lastSyncTime;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    return `${days} day${days > 1 ? 's' : ''} ago`;
  };

  const getChangeColor = (change: string): string => {
    if (change.startsWith('+')) return '#4CAF50';
    if (change.startsWith('-')) return '#F44336';
    return '#FF9800';
  };

  const getChangeIcon = (change: string): string => {
    if (change.startsWith('+')) return '↑';
    if (change.startsWith('-')) return '↓';
    return '';
  };
  const isDark = theme.colorScheme === 'dark';

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={isDark ? 'light-content' : 'dark-content'}
        backgroundColor={theme.colors.background} 
      />
      <View style={styles.headerContainer}>
        <AppHeader title="Dashboard" />
        
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
            progressBackgroundColor={isDark ? '#2A2A2A' : '#FFFFFF'}
          />
        }
      >
        {isLoading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#FF7A3D" />
            <Text style={[styles.loadingText, { color: theme.colors.textSecondary }]}>
              Loading dashboard...
            </Text>
          </View>
        ) : (
          <>
        {/* Hero Card - Total Calls */}
        <LinearGradient
          colors={['#FF7A3D', '#FFA45C']}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.heroCard}
        >
          <View style={styles.heroTop}>
            <Text style={styles.heroLabel}>Total Calls</Text>
            {dashboardData && (
              <View style={styles.heroBadge}>
                <Text style={styles.heroBadgeText}>
                  {dashboardData.summary.total_calls.change}
                </Text>
              </View>
            )}
          </View>
          <Text style={styles.heroValue}>
            {dashboardData?.summary.total_calls.value || 0}
          </Text>
          <Text style={styles.heroSubtext}>All call activity</Text>
          
          {/* Decorative curve */}
          <View style={styles.curveContainer}>
            <View style={styles.curvePoint} />
          </View>
        </LinearGradient>

        {/* Stats Grid - 2 Column */}
        <View style={styles.statsGrid}>
          <View style={[styles.statCard, { backgroundColor: theme.colors.surface }]}>
            <View style={styles.statHeader}>
              <View style={[styles.statIconBox, { backgroundColor: '#E8F5E9' }]}>
                <MaterialCommunityIcons name="phone-check" size={20} color="#4CAF50" />
              </View>
              {dashboardData && (
                <Text style={[styles.statBadge, { 
                  color: getChangeColor(dashboardData.summary.answer_rate.change) 
                }]}>
                  {getChangeIcon(dashboardData.summary.answer_rate.change)} {dashboardData.summary.answer_rate.change}
                </Text>
              )}
            </View>
            <Text style={[styles.statValue, { color: theme.colors.textPrimary }]}>
              {dashboardData?.summary.answer_rate.value || '0%'}
            </Text>
            <Text style={[styles.statLabel, { color: theme.colors.textSecondary }]}>Answer Rate</Text>
          </View>

          <View style={[styles.statCard, { backgroundColor: theme.colors.surface }]}>
            <View style={styles.statHeader}>
              <View style={[styles.statIconBox, { backgroundColor: '#FFF3E0' }]}>
                <MaterialCommunityIcons name="clock-outline" size={20} color="#FF9800" />
              </View>
              {dashboardData && (
                <Text style={[styles.statBadge, { 
                  color: getChangeColor(dashboardData.summary.avg_duration.change) 
                }]}>
                  {getChangeIcon(dashboardData.summary.avg_duration.change)} {dashboardData.summary.avg_duration.change}
                </Text>
              )}
            </View>
            <Text style={[styles.statValue, { color: theme.colors.textPrimary }]}>
              {dashboardData?.summary.avg_duration.value || '0m 0s'}
            </Text>
            <Text style={[styles.statLabel, { color: theme.colors.textSecondary }]}>Avg Duration</Text>
          </View>
        </View>

        {/* Call Type Cards - 2 Column Grid */}
        <View style={styles.statsGrid}>
          <View style={[styles.statCard, { backgroundColor: theme.colors.surface }]}>
            <View style={styles.statHeader}>
              <View style={[styles.statIconBox, { backgroundColor: '#FFE5E5' }]}>
                <MaterialCommunityIcons name="phone-outgoing" size={20} color="#FF6B6B" />
              </View>
              {dashboardData && (
                <Text style={[styles.statBadge, { 
                  color: getChangeColor(dashboardData.outbound.change) 
                }]}>
                  {getChangeIcon(dashboardData.outbound.change)} {dashboardData.outbound.change}
                </Text>
              )}
            </View>
            <Text style={[styles.statValue, { color: theme.colors.textPrimary }]}>
              {dashboardData?.outbound.total || 0}
            </Text>
            <Text style={[styles.statLabel, { color: theme.colors.textSecondary }]}>Outbound</Text>
          </View>

          <View style={[styles.statCard, { backgroundColor: theme.colors.surface }]}>
            <View style={styles.statHeader}>
              <View style={[styles.statIconBox, { backgroundColor: '#E3F2FD' }]}>
                <MaterialCommunityIcons name="phone-incoming" size={20} color="#2196F3" />
              </View>
              {dashboardData && (
                <Text style={[styles.statBadge, { 
                  color: getChangeColor(dashboardData.inbound.change) 
                }]}>
                  {getChangeIcon(dashboardData.inbound.change)} {dashboardData.inbound.change}
                </Text>
              )}
            </View>
            <Text style={[styles.statValue, { color: theme.colors.textPrimary }]}>
              {dashboardData?.inbound.total || 0}
            </Text>
            <Text style={[styles.statLabel, { color: theme.colors.textSecondary }]}>Inbound</Text>
          </View>
        </View>

        {/* Missed Calls Alert */}
        <TouchableOpacity 
          style={[styles.alertCard, { backgroundColor: isDark ? '#2A2A2A' : '#FFF9F5' }]} 
          activeOpacity={0.7}
          onPress={handleMissedCallsClick}
        >
          <View style={styles.alertLeft}>
            <View style={styles.alertIconBox}>
              <MaterialCommunityIcons name="alert-circle" size={18} color="#FF6B6B" />
            </View>
            <View style={styles.alertTextContainer}>
              <Text style={[styles.alertTitle, { color: theme.colors.textPrimary }]}>
                {dashboardData?.alerts.missed_calls.value || 0} Missed Calls
              </Text>
              <Text style={[styles.alertSubtitle, { color: theme.colors.textSecondary }]}>Tap to view and return calls</Text>
            </View>
          </View>
          <MaterialCommunityIcons name="chevron-right" size={20} color={theme.colors.textSecondary} />
        </TouchableOpacity>

        {/* Call Breakdown */}
        <View style={styles.detailSection}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Call Breakdown</Text>
          
          {/* Outbound Card */}
          <View style={[styles.breakdownCard, { backgroundColor: theme.colors.surface }]}>
            <View style={styles.cardHeader}>
              <View style={[styles.cardIcon, { backgroundColor: isDark ? '#3D2020' : '#FFEBEE' }]}>
                <MaterialCommunityIcons name="phone-outgoing" size={20} color="#FF6B6B" />
              </View>
              <Text style={[styles.cardTitle, { color: theme.colors.textPrimary }]}>Outbound Calls</Text>
            </View>

            <View style={styles.metricsList}>
              <View style={styles.metricRow}>
                <View style={styles.metricLeft}>
                  <MaterialCommunityIcons name="phone-check" size={18} color="#2196F3" />
                  <Text style={[styles.metricLabel, { color: theme.colors.textSecondary }]}>Answered</Text>
                </View>
                <Text style={[styles.metricValue, { color: theme.colors.textPrimary }]}>
                  {dashboardData?.outbound.answered || 0}
                </Text>
              </View>

              <View style={styles.metricRow}>
                <View style={styles.metricLeft}>
                  <MaterialCommunityIcons name="phone-cancel" size={18} color="#FF9800" />
                  <Text style={[styles.metricLabel, { color: theme.colors.textSecondary }]}>No Answer</Text>
                </View>
                <Text style={[styles.metricValue, { color: theme.colors.textPrimary }]}>
                  {dashboardData?.outbound.no_answer || 0}
                </Text>
              </View>
            </View>

            <View style={[styles.totalRow, { backgroundColor: isDark ? '#2A2A2A' : '#F5F5F5' }]}>
              <Text style={[styles.totalLabel, { color: theme.colors.textSecondary }]}>Total Outbound</Text>
              <Text style={[styles.totalValue, { color: theme.colors.textPrimary }]}>
                {dashboardData?.outbound.total || 0}
              </Text>
            </View>
          </View>

          {/* Inbound Card */}
          <View style={[styles.breakdownCard, { backgroundColor: theme.colors.surface }]}>
            <View style={styles.cardHeader}>
              <View style={[styles.cardIcon, { backgroundColor: isDark ? '#1A2530' : '#E3F2FD' }]}>
                <MaterialCommunityIcons name="phone-incoming" size={20} color="#2196F3" />
              </View>
              <Text style={[styles.cardTitle, { color: theme.colors.textPrimary }]}>Inbound Calls</Text>
            </View>

            <View style={styles.metricsList}>
              <View style={styles.metricRow}>
                <View style={styles.metricLeft}>
                  <MaterialCommunityIcons name="phone-check" size={18} color="#4CAF50" />
                  <Text style={[styles.metricLabel, { color: theme.colors.textSecondary }]}>Answered</Text>
                </View>
                <Text style={[styles.metricValue, { color: theme.colors.textPrimary }]}>
                  {dashboardData?.inbound.answered || 0}
                </Text>
              </View>

              <View style={styles.metricRow}>
                <View style={styles.metricLeft}>
                  <MaterialCommunityIcons name="phone-missed" size={18} color="#F44336" />
                  <Text style={[styles.metricLabel, { color: theme.colors.textSecondary }]}>Missed</Text>
                </View>
                <Text style={[styles.metricValue, { color: theme.colors.textPrimary }]}>
                  {dashboardData?.inbound.missed || 0}
                </Text>
              </View>
            </View>

            <View style={[styles.totalRow, { backgroundColor: isDark ? '#2A2A2A' : '#F5F5F5' }]}>
              <Text style={[styles.totalLabel, { color: theme.colors.textSecondary }]}>Total Inbound</Text>
              <Text style={[styles.totalValue, { color: theme.colors.textPrimary }]}>
                {dashboardData?.inbound.total || 0}
              </Text>
            </View>
          </View>
        </View>

        {/* Sync Status Card */}
        <View style={[styles.syncCard, { backgroundColor: theme.colors.surface }]}>
          <View style={styles.syncLeft}>
            <View style={styles.syncIconBox}>
              <MaterialCommunityIcons name="cloud-check" size={18} color="#4CAF50" />
            </View>
            <View style={styles.syncTextContainer}>
              <Text style={[styles.syncTitle, { color: theme.colors.textPrimary }]}>All synced</Text>
              <Text style={[styles.syncSubtitle, { color: theme.colors.textSecondary }]}>Last sync: {formatLastSync()}</Text>
            </View>
          </View>
          <TouchableOpacity 
            style={styles.syncActionIcon}
            onPress={handleSync}
            disabled={isSyncing}
            activeOpacity={0.7}
          >
            {isSyncing ? (
              <ActivityIndicator size="small" color="#FF9800" />
            ) : (
              <MaterialCommunityIcons name="sync" size={20} color="#FF9800" />
            )}
          </TouchableOpacity>
        </View>
        </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
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
  
  // Hero Card
  heroCard: {
    borderRadius: 24,
    padding: 20,
    marginBottom: 16,
    minHeight: 160,
    elevation: 4,
    shadowColor: '#FF7A3D',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    overflow: 'hidden',
  },
  heroTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  heroLabel: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.9)',
    fontWeight: '600',
  },
  heroBadge: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  heroBadgeText: {
    color: '#FF7A3D',
    fontSize: 12,
    fontWeight: '700',
  },
  heroValue: {
    fontSize: 48,
    fontWeight: '800',
    color: '#fff',
    marginBottom: 4,
  },
  heroSubtext: {
    fontSize: 13,
    color: 'rgba(255,255,255,0.85)',
    fontWeight: '500',
  },
  curveContainer: {
    position: 'absolute',
    right: 20,
    bottom: 20,
    width: 100,
    height: 60,
  },
  curvePoint: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(255,255,255,0.5)',
    position: 'absolute',
    right: 0,
    top: 20,
  },

  // Stats Grid
  statsGrid: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 16,
  },
  statCard: {
    flex: 1,
    borderRadius: 16,
    padding: 16,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
  },
  statHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  statIconBox: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  statBadge: {
    fontSize: 12,
    fontWeight: '700',
    color: '#FF6B6B',
  },
  statValue: {
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 4,
  },
  statLabel: {
    fontSize: 13,
    fontWeight: '600',
    marginBottom: 8,
  },
  statUpdate: {
    fontSize: 11,
    color: '#999',
  },

  // Detail Section
  detailSection: {
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 16,
  },
  
  // Breakdown Cards
  breakdownCard: {
    borderRadius: 16,
    padding: 20,
    marginBottom: 16,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 20,
  },
  cardIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
  },
  
  // Metrics List
  metricsList: {
    gap: 16,
    marginBottom: 16,
  },
  metricRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  metricLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  metricLabel: {
    fontSize: 14,
    fontWeight: '500',
  },
  metricValue: {
    fontSize: 16,
    fontWeight: '700',
  },
  
  // Total Row
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 10,
    marginTop: 4,
  },
  totalLabel: {
    fontSize: 14,
    fontWeight: '600',
  },
  totalValue: {
    fontSize: 18,
    fontWeight: '800',
  },

  // Alert Card
  alertCard: {
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 14,
    marginBottom: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderLeftWidth: 4,
    borderLeftColor: '#FF9800',
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
  },
  alertLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    gap: 10,
  },
  alertIconBox: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#FFE5E5',
    alignItems: 'center',
    justifyContent: 'center',
  },
  alertTextContainer: {
    flex: 1,
  },
  alertTitle: {
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 2,
  },
  alertSubtitle: {
    fontSize: 11,
    fontWeight: '500',
  },

  // Sync Card
  syncCard: {
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 14,
    marginBottom: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderLeftWidth: 4,
    borderLeftColor: '#4CAF50',
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
  },
  syncLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    gap: 10,
  },
  syncIconBox: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#E8F5E9',
    alignItems: 'center',
    justifyContent: 'center',
  },
  syncTextContainer: {
    flex: 1,
  },
  syncTitle: {
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 2,
  },
  syncSubtitle: {
    fontSize: 11,
    fontWeight: '500',
  },
  syncActionIcon: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#FFF3E0',
    alignItems: 'center',
    justifyContent: 'center',
  },
});

export default HomeScreen;