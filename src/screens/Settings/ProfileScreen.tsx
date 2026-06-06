import React from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { useTheme } from '../../contexts/ThemeContext';
import { useAuth } from '../../contexts/AuthContext';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';

const ProfileScreen: React.FC = () => {
  const { theme } = useTheme();
  const { user } = useAuth();

  return (
    <ScrollView 
      style={[styles.container, { backgroundColor: theme.colors.background }]}
      contentContainerStyle={styles.content}
    >
      {/* Profile Header */}
      <View style={styles.profileHeader}>
        <View style={[styles.avatarContainer, { backgroundColor: theme.colors.surface }]}>
          <MaterialCommunityIcons name="account" size={60} color="#2563EB" />
        </View>
      </View>

      {/* Profile Info */}
      <Text style={[styles.sectionTitle, { color: theme.colors.textSecondary }]}>
        PERSONAL INFORMATION
      </Text>

      <View style={[styles.section, { backgroundColor: theme.colors.surface }]}>
        {/* Name */}
        <View style={styles.infoItem}>
          <View style={[styles.iconContainer, { backgroundColor: '#DBEAFE' }]}>
            <MaterialCommunityIcons name="account-outline" size={20} color="#3B82F6" />
          </View>
          <View style={styles.infoContent}>
            <Text style={[styles.label, { color: theme.colors.textSecondary }]}>
              Name
            </Text>
            <Text style={[styles.value, { color: theme.colors.textPrimary }]}>
              {user?.name || 'Not set'}
            </Text>
          </View>
        </View>

        <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />

        {/* Mobile */}
        <View style={styles.infoItem}>
          <View style={[styles.iconContainer, { backgroundColor: '#D1FAE5' }]}>
            <MaterialCommunityIcons name="phone-outline" size={20} color="#10B981" />
          </View>
          <View style={styles.infoContent}>
            <Text style={[styles.label, { color: theme.colors.textSecondary }]}>
              Mobile
            </Text>
            <Text style={[styles.value, { color: theme.colors.textPrimary }]}>
              {user?.mobile || 'Not set'}
            </Text>
          </View>
        </View>

        <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />

        {/* Department */}
        <View style={styles.infoItem}>
          <View style={[styles.iconContainer, { backgroundColor: '#E0E7FF' }]}>
            <MaterialCommunityIcons name="badge-account-outline" size={20} color="#6366F1" />
          </View>
          <View style={styles.infoContent}>
            <Text style={[styles.label, { color: theme.colors.textSecondary }]}>
              Department
            </Text>
            <Text style={[styles.value, { color: theme.colors.textPrimary }]}>
              {user?.department_name || 'Not set'}
            </Text>
          </View>
        </View>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  content: {
    padding: 16,
  },
  profileHeader: {
    alignItems: 'center',
    paddingVertical: 24,
  },
  avatarContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '600',
    letterSpacing: 0.5,
    marginTop: 8,
    marginBottom: 12,
    marginLeft: 4,
  },
  section: {
    borderRadius: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
  },
  infoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  infoContent: {
    flex: 1,
  },
  label: {
    fontSize: 13,
    marginBottom: 4,
  },
  value: {
    fontSize: 16,
    fontWeight: '500',
  },
  divider: {
    height: 1,
    marginLeft: 68,
  },
});

export default ProfileScreen;
