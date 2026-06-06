import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext';

interface AppHeaderProps {
  title: string;
  showProfile?: boolean;
  showNotification?: boolean;
}

const AppHeader: React.FC<AppHeaderProps> = ({ 
  title, 
  showProfile = true, 
  showNotification = true 
}) => {
  const { theme } = useTheme();
  const { user } = useAuth();
  const isDark = theme.colorScheme === 'dark';

  const getInitial = () => {
    if (user?.name) {
      return user.name.charAt(0).toUpperCase();
    }
    return 'A';
  };

  return (
    <View style={styles.header}>
      <Text style={[styles.headerTitle, { color: theme.colors.textPrimary }]}>
        {title}
      </Text>
      <View style={styles.headerIcons}>
        {showNotification && (
          <TouchableOpacity 
            style={[styles.iconButton, { 
              backgroundColor: isDark ? '#2A2A2A' : '#F5F5F5', 
              borderRadius: 20 
            }]}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons 
              name="bell-outline" 
              size={22} 
              color={theme.colors.textPrimary} 
            />
            <View style={styles.notificationDot} />
          </TouchableOpacity>
        )}
        {showProfile && (
          <TouchableOpacity 
            style={[styles.profileAvatar, { 
              backgroundColor: isDark ? '#1E3A5F' : '#E3F2FD' 
            }]}
            activeOpacity={0.7}
          >
            <Text style={[styles.profileInitial, { 
              color: isDark ? '#64B5F6' : '#2196F3' 
            }]}>{getInitial()}</Text>
          </TouchableOpacity>
        )}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 16,
    paddingBottom: 16,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '700',
  },
  headerIcons: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  iconButton: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  notificationDot: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#FF5722',
  },
  profileAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  profileInitial: {
    fontSize: 16,
    fontWeight: '700',
  },
});

export default AppHeader;
