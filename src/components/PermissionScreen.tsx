import React from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  BackHandler,
  Alert,
} from 'react-native';
import { useTheme } from '../contexts/ThemeContext';
import { useBackHandler } from '../hooks/useBackHandler';

interface PermissionScreenProps {
  step: number;
  totalSteps: number;
  icon: string;
  title: string;
  description: string;
  buttonText: string;
  secondaryButtonText?: string;
  isLoading: boolean;
  onPress: () => void;
  onSecondaryPress?: () => void;
}

const PermissionScreen: React.FC<PermissionScreenProps> = ({
  step,
  totalSteps,
  icon,
  title,
  description,
  buttonText,
  secondaryButtonText,
  isLoading,
  onPress,
  onSecondaryPress,
}) => {
  const { theme } = useTheme();
  const progressPercentage = (step / totalSteps) * 100;

  // Prevent going back from permission screens
  useBackHandler({
    enabled: true,
    onBackPress: () => {
      // Show exit confirmation
      Alert.alert(
        'Exit App',
        'Are you sure you want to exit the app?',
        [
          {
            text: 'Cancel',
            style: 'cancel',
          },
          {
            text: 'Exit',
            style: 'destructive',
            onPress: () => BackHandler.exitApp(),
          },
        ]
      );
      return true; // Prevent default back behavior
    },
  });

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <View style={[styles.card, { backgroundColor: theme.colors.surface }]}>
        {/* Progress Indicator */}
        <View style={styles.progressContainer}>
          <Text style={[styles.progressText, { color: theme.colors.textSecondary }]}>
            {step}/{totalSteps}
          </Text>
          <View style={[styles.progressBar, { backgroundColor: theme.colors.divider }]}>
            <View 
              style={[
                styles.progressFill, 
                { 
                  width: `${progressPercentage}%`, 
                  backgroundColor: theme.colors.primary 
                }
              ]} 
            />
          </View>
        </View>

        {/* Illustration */}
        <View style={[styles.illustration, { backgroundColor: theme.colors.background }]}>
          <Text style={styles.illustrationText}>{icon}</Text>
        </View>

        {/* Content */}
        <Text style={[styles.title, { color: theme.colors.textPrimary }]}>{title}</Text>
        <Text style={[styles.description, { color: theme.colors.textSecondary }]}>
          {description}
        </Text>

        {/* CTA Button */}
        <TouchableOpacity
          style={[
            styles.button, 
            { backgroundColor: theme.colors.primary },
            isLoading && { backgroundColor: theme.colors.disabled }
          ]}
          onPress={onPress}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color={theme.colors.onPrimary} size="small" />
          ) : (
            <Text style={[styles.buttonText, { color: theme.colors.onPrimary }]}>
              {buttonText}
            </Text>
          )}
        </TouchableOpacity>

        {/* Optional Secondary Button (e.g., Skip) */}
        {secondaryButtonText && onSecondaryPress && (
          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={onSecondaryPress}
            disabled={isLoading}
          >
            <Text style={[styles.secondaryButtonText, { color: theme.colors.textSecondary }]}>
              {secondaryButtonText}
            </Text>
          </TouchableOpacity>
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  card: {
    borderRadius: 16,
    padding: 32,
    width: '100%',
    maxWidth: 400,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 8,
  },
  progressContainer: {
    width: '100%',
    marginBottom: 32,
  },
  progressText: {
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
    marginBottom: 8,
  },
  progressBar: {
    height: 4,
    borderRadius: 2,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 2,
  },
  illustration: {
    width: 80,
    height: 80,
    borderRadius: 40,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 24,
  },
  illustrationText: {
    fontSize: 32,
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    textAlign: 'center',
    marginBottom: 16,
  },
  description: {
    fontSize: 16,
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 32,
  },
  button: {
    paddingHorizontal: 32,
    paddingVertical: 16,
    borderRadius: 12,
    width: '100%',
    alignItems: 'center',
  },
  buttonText: {
    fontSize: 16,
    fontWeight: '600',
  },
  secondaryButton: {
    paddingVertical: 12,
    marginTop: 12,
    width: '100%',
    alignItems: 'center',
  },
  secondaryButtonText: {
    fontSize: 15,
    fontWeight: '500',
  },
});

export default PermissionScreen;

