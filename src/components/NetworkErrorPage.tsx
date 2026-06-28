import React, { useEffect, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Animated,

  SafeAreaView,
  StatusBar,
} from 'react-native';
import { useTheme } from '../contexts/ThemeContext';
import { useNetwork } from '../contexts/NetworkContext';

const NetworkErrorPage: React.FC = () => {
  const { theme } = useTheme();
  const { retryConnection, isNetworkLoading } = useNetwork();
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.8)).current;
  const iconAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // Start animations
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 800,
        useNativeDriver: true,
      }),
      Animated.spring(scaleAnim, {
        toValue: 1,
        tension: 50,
        friction: 8,
        useNativeDriver: true,
      }),
      Animated.timing(iconAnim, {
        toValue: 1,
        duration: 1000,
        useNativeDriver: true,
      }),
    ]).start();
  }, [fadeAnim, scaleAnim, iconAnim]);

  const handleRetry = () => {
    // Add a small animation feedback
    Animated.sequence([
      Animated.timing(scaleAnim, {
        toValue: 0.95,
        duration: 100,
        useNativeDriver: true,
      }),
      Animated.timing(scaleAnim, {
        toValue: 1,
        duration: 100,
        useNativeDriver: true,
      }),
    ]).start();
    
    retryConnection();
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <Animated.View
        style={[
          styles.content,
          {
            opacity: fadeAnim,
            transform: [{ scale: scaleAnim }],
          },
        ]}
      >
        {/* Icon Container */}
        <Animated.View
          style={[
            styles.iconContainer,
            {
              backgroundColor: theme.colors.surface,
              opacity: iconAnim,
              transform: [
                {
                  scale: iconAnim.interpolate({
                    inputRange: [0, 1],
                    outputRange: [0.5, 1],
                  }),
                },
              ],
            },
          ]}
        >
          <Text style={styles.icon}>📶</Text>
        </Animated.View>

        {/* Title */}
        <Text style={[styles.title, { color: theme.colors.textPrimary }]}>
          No Internet Connection
        </Text>

        {/* Description */}
        <Text style={[styles.description, { color: theme.colors.textSecondary }]}>
          Please check your internet connection and try again.
        </Text>

        {/* Retry Button */}
        <TouchableOpacity
          style={[
            styles.retryButton,
            {
              backgroundColor: theme.colors.primary,
            },
            isNetworkLoading && { backgroundColor: theme.colors.disabled },
          ]}
          onPress={handleRetry}
          disabled={isNetworkLoading}
        >
          <Text style={[styles.retryButtonText, { color: theme.colors.onPrimary }]}>
            {isNetworkLoading ? 'Checking...' : 'Retry'}
          </Text>
        </TouchableOpacity>

        {/* Additional Info */}
        <View style={styles.infoContainer}>
          <Text style={[styles.infoText, { color: theme.colors.textSecondary }]}>
            Make sure you're connected to Wi-Fi or mobile data
          </Text>
        </View>
      </Animated.View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    alignItems: 'center',
    paddingHorizontal: 32,
    maxWidth: 400,
  },
  iconContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 32,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 8,
  },
  icon: {
    fontSize: 48,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 16,
  },
  description: {
    fontSize: 16,
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 40,
  },
  retryButton: {
    paddingHorizontal: 32,
    paddingVertical: 16,
    borderRadius: 12,
    minWidth: 160,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  retryButtonText: {
    fontSize: 16,
    fontWeight: '600',
  },
  infoContainer: {
    marginTop: 32,
    alignItems: 'center',
  },
  infoText: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
  },
});

export default NetworkErrorPage;
