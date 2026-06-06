import React from 'react';
import { View, Text, StyleSheet, Dimensions } from 'react-native';
import { LineChart } from 'react-native-chart-kit';
import { useTheme } from '../../contexts/ThemeContext';

const DailyCallVolume = ({ data, avgPerDay }) => {
  const { theme } = useTheme();
  const isDark = theme.colorScheme === 'dark';
  const screenWidth = Dimensions.get('window').width;

  const chartConfig = {
    backgroundColor: isDark ? '#1E1E1E' : '#FFFFFF',
    backgroundGradientFrom: isDark ? '#1E1E1E' : '#FFFFFF',
    backgroundGradientTo: isDark ? '#1E1E1E' : '#FFFFFF',
    decimalPlaces: 0,
    color: (opacity = 1) => `rgba(255, 107, 60, ${opacity})`,
    labelColor: (opacity = 1) => (isDark ? `rgba(224, 224, 224, ${opacity})` : `rgba(102, 102, 102, ${opacity})`),
    style: {
      borderRadius: 16,
    },
    propsForDots: {
      r: '6',
      strokeWidth: '2',
      stroke: '#FF6B3C',
      fill: '#FFFFFF',
    },
    propsForBackgroundLines: {
      strokeDasharray: '',
      stroke: isDark ? '#333' : '#E0E0E0',
      strokeWidth: 1,
    },
  };

  const chartData = {
    labels: data.labels,
    datasets: [
      {
        data: data.values,
        color: (opacity = 1) => `rgba(255, 107, 60, ${opacity})`,
        strokeWidth: 3,
      },
    ],
  };

  return (
    <View style={[styles.card, { backgroundColor: isDark ? '#1E1E1E' : '#FFFFFF' }]}>
      <View style={styles.header}>
        <View>
          <Text style={[styles.title, { color: theme.colors.textPrimary }]}>
            Daily Call Volume
          </Text>
          <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>
            Last 7 days • Avg {avgPerDay} calls/day
          </Text>
        </View>
      </View>

      <View style={styles.chartContainer}>
        <LineChart
          data={chartData}
          width={screenWidth - 64}
          height={200}
          chartConfig={chartConfig}
          bezier
          style={styles.chart}
          withInnerLines={true}
          withOuterLines={true}
          withVerticalLines={false}
          withHorizontalLines={true}
          withVerticalLabels={true}
          withHorizontalLabels={true}
          fromZero={true}
        />
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
    marginBottom: 8,
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
    marginTop: 4,
  },
  chart: {
    borderRadius: 16,
  },
});

export default DailyCallVolume;
