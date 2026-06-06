import React, { useEffect, useState } from 'react';
import { Alert, Linking } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { usePermissions } from '../../hooks/usePermissions';
import { useAuth } from '../../contexts/AuthContext';
import Toast from 'react-native-toast-message';
import PermissionScreen from '../../components/PermissionScreen';

const PhoneStatePermission: React.FC = () => {
  const navigation = useNavigation();
  const { requestPermission, permissions } = usePermissions();
  const { isAuthenticated } = useAuth();
  const [isLoading, setIsLoading] = useState(false);

  const handleRequestPermission = async () => {
    setIsLoading(true);
    try {
      // Check if already granted
      if (permissions.phoneState === 'granted') {
        navigation.navigate('NotificationPermission' as never);
        return;
      }

      // Request Phone State permission
      const phoneStateStatus = await requestPermission('phoneState');
      if (phoneStateStatus === 'granted') {
        Toast.show({ 
          type: 'success', 
          text1: 'Permission Granted', 
          text2: 'Phone state access enabled!' 
        });
        navigation.navigate('NotificationPermission' as never);
      } else {
        Alert.alert(
          'Permission Required',
          'Phone state permission is required to continue. Please grant access in Settings.',
          [
            { text: 'Cancel', style: 'cancel' },
            { text: 'Open Settings', onPress: () => Linking.openSettings() },
          ]
        );
      }
    } catch (error) {
      console.error('Error requesting phone state permission:', error);
      Alert.alert('Error', 'Failed to request phone state permission. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <PermissionScreen
      step={3}
      totalSteps={4}
      icon="📶"
      title="Allow Phone Access"
      description="Required for call analytics and tracking call states. This helps us provide accurate call logs."
      buttonText="Grant Permission"
      isLoading={isLoading}
      onPress={handleRequestPermission}
    />
  );
};

export default PhoneStatePermission;
