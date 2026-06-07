import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { useTheme } from '../../contexts/ThemeContext';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { SettingsStackParamList } from '../../navigation/SettingsStack';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { LEGAL_URLS } from '../../constants';

type PrivacyScreenNavigationProp = StackNavigationProp<SettingsStackParamList, 'Privacy'>;

const PrivacyScreen: React.FC = () => {
  const { theme } = useTheme();
  const navigation = useNavigation<PrivacyScreenNavigationProp>();

  const privacyItems = [
    {
      icon: 'shield-lock',
      iconBg: '#F3E8FF',
      iconColor: '#A855F7',
      title: 'Data Protection',
      description: 'Your data is encrypted and secure',
    },
    {
      icon: 'database-lock',
      iconBg: '#DBEAFE',
      iconColor: '#3B82F6',
      title: 'Data Storage',
      description: 'Call logs stored locally and securely',
    },
    {
      icon: 'account-lock',
      iconBg: '#D1FAE5',
      iconColor: '#10B981',
      title: 'Privacy Controls',
      description: 'Control what data you share',
    },
  ];

  return (
    <ScrollView
      style={[styles.container, { backgroundColor: theme.colors.background }]}
      contentContainerStyle={styles.content}
    >
      <Text style={[styles.sectionTitle, { color: theme.colors.textSecondary }]}>
        PRIVACY & DATA
      </Text>

      <View style={[styles.section, { backgroundColor: theme.colors.surface }]}>
        {privacyItems.map((item, index) => (
          <React.Fragment key={item.title}>
            {index > 0 && <View style={[styles.divider, { backgroundColor: theme.colors.border }]} />}
            <View style={styles.settingItem}>
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
            </View>
          </React.Fragment>
        ))}
      </View>

      <TouchableOpacity
        style={[styles.policyButton, { backgroundColor: theme.colors.surface }]}
        onPress={() => navigation.navigate('WebView', { url: LEGAL_URLS.PRIVACY_POLICY, title: 'Privacy Policy' })}
        activeOpacity={0.7}
      >
        <View style={[styles.iconContainer, { backgroundColor: '#FEE2E2' }]}>
          <MaterialCommunityIcons name="file-document" size={20} color="#EF4444" />
        </View>
        <Text style={[styles.settingTitle, { color: theme.colors.textPrimary, flex: 1 }]}>
          Privacy Policy
        </Text>
        <MaterialCommunityIcons
          name="chevron-right"
          size={22}
          color={theme.colors.textSecondary}
        />
      </TouchableOpacity>

      <TouchableOpacity
        style={[styles.policyButton, { backgroundColor: theme.colors.surface }]}
        onPress={() => navigation.navigate('WebView', { url: LEGAL_URLS.TERMS_CONDITIONS, title: 'Terms & Conditions' })}
        activeOpacity={0.7}
      >
        <View style={[styles.iconContainer, { backgroundColor: '#DBEAFE' }]}>
          <MaterialCommunityIcons name="file-document-outline" size={20} color="#3B82F6" />
        </View>
        <Text style={[styles.settingTitle, { color: theme.colors.textPrimary, flex: 1 }]}>
          Terms &amp; Conditions
        </Text>
        <MaterialCommunityIcons
          name="chevron-right"
          size={22}
          color={theme.colors.textSecondary}
        />
      </TouchableOpacity>

      <View style={[styles.infoBox, { backgroundColor: theme.colors.surface }]}>
        <MaterialCommunityIcons name="information" size={20} color="#3B82F6" />
        <Text style={[styles.infoText, { color: theme.colors.textSecondary }]}>
          We take your privacy seriously. Your call logs are encrypted and only accessible to you. 
          We never share your personal data with third parties.
        </Text>
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
  policyButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderRadius: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
  },
  infoBox: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 16,
  },
  infoText: {
    fontSize: 13,
    lineHeight: 20,
    marginLeft: 12,
    flex: 1,
  },
});

export default PrivacyScreen;
