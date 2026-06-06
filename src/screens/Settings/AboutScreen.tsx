import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Linking } from 'react-native';
import { useTheme } from '../../contexts/ThemeContext';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';

const AboutScreen: React.FC = () => {
  const { theme } = useTheme();

  const appInfo = [
    { label: 'Version', value: '1.0.0' },
    { label: 'Build Number', value: '100' },
    { label: 'Last Updated', value: 'November 23, 2025' },
  ];

  const handleRateApp = () => {
    Linking.openURL('https://play.google.com/store/apps/details?id=com.yourapp');
  };

  const handleShareApp = () => {
    // Implement share functionality
  };

  const handleTerms = () => {
    Linking.openURL('https://yourapp.com/terms');
  };

  const handleLicenses = () => {
    // Show licenses screen
  };

  return (
    <ScrollView 
      style={[styles.container, { backgroundColor: theme.colors.background }]}
      contentContainerStyle={styles.content}
    >
      {/* App Logo Section */}
      <View style={styles.logoSection}>
        <View style={[styles.logoContainer, { backgroundColor: theme.colors.surface }]}>
          <MaterialCommunityIcons name="phone-log" size={48} color="#2563EB" />
        </View>
        <Text style={[styles.appName, { color: theme.colors.textPrimary }]}>
          Callytics
        </Text>
        <Text style={[styles.appTagline, { color: theme.colors.textSecondary }]}>
          Call Log Management Made Easy
        </Text>
      </View>

      {/* App Info */}
      <Text style={[styles.sectionTitle, { color: theme.colors.textSecondary }]}>
        APP INFORMATION
      </Text>
      <View style={[styles.section, { backgroundColor: theme.colors.surface }]}>
        {appInfo.map((item, index) => (
          <React.Fragment key={item.label}>
            {index > 0 && <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />}
            <View style={styles.infoItem}>
              <Text style={[styles.infoLabel, { color: theme.colors.textSecondary }]}>
                {item.label}
              </Text>
              <Text style={[styles.infoValue, { color: theme.colors.textPrimary }]}>
                {item.value}
              </Text>
            </View>
          </React.Fragment>
        ))}
      </View>

      {/* Actions */}
      <View style={[styles.section, { backgroundColor: theme.colors.surface }]}>
        <TouchableOpacity 
          style={styles.actionItem}
          onPress={handleRateApp}
          activeOpacity={0.7}
        >
          <View style={[styles.iconContainer, { backgroundColor: '#FEF3C7' }]}>
            <MaterialCommunityIcons name="star" size={20} color="#F59E0B" />
          </View>
          <Text style={[styles.actionTitle, { color: theme.colors.textPrimary }]}>
            Rate Us
          </Text>
          <MaterialCommunityIcons 
            name="chevron-right" 
            size={22} 
            color={theme.colors.textSecondary} 
          />
        </TouchableOpacity>

        <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />

        <TouchableOpacity 
          style={styles.actionItem}
          onPress={handleShareApp}
          activeOpacity={0.7}
        >
          <View style={[styles.iconContainer, { backgroundColor: '#DBEAFE' }]}>
            <MaterialCommunityIcons name="share-variant" size={20} color="#3B82F6" />
          </View>
          <Text style={[styles.actionTitle, { color: theme.colors.textPrimary }]}>
            Share App
          </Text>
          <MaterialCommunityIcons 
            name="chevron-right" 
            size={22} 
            color={theme.colors.textSecondary} 
          />
        </TouchableOpacity>

        <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />

        <TouchableOpacity 
          style={styles.actionItem}
          onPress={handleTerms}
          activeOpacity={0.7}
        >
          <View style={[styles.iconContainer, { backgroundColor: '#F3E8FF' }]}>
            <MaterialCommunityIcons name="file-document-outline" size={20} color="#A855F7" />
          </View>
          <Text style={[styles.actionTitle, { color: theme.colors.textPrimary }]}>
            Terms of Service
          </Text>
          <MaterialCommunityIcons 
            name="chevron-right" 
            size={22} 
            color={theme.colors.textSecondary} 
          />
        </TouchableOpacity>

        <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />

        <TouchableOpacity 
          style={styles.actionItem}
          onPress={handleLicenses}
          activeOpacity={0.7}
        >
          <View style={[styles.iconContainer, { backgroundColor: '#D1FAE5' }]}>
            <MaterialCommunityIcons name="license" size={20} color="#10B981" />
          </View>
          <Text style={[styles.actionTitle, { color: theme.colors.textPrimary }]}>
            Open Source Licenses
          </Text>
          <MaterialCommunityIcons 
            name="chevron-right" 
            size={22} 
            color={theme.colors.textSecondary} 
          />
        </TouchableOpacity>
      </View>

      {/* Copyright */}
      <Text style={[styles.copyright, { color: theme.colors.textSecondary }]}>
        © 2025 Callytics. All rights reserved.
      </Text>
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
  logoSection: {
    alignItems: 'center',
    paddingVertical: 32,
  },
  logoContainer: {
    width: 80,
    height: 80,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  appName: {
    fontSize: 24,
    fontWeight: '700',
    marginBottom: 4,
  },
  appTagline: {
    fontSize: 14,
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
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  infoLabel: {
    fontSize: 16,
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 16,
  },
  actionItem: {
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
  actionTitle: {
    fontSize: 16,
    fontWeight: '500',
    flex: 1,
  },
  divider: {
    height: 1,
    marginLeft: 68,
  },
  copyright: {
    fontSize: 13,
    textAlign: 'center',
    marginTop: 16,
    marginBottom: 32,
  },
});

export default AboutScreen;
