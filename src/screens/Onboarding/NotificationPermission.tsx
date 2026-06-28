import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Alert,
  Modal,
  Platform,
  StyleSheet,
} from 'react-native';
import Toast from 'react-native-toast-message';
import { usePermissions } from '../../hooks/usePermissions';
import { useOnboarding } from '../../contexts/OnboardingContext';
import { useTheme } from '../../contexts/ThemeContext';
import PermissionScreen from '../../components/PermissionScreen';

const NotificationPermission: React.FC = () => {
  const { requestPermission, permissions, openAppSettings } = usePermissions();
  const { completeOnboarding } = useOnboarding();
  const { theme } = useTheme();
  const [isLoading, setIsLoading] = useState(false);
  const [showSettingsModal, setShowSettingsModal] = useState(false);

  // Do not auto-navigate on mount; require explicit user action via the button

  const handleRequestPermission = async () => {
    // If running on Android < 13, complete onboarding immediately
    if (Platform.OS === 'android' && Platform.Version < 33) {
      await completeOnboarding();
      Toast.show({
        type: 'success',
        text1: 'All permissions enabled!',
        text2: 'Welcome to the app!',
      });
      // Navigator will automatically show Home screen after onboarding completion
      return;
    }

    setIsLoading(true);
    try {
      // Check if already granted
      if (permissions.notifications === 'granted') {
        await completeOnboarding();
        Toast.show({
          type: 'success',
          text1: 'All permissions enabled!',
          text2: 'Welcome to the app!',
        });
        // Navigator will automatically show Home screen after onboarding completion
        return;
      }

      const status = await requestPermission('notifications');
      
      if (status === 'granted') {
        await completeOnboarding();
        Toast.show({
          type: 'success',
          text1: 'All permissions enabled!',
          text2: 'Welcome to the app!',
        });
        // Navigator will automatically show Home screen after onboarding completion
      } else if (status === 'denied') {
        // Try requesting again
        const retryStatus = await requestPermission('notifications');
        if (retryStatus === 'granted') {
          await completeOnboarding();
          Toast.show({
            type: 'success',
            text1: 'All permissions enabled!',
            text2: 'Welcome to the app!',
          });
          // Navigator will automatically show Home screen after onboarding completion
        } else {
          setShowSettingsModal(true);
        }
      } else if (status === 'blocked') {
        setShowSettingsModal(true);
      }
    } catch (error) {
      console.error('Error requesting notification permission:', error);
      Alert.alert('Error', 'Failed to request notification permission. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleOpenSettings = async () => {
    setShowSettingsModal(false);
    try {
      await openAppSettings();
    } catch (error) {
      console.error('Error opening settings:', error);
    }
  };

  const handleRetry = async () => {
    setShowSettingsModal(false);
    const status = permissions.notifications;
    
    if (status === 'blocked') {
      // If blocked, don't retry - show settings modal again
      setShowSettingsModal(true);
    } else {
      // Try requesting again
      await handleRequestPermission();
    }
  };

  return (
    <>
      <PermissionScreen
        step={4}
        totalSteps={4}
        icon="🔔"
        title="Allow Notifications"
        description="Needed for sync alerts and timely reminders."
        buttonText="Grant Permission"
        isLoading={isLoading}
        onPress={handleRequestPermission}
      />

      {/* Settings Modal */}
      <Modal
        visible={showSettingsModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowSettingsModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, { backgroundColor: theme.colors.surface }]}>
            <Text style={[styles.modalTitle, { color: theme.colors.textPrimary }]}>Notifications Disabled</Text>
            <Text style={[styles.modalBody, { color: theme.colors.textSecondary }]}>
              Please open Settings to enable Notifications to continue.
            </Text>
            
            <View style={styles.modalButtons}>
              <TouchableOpacity
                style={[styles.modalButton, styles.modalButtonSecondary, { backgroundColor: theme.colors.background }]}
                onPress={handleRetry}
              >
                <Text style={[styles.modalButtonTextSecondary, { color: theme.colors.textSecondary }]}>Retry</Text>
              </TouchableOpacity>
              
              <TouchableOpacity
                style={[styles.modalButton, styles.modalButtonPrimary, { backgroundColor: theme.colors.primary }]}
                onPress={handleOpenSettings}
              >
                <Text style={[styles.modalButtonTextPrimary, { color: theme.colors.onPrimary }]}>Open Settings</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </>
  );
};

const styles = StyleSheet.create({
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalContent: {
    borderRadius: 16,
    padding: 24,
    width: '100%',
    maxWidth: 400,
    alignItems: 'center',
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: '700',
    textAlign: 'center',
    marginBottom: 16,
  },
  modalBody: {
    fontSize: 16,
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 24,
  },
  modalButtons: {
    flexDirection: 'row',
    width: '100%',
    gap: 12,
  },
  modalButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalButtonPrimary: {},
  modalButtonSecondary: {},
  modalButtonTextPrimary: {
    fontSize: 16,
    fontWeight: '600',
  },
  modalButtonTextSecondary: {
    fontSize: 16,
    fontWeight: '600',
  },
});

export default NotificationPermission;
