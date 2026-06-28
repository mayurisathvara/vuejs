import React, { useState } from 'react';
import { Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Toast from 'react-native-toast-message';
import { usePermissions } from '../../hooks/usePermissions';

import PermissionScreen from '../../components/PermissionScreen';

const ContactsPermission: React.FC = () => {
  const navigation = useNavigation();
  const { requestPermission, permissions } = usePermissions();

  const [isLoading, setIsLoading] = useState(false);

  const handleRequestPermission = async () => {
    setIsLoading(true);
    try {
      // Check if already granted
      if (permissions.contacts === 'granted') {
        navigation.navigate('CallLogPermission' as never);
        return;
      }

      const status = await requestPermission('contacts');
      
      if (status === 'granted') {
        Toast.show({
          type: 'success',
          text1: 'Permission Granted',
          text2: 'Contacts access enabled!',
        });
        navigation.navigate('CallLogPermission' as never);
      } else if (status === 'denied' || status === 'blocked') {
        Alert.alert(
          'Permission Denied',
          'You can grant this permission later in Settings if needed. The app will still work without contact names.',
          [
            { text: 'Continue Anyway', onPress: () => navigation.navigate('CallLogPermission' as never) },
            { text: 'Try Again', onPress: handleRequestPermission },
          ]
        );
      }
    } catch (error) {
      console.error('Error requesting contacts permission:', error);
      Alert.alert('Error', 'Failed to request contacts permission. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleSkip = () => {
    Alert.alert(
      'Skip Contacts Permission?',
      'Without contacts access, phone numbers will be shown instead of contact names. You can grant this permission later in Settings.',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Skip', style: 'default', onPress: () => navigation.navigate('CallLogPermission' as never) },
      ]
    );
  };

  return (
    <PermissionScreen
      step={1}
      totalSteps={4}
      icon="📱"
      title="Allow access to Contacts (Optional)"
      description="Shows contact names in your call history. You can skip this and the app will still work."
      buttonText="Grant Permission"
      secondaryButtonText="Skip"
      isLoading={isLoading}
      onPress={handleRequestPermission}
      onSecondaryPress={handleSkip}
    />
  );
};

export default ContactsPermission;
