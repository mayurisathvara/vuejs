import React from 'react';
import { TouchableOpacity, Text, StyleSheet } from 'react-native';
import { useTheme } from '../contexts/ThemeContext';

const ThemeToggle: React.FC = () => {
  const { isDark, toggleTheme } = useTheme();

  return (
    <TouchableOpacity style={[styles.toggle, { backgroundColor: 'transparent' }]} onPress={toggleTheme}>
      <Text style={[styles.toggleText, { color: isDark ? '#FFD700' : '#FFA500' }]}>
        {isDark ? '☀️' : '🌙'}
      </Text>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  toggle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 8,
  },
  toggleText: {
    fontSize: 16,
  },
});

export default ThemeToggle;
