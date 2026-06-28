import React, { useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Animated,
  Dimensions,
  StatusBar,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import { useTheme } from '../contexts/ThemeContext';

const { width } = Dimensions.get('window');

interface SplashScreenProps {
  onAnimationFinish: () => void;
}

const SplashScreen: React.FC<SplashScreenProps> = ({ onAnimationFinish }) => {
  const { theme } = useTheme();
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.8)).current;
  const logoAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // Start animations
    const startAnimations = () => {
      Animated.parallel([
        Animated.timing(fadeAnim, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
        }),
        Animated.spring(scaleAnim, {
          toValue: 1,
          tension: 50,
          friction: 7,
          useNativeDriver: true,
        }),
        Animated.timing(logoAnim, {
          toValue: 1,
          duration: 1500,
          useNativeDriver: true,
        }),
      ]).start();
    };

    // Start animations after a short delay
    const timer = setTimeout(startAnimations, 300);

    // Complete splash after animation
    const completeTimer = setTimeout(() => {
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 500,
        useNativeDriver: true,
      }).start(() => {
        onAnimationFinish();
      });
    }, 3000);

    return () => {
      clearTimeout(timer);
      clearTimeout(completeTimer);
    };
  }, [fadeAnim, scaleAnim, logoAnim, onAnimationFinish]);

  return (
    <View style={styles.container}>
      <StatusBar 
        barStyle="light-content" 
        backgroundColor="transparent" 
        translucent 
      />
      <LinearGradient
        colors={[theme.colors.primary, theme.colors.secondary]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.gradient}
      >
        <Animated.View
          style={[
            styles.content,
            {
              opacity: fadeAnim,
              transform: [
                { scale: scaleAnim },
                { translateY: logoAnim.interpolate({
                  inputRange: [0, 1],
                  outputRange: [50, 0],
                })}
              ],
            },
          ]}
        >
          {/* App Icon/Logo */}
          <View style={styles.logoContainer}>
            <View style={[styles.logoCircle, { backgroundColor: theme.colors.onPrimary + '20' }]}>
              <Text style={styles.logoText}>📱</Text>
            </View>
          </View>

          {/* App Logo/Title */}
          <Animated.View style={[styles.titleContainer, { opacity: logoAnim }]}>
            <Text style={[styles.appName, { color: theme.colors.onPrimary }]}>
              Callytics
            </Text>
            <Text style={[styles.tagline, { color: theme.colors.onPrimary }]}>
              Smart Call Tracking & Analytics
            </Text>
          </Animated.View>

          {/* Loading indicator */}
          <Animated.View style={[styles.loadingContainer, { opacity: logoAnim }]}>
            <View style={[styles.loadingDot, { backgroundColor: theme.colors.onPrimary }]} />
            <View style={[styles.loadingDot, { backgroundColor: theme.colors.onPrimary, opacity: 0.85 }]} />
            <View style={[styles.loadingDot, { backgroundColor: theme.colors.onPrimary, opacity: 0.7 }]} />
          </Animated.View>
        </Animated.View>
      </LinearGradient>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  gradient: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 40,
  },
  logoCircle: {
    width: width * 0.3,
    height: width * 0.3,
    borderRadius: width * 0.15,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  logoText: {
    fontSize: 60,
  },
  titleContainer: {
    alignItems: 'center',
    marginBottom: 60,
  },
  appName: {
    fontSize: 32,
    fontWeight: 'bold',
    marginBottom: 8,
    textAlign: 'center',
  },
  tagline: {
    fontSize: 16,
    opacity: 0.9,
    textAlign: 'center',
  },
  loadingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginHorizontal: 4,
    opacity: 0.7,
  },
  // Note: explicit CSS animationDelay is not supported in RN StyleSheet; dots use static opacity
});

export default SplashScreen;
