import React from 'react';
import { View, Text, StyleSheet, Dimensions } from 'react-native';
import { PieChart } from 'react-native-chart-kit';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../../contexts/ThemeContext';

const CallTypeDistribution = ({ data }) => {
  const { theme } = useTheme();
  const isDark = theme.colorScheme === 'dark';
  const screenWidth = Dimensions.get('window').width;

  // Transform data for pie chart
  const chartData = [
    {
      name: 'Incoming',
      population: data.incoming,
      color: '#4472C4',
      legendFontColor: isDark ? '#E0E0E0' : '#666',
      legendFontSize: 13,
    },
    {
      name: 'Outgoing',
      population: data.outgoing,
      color: '#5B9BD5',
      legendFontColor: isDark ? '#E0E0E0' : '#666',
      legendFontSize: 13,
    },
    {
      name: 'Missed',
      population: data.missed,
      color: '#ED7D31',
      legendFontColor: isDark ? '#E0E0E0' : '#666',
      legendFontSize: 13,
    },
  ];

  const chartConfig = {
    color: (opacity = 1) => `rgba(0, 0, 0, ${opacity})`,
  };

  const totalCalls = data.incoming + data.outgoing + data.missed;

  return (
    <View style={[styles.card, { backgroundColor: isDark ? '#1E1E1E' : '#FFFFFF' }]}>
      <View style={styles.header}>
        <View>
          <Text style={[styles.title, { color: theme.colors.textPrimary }]}>
            Call Type Distribution
          </Text>
          <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>
            Total {totalCalls} calls
          </Text>
        </View>
      </View>

      <View style={styles.chartContainer}>
        <PieChart
          data={chartData}
          width={screenWidth - 64}
          height={200}
          chartConfig={chartConfig}
          accessor="population"
          backgroundColor="transparent"
          paddingLeft="15"
          absolute
          hasLegend={true}
          avoidFalseZero={true}
          strokeWidth={3}
          strokeColor={isDark ? '#1E1E1E' : '#FFFFFF'}
        />
      </View>

      {/* Custom Legend with Icons */}
      <View style={styles.legendContainer}>
        <View style={styles.legendItem}>
          <MaterialCommunityIcons name="phone-incoming" size={18} color="#4472C4" />
          <Text style={[styles.legendText, { color: theme.colors.textSecondary }]}>
            Incoming
          </Text>
          <Text style={[styles.legendValue, { color: theme.colors.textPrimary }]}>
            {data.incoming}
          </Text>
        </View>

        <View style={styles.legendItem}>
          <MaterialCommunityIcons name="phone-outgoing" size={18} color="#5B9BD5" />
          <Text style={[styles.legendText, { color: theme.colors.textSecondary }]}>
            Outgoing
          </Text>
          <Text style={[styles.legendValue, { color: theme.colors.textPrimary }]}>
            {data.outgoing}
          </Text>
        </View>

        <View style={styles.legendItem}>
          <MaterialCommunityIcons name="phone-missed" size={18} color="#ED7D31" />
          <Text style={[styles.legendText, { color: theme.colors.textSecondary }]}>
            Missed
          </Text>
          <Text style={[styles.legendValue, { color: theme.colors.textPrimary }]}>
            {data.missed}
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
    marginVertical: 8,
  },
  legendContainer: {
    marginTop: 12,
    gap: 10,
  },
  legendItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    paddingVertical: 6,
  },
  legendText: {
    fontSize: 14,
    fontWeight: '500',
    flex: 1,
  },
  legendValue: {
    fontSize: 15,
    fontWeight: '700',
  },
});

export default CallTypeDistribution;
