import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../../contexts/ThemeContext';

const PeakCallHours = ({ data }) => {
  const { theme } = useTheme();
  const isDark = theme.colorScheme === 'dark';

  // Get ranges from API data
  const ranges = data.ranges || [];
  
  if (ranges.length === 0) {
    return null; // Don't render if no data
  }

  const maxCalls = Math.max(...ranges.map(r => r.calls));

  const renderPeakCard = (rangeData, index) => {
    const percentage = (rangeData.calls / maxCalls) * 100;
    
    // Alternate icons and colors for visual variety
    const icons = ['weather-sunny', 'weather-sunset', 'weather-night', 'weather-cloudy'];
    const colors = ['#FF9800', '#FF6B3C', '#9C27B0', '#2196F3'];
    const bgColors = ['#FFF3E0', '#FFE5E0', '#F3E5F5', '#E3F2FD'];
    
    const icon = icons[index % icons.length];
    const iconColor = colors[index % colors.length];
    const iconBg = bgColors[index % bgColors.length];

    return (
      <View key={index} style={[styles.peakCard, { backgroundColor: isDark ? '#2A2A2A' : '#F8F9FA' }]}>
        <View style={styles.peakHeader}>
          <View style={[styles.iconBox, { backgroundColor: iconBg }]}>
            <MaterialCommunityIcons name={icon} size={22} color={iconColor} />
          </View>
          <View style={styles.peakInfo}>
            <Text style={[styles.timeRange, { color: theme.colors.textPrimary }]}>
              {rangeData.range}
            </Text>
            <Text style={[styles.peakCalls, { color: theme.colors.textSecondary }]}>
              {rangeData.calls} calls
            </Text>
          </View>
        </View>

        {/* Progress Bar */}
        <View style={[styles.progressBarContainer, { backgroundColor: isDark ? '#1E1E1E' : '#E0E0E0' }]}>
          <View
            style={[
              styles.progressBarFill,
              {
                width: `${percentage}%`,
                backgroundColor: iconColor,
              },
            ]}
          />
        </View>
      </View>
    );
  };

  return (
    <View style={[styles.card, { backgroundColor: isDark ? '#1E1E1E' : '#FFFFFF' }]}>
      <View style={styles.header}>
        <Text style={[styles.title, { color: theme.colors.textPrimary }]}>
          Peak Call Hours
        </Text>
        <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>
          Highest activity periods
        </Text>
      </View>

      <View style={styles.peakContainer}>
        {ranges.map((rangeData, index) => renderPeakCard(rangeData, index))}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    borderRadius: 16,
    padding: 18,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 3,
  },
  header: {
    marginBottom: 16,
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 13,
    fontWeight: '500',
  },
  peakContainer: {
    gap: 12,
  },
  peakCard: {
    borderRadius: 12,
    padding: 14,
  },
  peakHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  iconBox: {
    width: 44,
    height: 44,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  peakInfo: {
    flex: 1,
  },
  timeRange: {
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 2,
  },
  peakCalls: {
    fontSize: 13,
    fontWeight: '500',
  },
  progressBarContainer: {
    height: 8,
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: '100%',
    borderRadius: 4,
  },
});

export default PeakCallHours;
