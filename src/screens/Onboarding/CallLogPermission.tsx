import React, { useState, useEffect } from 'react';
import { Alert, Linking } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Toast from 'react-native-toast-message';
import { usePermissions } from '../../hooks/usePermissions';
import { useAuth } from '../../contexts/AuthContext';
import PermissionScreen from '../../components/PermissionScreen';

const CallLogPermission: React.FC = () => {
  const navigation = useNavigation();
  const { requestPermission, permissions } = usePermissions();
  const { isAuthenticated } = useAuth();
  const [isLoading, setIsLoading] = useState(false);

  const handleRequestPermission = async () => {
    setIsLoading(true);
    try {
      // Check if already granted
      if (permissions.callLog === 'granted') {
        navigation.navigate('PhoneStatePermission' as never);
        return;
      }

      // Step 1: Request READ_CALL_LOG
      const callLogStatus = await requestPermission('callLog');
      if (callLogStatus === 'granted') {
        Toast.show({
          type: 'success',
          text1: 'Permission Granted',
          text2: 'Call history access enabled!',
        });
        // Navigate to next screen (Phone State is now a separate step)
        navigation.navigate('PhoneStatePermission' as never);
      } else if (callLogStatus === 'denied') {
        Toast.show({
          type: 'info',
          text1: 'Permission Needed',
          text2: 'Call log permission is needed to continue.',
        });
        Alert.alert(
          'Permission Denied',
          'Call log permission is required to continue. Please grant access in Settings.',
          [
            { text: 'Cancel', style: 'cancel' },
            { text: 'Open Settings', onPress: () => Linking.openSettings() },
          ]
        );
      } else if (callLogStatus === 'blocked') {
        Alert.alert(
          'Permission Blocked',
          'Call log permission has been permanently denied. Please enable it in Settings to continue.',
          [
            { text: 'Cancel', style: 'cancel' },
            { text: 'Open Settings', onPress: () => Linking.openSettings() },
          ]
        );
      }
    } catch (error) {
      console.error('Error requesting call log permission:', error);
      Alert.alert('Error', 'Failed to request call log permission. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <PermissionScreen
      step={2}
      totalSteps={4}
      icon="📞"
      title="Allow access to Call History (Required)"
      description="This is the core feature of the app. We need this permission to show your call history and provide analytics. Your data is encrypted and never sold."
      buttonText="Grant Permission"
      isLoading={isLoading}
      onPress={handleRequestPermission}
    />
  );
};

export default CallLogPermission;
