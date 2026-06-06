import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useCallLogSync } from '../contexts/CallLogContext';
import { useTheme } from '../contexts/ThemeContext';
import Toast from 'react-native-toast-message';

const CallLogSyncButton: React.FC = () => {
  const { isSyncing, pendingCount, lastSyncTime, manualSync, error } = useCallLogSync();
  const { theme } = useTheme();

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
      const errorMessage = err?.message || error || 'Failed to sync call logs';
      Toast.show({
        type: 'error',
        text1: 'Sync Failed',
        text2: errorMessage,
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
    if (minutes < 60) return `${minutes} min ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    return `${days} day${days > 1 ? 's' : ''} ago`;
  };

  return (
    <View style={[styles.container, { backgroundColor: theme.colors.surface }]}>
      <View style={styles.row}>
        <View style={[styles.iconContainer, { backgroundColor: '#D1FAE5' }]}>
          <MaterialCommunityIcons 
            name="cloud-check" 
            size={24} 
            color="#10B981" 
          />
        </View>
        
        <View style={styles.infoSection}>
          <Text style={[styles.title, { color: theme.colors.textPrimary }]}>
            {pendingCount > 0 ? `${pendingCount} pending call logs` : 'All synced'}
          </Text>
          <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>
            Last sync: {formatLastSync()}
          </Text>
        </View>
        
        <TouchableOpacity
          style={styles.syncButton}
          onPress={handleSync}
          disabled={isSyncing}
          activeOpacity={0.7}
        >
          {isSyncing ? (
            <ActivityIndicator color="#F59E0B" size="small" />
          ) : (
            <MaterialCommunityIcons name="sync" size={24} color="#F59E0B" />
          )}
        </TouchableOpacity>
      </View>

      {error && (
        <View style={styles.errorContainer}>
          <MaterialCommunityIcons name="alert-circle" size={14} color="#ef4444" />
          <Text style={styles.errorText}>{error}</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 16,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconContainer: {
    width: 44,
    height: 44,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  infoSection: {
    flex: 1,
  },
  title: {
    fontSize: 16,
    fontWeight: '500',
    marginBottom: 2,
  },
  subtitle: {
    fontSize: 13,
  },
  syncButton: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 12,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(0,0,0,0.05)',
  },
  errorText: {
    fontSize: 12,
    color: '#ef4444',
    marginLeft: 6,
    flex: 1,
  },
});

export default CallLogSyncButton;
