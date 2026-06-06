import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { useTheme } from '../../contexts/ThemeContext';
import Svg, { Circle, G } from 'react-native-svg';

interface MissedCallsData {
  total_missed: number;
  returned_calls: number;
  callback_pending: number;
}

interface MissedCallsChartProps {
  data: MissedCallsData;
}

const MissedCallsChart: React.FC<MissedCallsChartProps> = ({ data }) => {
  const { theme } = useTheme();
  const isDark = theme.colorScheme === 'dark';

  // Calculate percentages
  const returnedPercentage = data.total_missed > 0 
    ? Math.round((data.returned_calls / data.total_missed) * 100) 
    : 0;
  const pendingPercentage = data.total_missed > 0 
    ? Math.round((data.callback_pending / data.total_missed) * 100) 
    : 0;

  // Donut chart configuration
  const size = 240;
  const strokeWidth = 45;
  const radius = (size - strokeWidth) / 2;
  const circumference = 2 * Math.PI * radius;
  
  // Calculate stroke offsets for segments
  const returnedStroke = (returnedPercentage / 100) * circumference;
  const pendingStroke = (pendingPercentage / 100) * circumference;
  
  return (
    <View style={[styles.card, { backgroundColor: isDark ? '#1E1E1E' : '#FFFFFF' }]}>
      <View style={styles.header}>
        <Text style={[styles.title, { color: theme.colors.textPrimary }]}>
          Missed Calls Analysis
        </Text>
        <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>
          Total missed calls breakdown
        </Text>
      </View>

      <View style={styles.chartContainer}>
        {/* Donut Chart */}
        <View style={styles.donutContainer}>
          <Svg width={size} height={size}>
            <G rotation="-90" origin={`${size / 2}, ${size / 2}`}>
              {/* Green segment (Returned) */}
              <Circle
                cx={size / 2}
                cy={size / 2}
                r={radius}
                stroke="#1ABC9C"
                strokeWidth={strokeWidth}
                fill="transparent"
                strokeDasharray={`${returnedStroke} ${circumference}`}
                strokeLinecap="round"
              />
              {/* Orange segment (Pending) */}
              <Circle
                cx={size / 2}
                cy={size / 2}
                r={radius}
                stroke="#FFA726"
                strokeWidth={strokeWidth}
                fill="transparent"
                strokeDasharray={`${pendingStroke} ${circumference}`}
                strokeDashoffset={-returnedStroke}
                strokeLinecap="round"
              />
            </G>
          </Svg>
          
          {/* Center content */}
          <View style={styles.centerContent}>
            <Text style={[styles.centerNumber, { color: theme.colors.textPrimary }]}>
              {data.total_missed}
            </Text>
            <Text style={[styles.centerLabel, { color: theme.colors.textSecondary }]}>
              Total Missed
            </Text>
          </View>
        </View>
      </View>

      {/* Stats cards */}
      <View style={styles.statsContainer}>
        <View style={[styles.statCard, { backgroundColor: '#E8F8F5' }]}>
          <View style={styles.statHeader}>
            <View style={[styles.dot, { backgroundColor: '#1ABC9C' }]} />
            <Text style={[styles.statLabel, { color: '#2C3E50' }]}>
              Returned Calls
            </Text>
            <Text style={[styles.statCount, { color: '#1ABC9C' }]}>
              {data.returned_calls}
            </Text>
          </View>
          <Text style={[styles.statValue, { color: '#7F8C8D' }]}>
            {returnedPercentage}% of total
          </Text>
        </View>

        <View style={[styles.statCard, { backgroundColor: '#FFF3E0' }]}>
          <View style={styles.statHeader}>
            <View style={[styles.dot, { backgroundColor: '#FFA726' }]} />
            <Text style={[styles.statLabel, { color: '#2C3E50' }]}>
              Callback Pending
            </Text>
            <Text style={[styles.statCount, { color: '#FFA726' }]}>
              {data.callback_pending}
            </Text>
          </View>
          <Text style={[styles.statValue, { color: '#7F8C8D' }]}>
            {pendingPercentage}% of total
          </Text>
        </View>
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
  chartContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  donutContainer: {
    position: 'relative',
    alignItems: 'center',
    justifyContent: 'center',
  },
  centerContent: {
    position: 'absolute',
    alignItems: 'center',
    justifyContent: 'center',
  },
  centerNumber: {
    fontSize: 36,
    fontWeight: '700',
    marginBottom: 4,
  },
  centerLabel: {
    fontSize: 12,
    fontWeight: '500',
  },
  statsContainer: {
    gap: 12,
  },
  statCard: {
    borderRadius: 12,
    padding: 12,
    marginBottom: 8,
  },
  statHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  dot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    marginRight: 8,
  },
  statLabel: {
    fontSize: 15,
    fontWeight: '700',
    flex: 1,
  },
  statValue: {
    fontSize: 13,
    fontWeight: '500',
  },
  statCount: {
    fontSize: 24,
    fontWeight: '700',
  },
});

export default MissedCallsChart;
