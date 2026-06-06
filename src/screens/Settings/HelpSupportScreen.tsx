import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Linking, Alert } from 'react-native';
import { useTheme } from '../../contexts/ThemeContext';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';

const HelpSupportScreen: React.FC = () => {
  const { theme } = useTheme();

  const handleEmail = () => {
    Linking.openURL('mailto:support@yourapp.com');
  };

  const handleWebsite = () => {
    Linking.openURL('https://yourapp.com/support');
  };

  const handleFAQ = () => {
    Alert.alert('FAQ', 'FAQ section coming soon!');
  };

  const handleReportBug = () => {
    Alert.alert('Report Bug', 'Bug reporting feature coming soon!');
  };

  const supportItems = [
    {
      icon: 'email',
      iconBg: '#DBEAFE',
      iconColor: '#3B82F6',
      title: 'Email Support',
      description: 'support@yourapp.com',
      onPress: handleEmail,
    },
    {
      icon: 'web',
      iconBg: '#D1FAE5',
      iconColor: '#10B981',
      title: 'Help Center',
      description: 'Visit our help center',
      onPress: handleWebsite,
    },
    {
      icon: 'frequently-asked-questions',
      iconBg: '#FEF3C7',
      iconColor: '#F59E0B',
      title: 'FAQ',
      description: 'Frequently asked questions',
      onPress: handleFAQ,
    },
    {
      icon: 'bug',
      iconBg: '#FEE2E2',
      iconColor: '#EF4444',
      title: 'Report a Bug',
      description: 'Help us improve the app',
      onPress: handleReportBug,
    },
  ];

  return (
    <ScrollView 
      style={[styles.container, { backgroundColor: theme.colors.background }]}
      contentContainerStyle={styles.content}
    >
      <Text style={[styles.sectionTitle, { color: theme.colors.textSecondary }]}>
        CONTACT US
      </Text>

      <View style={[styles.section, { backgroundColor: theme.colors.surface }]}>
        {supportItems.map((item, index) => (
          <React.Fragment key={item.title}>
            {index > 0 && <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />}
            <TouchableOpacity 
              style={styles.settingItem}
              onPress={item.onPress}
              activeOpacity={0.7}
            >
              <View style={[styles.iconContainer, { backgroundColor: item.iconBg }]}>
                <MaterialCommunityIcons name={item.icon} size={20} color={item.iconColor} />
              </View>
              <View style={styles.settingText}>
                <Text style={[styles.settingTitle, { color: theme.colors.textPrimary }]}>
                  {item.title}
                </Text>
                <Text style={[styles.settingDescription, { color: theme.colors.textSecondary }]}>
                  {item.description}
                </Text>
              </View>
              <MaterialCommunityIcons 
                name="chevron-right" 
                size={22} 
                color={theme.colors.textSecondary} 
              />
            </TouchableOpacity>
          </React.Fragment>
        ))}
      </View>

      <View style={[styles.infoBox, { backgroundColor: theme.colors.surface }]}>
        <MaterialCommunityIcons name="clock-outline" size={20} color="#3B82F6" />
        <View style={styles.infoTextContainer}>
          <Text style={[styles.infoTitle, { color: theme.colors.textPrimary }]}>
            Support Hours
          </Text>
          <Text style={[styles.infoText, { color: theme.colors.textSecondary }]}>
            Monday - Friday: 9:00 AM - 6:00 PM{'\n'}
            Saturday - Sunday: 10:00 AM - 4:00 PM
          </Text>
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
  settingItem: {
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
  settingText: {
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
    marginLeft: 68,
  },
  infoBox: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
  },
  infoTextContainer: {
    marginLeft: 12,
    flex: 1,
  },
  infoTitle: {
    fontSize: 15,
    fontWeight: '500',
    marginBottom: 4,
  },
  infoText: {
    fontSize: 13,
    lineHeight: 20,
  },
});

export default HelpSupportScreen;
