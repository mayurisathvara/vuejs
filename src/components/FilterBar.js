import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { useTheme } from '../contexts/ThemeContext';

const FilterBar = ({ filters, selectedFilter, onFilterChange }) => {
  const { theme } = useTheme();
  const isDark = theme.colorScheme === 'dark';

  return (
    <View style={[styles.filterWrapper, { backgroundColor: isDark ? theme.colors.background : '#FFFFFF' }]}>
      <View style={styles.filterContainer}>
        {filters.map((filter) => {
          const isSelected = selectedFilter === filter;
          return (
            <TouchableOpacity
              key={filter}
              style={[
                styles.filterButton,
                {
                  backgroundColor: isSelected
                    ? '#FF7F4D'
                    : 'transparent',
                  borderWidth: isSelected ? 0 : 1,
                  borderColor: isDark ? '#444' : '#E0E0E0',
                },
              ]}
              onPress={() => onFilterChange(filter)}
              activeOpacity={0.6}
            >
              <Text
                style={[
                  styles.filterText,
                  {
                    color: isSelected
                      ? '#FFFFFF'
                      : isDark
                      ? theme.colors.textPrimary
                      : '#555',
                  },
                ]}
                numberOfLines={1}
              >
                {filter}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  filterWrapper: {
    paddingVertical: 10,
    paddingHorizontal: 12,
    marginBottom: 12,
    borderRadius: 12,
  },
  filterContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 10,
  },
  filterButton: {
    flex: 1,
    paddingVertical: 9,
    paddingHorizontal: 15,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 38,
  },
  filterText: {
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
  },
});

export default FilterBar;
