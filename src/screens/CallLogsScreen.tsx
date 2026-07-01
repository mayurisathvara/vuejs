import React, { useState, useEffect } from 'react';
import { View, StyleSheet, FlatList, SafeAreaView, StatusBar, Text, TouchableOpacity, ActivityIndicator, RefreshControl, Linking, Platform } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../contexts/ThemeContext';
import AppHeader from '../components/AppHeader';
import DateRangeFilter from '../components/DateRangeFilter';
import FilterBar from '../components/FilterBar';
import Toast from 'react-native-toast-message';
import { callLogsAPI } from '../services/api';
import { formatDate } from '../utils/date';
import { getErrorMessage, logError } from '../utils/errorHandler';
import { appEvents, APP_EVENTS } from '../utils/eventEmitter';
import { CallLogListItem } from '../types';

const CallLogsScreen: React.FC = () => {
  const { theme } = useTheme();
  const [dateRangeLabel, setDateRangeLabel] = useState('Today');
  const [selectedFilter, setSelectedFilter] = useState('All');
  const [callLogs, setCallLogs] = useState<CallLogListItem[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMorePages, setHasMorePages] = useState(true);
  const [currentDateRange, setCurrentDateRange] = useState<{ start: Date; end: Date }>({
    start: new Date(),
    end: new Date(),
  });

  const filters = ['All', 'Inbound', 'Outbound', 'Missed'];

  const getFilterType = (filter: string): string => {
    if (filter === 'All') return 'all';
    return filter.toLowerCase();
  };

  const fetchCallLogs = async (
    startDate: Date,
    endDate: Date,
    filter: string,
    page: number = 1,
    isPullRefresh = false,
    isLoadMore = false
  ) => {
    if (isPullRefresh) {
      setRefreshing(true);
    } else if (isLoadMore) {
      setIsLoadingMore(true);
    } else {
      setIsLoading(true);
    }

    try {
      const startDateStr = formatDate(startDate);
      const endDateStr = formatDate(endDate);
      const filterType = getFilterType(filter);

      const response = await callLogsAPI.fetchCallLogs(startDateStr, endDateStr, filterType, page);

      if (response.status && response.data) {
        const newLogs = response.data.data;
        
        if (page === 1) {
          setCallLogs(newLogs);
        } else {
          setCallLogs(prev => [...prev, ...newLogs]);
        }

        setHasMorePages(response.data.current_page < response.data.last_page);
        setCurrentPage(response.data.current_page);

        if (isPullRefresh) {
          Toast.show({
            type: 'success',
            text1: 'Refreshed',
            text2: 'Call logs updated',
            position: 'bottom',
            visibilityTime: 2000,
          });
        }
      }
    } catch (err: unknown) {
      logError('CallLogsScreen.fetchCallLogs', err);
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
    } finally {
      setIsLoading(false);
      setRefreshing(false);
      setIsLoadingMore(false);
    }
  };

  const handleDateRangeApply = (startDate: Date, endDate: Date, label: string) => {
    setDateRangeLabel(label);
    setCurrentDateRange({ start: startDate, end: endDate });
    setCurrentPage(1);
    fetchCallLogs(startDate, endDate, selectedFilter, 1);
  };

  const handleFilterChange = (filter: string) => {
    setSelectedFilter(filter);
    setCurrentPage(1);
    fetchCallLogs(currentDateRange.start, currentDateRange.end, filter, 1);
  };

  const handleRefresh = () => {
    setCurrentPage(1);
    fetchCallLogs(currentDateRange.start, currentDateRange.end, selectedFilter, 1, true);
  };

  const handleLoadMore = () => {
    if (!isLoadingMore && hasMorePages) {
      const nextPage = currentPage + 1;
      fetchCallLogs(currentDateRange.start, currentDateRange.end, selectedFilter, nextPage, false, true);
    }
  };

  // Initial load
  useEffect(() => {
    const today = new Date();
    fetchCallLogs(today, today, 'All', 1);
  }, []);

  // Navigate-to-call-logs event (e.g. from HomeScreen missed-calls tap)
  useEffect(() => {
    const unsubscribe = appEvents.on(APP_EVENTS.NAVIGATE_TO_CALL_LOGS, ({ filter }: { filter?: string } = {}) => {
      if (filter) {
        const today = new Date();
        setSelectedFilter(filter);
        setCurrentDateRange({ start: today, end: today });
        setCurrentPage(1);
        fetchCallLogs(today, today, filter, 1);
      }
    });
    return () => unsubscribe();
  }, []);

  const getCallIcon = (callType: string, callStatus: string) => {
    if (callType === 'inbound') {
      if (callStatus === 'Missed') {
        return { name: 'phone-missed', color: '#F44336' };
      }
      return { name: 'phone-incoming', color: '#4CAF50' };
    }
    if (callType === 'outbound') {
      if (callStatus === 'No Answer') {
        return { name: 'phone-cancel', color: '#FF9800' };
      }
      return { name: 'phone-outgoing', color: '#2196F3' };
    }
    return { name: 'phone', color: '#6B7280' };
  };

  const getContactInitial = (name: string | null, _number: string) => {
    if (name && name.trim()) {
      return name.charAt(0).toUpperCase();
    }
    return '#';
  };

  const getAvatarColor = (name: string | null, number: string) => {
    const colors = ['#FFE5E5', '#E3F2FD', '#E8F5E9', '#FFF3E0', '#F3E5F5', '#E1F5FE'];
    const str = name && name.trim() ? name : number;
    const index = str.charCodeAt(0) % colors.length;
    return colors[index];
  };

  const getAvatarTextColor = (name: string | null, number: string) => {
    const colors = ['#FF6B6B', '#2196F3', '#4CAF50', '#FF9800', '#9C27B0', '#00BCD4'];
    const str = name && name.trim() ? name : number;
    const index = str.charCodeAt(0) % colors.length;
    return colors[index];
  };

  const formatCallTime = (dateTimeStr: string | null | undefined): string => {
    // Handle null or undefined
    if (!dateTimeStr) {
      return 'N/A';
    }

    try {
      // Parse the date_time string properly (format: 2025-11-26 00:43:08)
      const parts = dateTimeStr.split(' ');
      if (parts.length !== 2) {
        return dateTimeStr; // Return as-is if format is unexpected
      }

      const [datePart, timePart] = parts;
      const dateParts = datePart.split('-');
      const timeParts = timePart.split(':');

      if (dateParts.length !== 3 || timeParts.length !== 3) {
        return dateTimeStr; // Return as-is if format is unexpected
      }

      const [year, month, day] = dateParts.map(Number);
      const [hours, minutes, seconds] = timeParts.map(Number);
      
      // Create date with proper timezone handling
      const callDate = new Date(year, month - 1, day, hours, minutes, seconds);
      const now = new Date();
      
      // Reset time to midnight for date comparison
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const yesterday = new Date(today);
      yesterday.setDate(yesterday.getDate() - 1);
      const callDay = new Date(callDate.getFullYear(), callDate.getMonth(), callDate.getDate());
      
      const diffTime = today.getTime() - callDay.getTime();
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays === 0) {
        // Today - show time in AM/PM format
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        const displayMinutes = minutes.toString().padStart(2, '0');
        return `${displayHours}:${displayMinutes} ${ampm}`;
      } else if (diffDays === 1) {
        // Yesterday
        return 'Yesterday';
      } else if (diffDays < 7) {
        // Within a week
        return `${diffDays} days ago`;
      } else if (diffDays < 14) {
        // 1-2 weeks
        const weeks = Math.floor(diffDays / 7);
        return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
      } else if (diffDays < 30) {
        // 2-4 weeks
        const weeks = Math.floor(diffDays / 7);
        return `${weeks} weeks ago`;
      } else {
        // More than a month - show date
        const monthName = callDate.toLocaleDateString('en-US', { month: 'short' });
        const dayNum = callDate.getDate();
        return `${monthName} ${dayNum}`;
      }
    } catch (error) {
      if (__DEV__) console.error('Error formatting call time:', error, dateTimeStr);
      return dateTimeStr || 'N/A';
    }
  };

  const handleCallPress = (phoneNumber: string) => {
    const phoneUrl = Platform.OS === 'android' ? `tel:${phoneNumber}` : `telprompt:${phoneNumber}`;
    
    Linking.canOpenURL(phoneUrl)
      .then((supported) => {
        if (supported) {
          Linking.openURL(phoneUrl).catch(() => {
            Toast.show({ type: 'error', text1: 'Failed to dial', text2: 'Please try again', position: 'bottom' });
          });
        } else {
          Toast.show({ type: 'error', text1: 'Cannot make call', text2: 'Phone dialer not available', position: 'bottom' });
        }
      })
      .catch(() => {
        Toast.show({ type: 'error', text1: 'Failed to dial', text2: 'Please try again', position: 'bottom' });
      });
  };

  const renderCallItem = ({ item }: { item: CallLogListItem }) => {
    const callIcon = getCallIcon(item.call_type, item.call_status);
    const hasName = item.contact_name && item.contact_name.trim();
    const displayName = hasName ? item.contact_name : 'Unknown';
    
    return (
      <TouchableOpacity 
        style={[styles.callItem, { backgroundColor: theme.colors.surface }]}
        activeOpacity={0.7}
      >
        <View style={styles.callLeft}>
          <View style={[styles.avatar, { backgroundColor: getAvatarColor(item.contact_name, item.caller_number) }]}>
            <Text style={[styles.avatarText, { color: getAvatarTextColor(item.contact_name, item.caller_number) }]}>
              {getContactInitial(item.contact_name, item.caller_number)}
            </Text>
          </View>
          <View style={styles.callInfo}>
            <Text style={[styles.callName, { color: theme.colors.textPrimary }]} numberOfLines={1}>
              {displayName}
            </Text>
            <Text style={[styles.callNumber, { color: theme.colors.textSecondary }]} numberOfLines={1}>
              {item.caller_number}
            </Text>
            <View style={styles.callMeta}>
              <MaterialCommunityIcons 
                name={callIcon.name} 
                size={14} 
                color={callIcon.color}
              />
              <Text style={[styles.callTime, { color: theme.colors.textSecondary }]}>
                {formatCallTime(item.date_time)}
              </Text>
            </View>
          </View>
        </View>
        <View style={styles.callRight}>
          {item.caller_duration && item.caller_duration !== '00:00:00' && item.caller_duration !== '0' && (
            <Text style={[styles.duration, { color: theme.colors.textSecondary }]}>
              {item.caller_duration.includes(':')
                ? item.caller_duration.substring(3)
                : `${Math.floor(Number(item.caller_duration) / 60)}:${String(Number(item.caller_duration) % 60).padStart(2, '0')}`}
            </Text>
          )}
          <TouchableOpacity 
            style={styles.callButton}
            onPress={() => handleCallPress(item.caller_number)}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons 
              name="phone" 
              size={20} 
              color="#4CAF50" 
            />
          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <View style={styles.contentContainer}>
        <AppHeader title="Call Logs" />

        {/* Date Range Filter */}
        <DateRangeFilter onApply={handleDateRangeApply} selectedLabel={dateRangeLabel} />

        {/* Call Type Filter */}
        <FilterBar 
          filters={filters}
          selectedFilter={selectedFilter}
          onFilterChange={handleFilterChange}
        />

        {isLoading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#FF7A3D" />
            <Text style={[styles.loadingText, { color: theme.colors.textSecondary }]}>
              Loading call logs...
            </Text>
          </View>
        ) : (
          <FlatList
            data={callLogs}
            keyExtractor={(item) => item.unique_id}
            renderItem={renderCallItem}
            contentContainerStyle={styles.listContent}
            showsVerticalScrollIndicator={false}
            removeClippedSubviews
            maxToRenderPerBatch={15}
            windowSize={10}
            initialNumToRender={15}
            refreshControl={
              <RefreshControl
                refreshing={refreshing}
                onRefresh={handleRefresh}
                colors={['#FF7A3D']}
                tintColor="#FF7A3D"
                progressBackgroundColor={theme.colorScheme === 'dark' ? '#2A2A2A' : '#FFFFFF'}
              />
            }
            onEndReached={handleLoadMore}
            onEndReachedThreshold={0.5}
            ListEmptyComponent={
              !isLoading ? (
                <View style={styles.emptyContainer}>
                  <MaterialCommunityIcons 
                    name="phone-off" 
                    size={64} 
                    color={theme.colors.textSecondary} 
                  />
                  <Text style={[styles.emptyText, { color: theme.colors.textSecondary }]}>
                    No call logs found
                  </Text>
                </View>
              ) : null
            }
            ListFooterComponent={
              isLoadingMore ? (
                <View style={styles.footerLoader}>
                  <ActivityIndicator size="small" color="#FF7A3D" />
                  <Text style={[styles.loadingMoreText, { color: theme.colors.textSecondary }]}>
                    Loading more...
                  </Text>
                </View>
              ) : null
            }
          />
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: { 
    flex: 1 
  },
  contentContainer: {
    flex: 1,
    paddingHorizontal: 16,
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
  listContent: {
    paddingBottom: 16,
  },
  callItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 10,
    marginBottom: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  callLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  avatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  avatarText: {
    fontSize: 16,
    fontWeight: '700',
  },
  callInfo: {
    flex: 1,
    justifyContent: 'center',
  },
  callName: {
    fontSize: 15,
    fontWeight: '600',
    marginBottom: 1,
    lineHeight: 18,
  },
  callNumber: {
    fontSize: 12,
    marginBottom: 2,
    lineHeight: 16,
  },
  callMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  callTime: {
    fontSize: 12,
    lineHeight: 16,
  },
  callRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  duration: {
    fontSize: 12,
    fontWeight: '600',
    minWidth: 38,
    textAlign: 'right',
  },
  callButton: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: '#E8F5E9',
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    fontSize: 16,
    marginTop: 12,
  },
  footerLoader: {
    paddingVertical: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingMoreText: {
    fontSize: 13,
    marginTop: 8,
    fontWeight: '500',
  },
});

export default CallLogsScreen;
