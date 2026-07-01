import React from 'react';
import { View, Text } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';

interface ToastProps {
  text1?: string;
  text2?: string;
}

export const toastConfig = {
  success: ({ text1, text2 }: ToastProps) => (
    <View style={{
      backgroundColor: '#FFFFFF',
      paddingHorizontal: 16,
      paddingVertical: 12,
      borderRadius: 12,
      flexDirection: 'row',
      alignItems: 'center',
      shadowColor: '#000',
      shadowOffset: { width: 0, height: 2 },
      shadowOpacity: 0.15,
      shadowRadius: 8,
      elevation: 4,
      marginHorizontal: 16,
      borderLeftWidth: 4,
      borderLeftColor: '#10B981',
    }}>
      <View style={{
        width: 32,
        height: 32,
        borderRadius: 16,
        backgroundColor: '#D1FAE5',
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 12,
      }}>
        <MaterialCommunityIcons name="check-circle" size={20} color="#10B981" />
      </View>
      <View style={{ flex: 1 }}>
        <Text style={{
          fontSize: 15,
          fontWeight: '600',
          color: '#1F2937',
          marginBottom: 2,
        }}>
          {text1}
        </Text>
        <Text style={{
          fontSize: 13,
          color: '#6B7280',
        }}>
          {text2}
        </Text>
      </View>
    </View>
  ),
  error: ({ text1, text2 }: ToastProps) => (
    <View style={{
      backgroundColor: '#FFFFFF',
      paddingHorizontal: 16,
      paddingVertical: 12,
      borderRadius: 12,
      flexDirection: 'row',
      alignItems: 'center',
      shadowColor: '#000',
      shadowOffset: { width: 0, height: 2 },
      shadowOpacity: 0.15,
      shadowRadius: 8,
      elevation: 4,
      marginHorizontal: 16,
      borderLeftWidth: 4,
      borderLeftColor: '#EF4444',
    }}>
      <View style={{
        width: 32,
        height: 32,
        borderRadius: 16,
        backgroundColor: '#FEE2E2',
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 12,
      }}>
        <MaterialCommunityIcons name="alert-circle" size={20} color="#EF4444" />
      </View>
      <View style={{ flex: 1 }}>
        <Text style={{
          fontSize: 15,
          fontWeight: '600',
          color: '#1F2937',
          marginBottom: 2,
        }}>
          {text1}
        </Text>
        <Text style={{
          fontSize: 13,
          color: '#6B7280',
        }}>
          {text2}
        </Text>
      </View>
    </View>
  ),
};
