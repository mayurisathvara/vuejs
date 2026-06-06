import React from 'react';
import { View, StyleSheet, ScrollView, Text, Alert, TouchableOpacity, SafeAreaView, StatusBar, Switch } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { SettingsStackParamList } from '../navigation/SettingsStack';
import CallLogSyncButton from '../components/CallLogSyncButton';
import AppHeader from '../components/AppHeader';

type SettingsScreenNavigationProp = StackNavigationProp<SettingsStackParamList, 'SettingsMain'>;

const SettingsScreen: React.FC = () => {
  const { theme, isDark, toggleTheme } = useTheme();
  const { user, logout } = useAuth();
  const navigation = useNavigation<SettingsScreenNavigationProp>();

  const handleLogout = async () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            await logout();
          },
        },
      ]
    );
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <View style={styles.headerContainer}>
        <AppHeader title="Settings" showNotification={false} showProfile={false} />
      </View>
      
      <ScrollView 
        style={[styles.scrollView, { backgroundColor: theme.colors.background }]}
        contentContainerStyle={styles.scrollContainer}
        showsVerticalScrollIndicator={false}
      >

        {/* Call Log Sync Section */}
        <View style={styles.syncSection}>
          <CallLogSyncButton />
        </View>

        {/* PREFERENCES Section */}
        <Text style={[styles.sectionTitle, { color: theme.colors.textSecondary }]}>
          PREFERENCES
        </Text>
        <View style={[styles.sectionCard, { backgroundColor: theme.colors.surface }]}>
          {/* Dark Theme */}
          <TouchableOpacity 
            style={styles.settingItem}
            onPress={toggleTheme}
            activeOpacity={0.7}
          >
            <View style={[styles.iconContainer, { backgroundColor: '#FEF3C7' }]}>
              <MaterialCommunityIcons 
                name="weather-night" 
                size={22} 
                color="#F59E0B" 
              />
            </View>
            <View style={styles.settingContent}>
              <Text style={[styles.settingTitle, { color: theme.colors.textPrimary }]}>
                Dark Theme
              </Text>
              <Text style={[styles.settingDescription, { color: theme.colors.textSecondary }]}>
                {isDark ? 'Enabled' : 'Disabled'}
              </Text>
            </View>
            <Switch
              value={isDark}
              onValueChange={toggleTheme}
              trackColor={{ false: '#E5E7EB', true: '#93C5FD' }}
              thumbColor={isDark ? '#2563EB' : '#F9FAFB'}
              ios_backgroundColor="#E5E7EB"
            />
          </TouchableOpacity>
        </View>

        {/* ACCOUNT Section */}
        <Text style={[styles.sectionTitle, { color: theme.colors.textSecondary }]}>
          ACCOUNT
        </Text>
        <View style={[styles.sectionCard, { backgroundColor: theme.colors.surface }]}>
          {/* Profile */}
          <TouchableOpacity 
            style={styles.settingItem}
            activeOpacity={0.7}
            onPress={() => navigation.navigate('Profile')}
          >
            <View style={[styles.iconContainer, { backgroundColor: '#DBEAFE' }]}>
              <MaterialCommunityIcons 
                name="account-outline" 
                size={22} 
                color="#3B82F6" 
              />
            </View>
            <View style={styles.settingContent}>
              <Text style={[styles.settingTitle, { color: theme.colors.textPrimary }]}>
                Profile
              </Text>
              <Text style={[styles.settingDescription, { color: theme.colors.textSecondary }]}>
                Manage your profile
              </Text>
            </View>
            <MaterialCommunityIcons 
              name="chevron-right" 
              size={22} 
              color={theme.colors.textSecondary} 
            />
          </TouchableOpacity>

          {/* Privacy */}
          <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />
          <TouchableOpacity 
            style={styles.settingItem}
            activeOpacity={0.7}
            onPress={() => navigation.navigate('Privacy')}
          >
            <View style={[styles.iconContainer, { backgroundColor: '#F3E8FF' }]}>
              <MaterialCommunityIcons 
                name="shield-check-outline" 
                size={22} 
                color="#A855F7" 
              />
            </View>
            <View style={styles.settingContent}>
              <Text style={[styles.settingTitle, { color: theme.colors.textPrimary }]}>
                Privacy
              </Text>
              <Text style={[styles.settingDescription, { color: theme.colors.textSecondary }]}>
                Privacy policy & data
              </Text>
            </View>
            <MaterialCommunityIcons 
              name="chevron-right" 
              size={22} 
              color={theme.colors.textSecondary} 
            />
          </TouchableOpacity>

          {/* Help & Support */}
          <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />
          <TouchableOpacity 
            style={styles.settingItem}
            activeOpacity={0.7}
            onPress={() => navigation.navigate('HelpSupport')}
          >
            <View style={[styles.iconContainer, { backgroundColor: '#FEF3C7' }]}>
              <MaterialCommunityIcons 
                name="help-circle-outline" 
                size={22} 
                color="#F59E0B" 
              />
            </View>
            <View style={styles.settingContent}>
              <Text style={[styles.settingTitle, { color: theme.colors.textPrimary }]}>
                Help & Support
              </Text>
              <Text style={[styles.settingDescription, { color: theme.colors.textSecondary }]}>
                Get help or contact us
              </Text>
            </View>
            <MaterialCommunityIcons 
              name="chevron-right" 
              size={22} 
              color={theme.colors.textSecondary} 
            />
          </TouchableOpacity>

          {/* About */}
          <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />
          <TouchableOpacity 
            style={styles.settingItem}
            activeOpacity={0.7}
            onPress={() => navigation.navigate('About')}
          >
            <View style={[styles.iconContainer, { backgroundColor: '#DBEAFE' }]}>
              <MaterialCommunityIcons 
                name="information-outline" 
                size={22} 
                color="#3B82F6" 
              />
            </View>
            <View style={styles.settingContent}>
              <Text style={[styles.settingTitle, { color: theme.colors.textPrimary }]}>
                About
              </Text>
              <Text style={[styles.settingDescription, { color: theme.colors.textSecondary }]}>
                Version 1.0.0
              </Text>
            </View>
            <MaterialCommunityIcons 
              name="chevron-right" 
              size={22} 
              color={theme.colors.textSecondary} 
            />
          </TouchableOpacity>
        </View>

        {/* Logout Button */}
        <TouchableOpacity 
          style={[styles.logoutButton, { backgroundColor: theme.colors.surface }]}
          onPress={handleLogout}
          activeOpacity={0.7}
        >
          <View style={[styles.iconContainer, { backgroundColor: '#FEE2E2' }]}>
            <MaterialCommunityIcons 
              name="logout" 
              size={22} 
              color="#EF4444" 
            />
          </View>
          <Text style={styles.logoutText}>
            Logout
          </Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  headerContainer: {
    paddingHorizontal: 16,
  },
  scrollView: {
    flex: 1,
  },
  scrollContainer: {
    paddingHorizontal: 16,
    paddingBottom: 24,
  },
  syncSection: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '600',
    letterSpacing: 0.5,
    marginTop: 8,
    marginBottom: 12,
    marginLeft: 4,
  },
  sectionCard: {
    borderRadius: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
  },
  settingItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  iconContainer: {
    width: 44,
    height: 44,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  settingContent: {
    flex: 1,
  },
  settingTitle: {
    fontSize: 16,
    fontWeight: '500',
    marginBottom: 2,
  },
  settingDescription: {
    fontSize: 13,
  },
  divider: {
    height: 1,
    marginLeft: 72,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderRadius: 16,
    marginTop: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
  },
  logoutText: {
    fontSize: 16,
    fontWeight: '500',
    color: '#EF4444',
    marginLeft: 12,
  },
});

export default SettingsScreen;
