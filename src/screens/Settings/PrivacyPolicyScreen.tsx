import React from 'react';
import { View, Text, StyleSheet, ScrollView, SafeAreaView, StatusBar } from 'react-native';
import { useTheme } from '../../contexts/ThemeContext';

const PrivacyPolicyScreen: React.FC = () => {
  const { theme } = useTheme();

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <ScrollView 
        style={styles.scrollView}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
      >
        <Text style={[styles.title, { color: theme.colors.textPrimary }]}>Privacy Policy</Text>
        <Text style={[styles.updated, { color: theme.colors.textSecondary }]}>Last Updated: November 9, 2025</Text>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Introduction</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            Callytics is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our mobile application.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Information We Collect</Text>
          
          <Text style={[styles.subTitle, { color: theme.colors.textPrimary }]}>1. Call Log Data</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            • What we collect: Call timestamps, phone numbers, call duration, call type{'\n'}
            • Why: To provide call analytics and sync your call history{'\n'}
            • How: Through Android's Call Log API with your explicit permission
          </Text>

          <Text style={[styles.subTitle, { color: theme.colors.textPrimary }]}>2. Contact Information</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            • What we collect: Contact names from your address book{'\n'}
            • Why: To display caller names in your call history{'\n'}
            • How: Only with your explicit permission
          </Text>

          <Text style={[styles.subTitle, { color: theme.colors.textPrimary }]}>3. Device Information</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            • What we collect: Device model, OS version, app version{'\n'}
            • Why: For troubleshooting and improving app performance{'\n'}
            • How: Automatically when you use the app
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>How We Use Your Information</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            • Provide app functionality and display call history{'\n'}
            • Understand call patterns and provide insights{'\n'}
            • Fix bugs and enhance user experience{'\n'}
            • Keep your call history synchronized across devices
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Data Security</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            • All data is transmitted using HTTPS encryption{'\n'}
            • Call logs are stored on secure servers{'\n'}
            • We retain data only while your account is active{'\n'}
            • You can request deletion of your data at any time
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>We DO NOT</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            ❌ Sell your call log data to third parties{'\n'}
            ❌ Share your information with advertisers{'\n'}
            ❌ Use your data for marketing purposes{'\n'}
            ❌ Access your data without your permission
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Your Rights</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            You have the right to:{'\n\n'}
            • Access: View all data we have about you{'\n'}
            • Correct: Update incorrect information{'\n'}
            • Delete: Request permanent deletion of your data{'\n'}
            • Opt-Out: Disable automatic call log syncing{'\n'}
            • Export: Download your call log data
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Permissions Explained</Text>
          
          <Text style={[styles.subTitle, { color: theme.colors.textPrimary }]}>Required Permissions</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            📞 READ_CALL_LOG: Read call history from your device{'\n'}
            📱 READ_PHONE_STATE: Detect when calls start and end{'\n\n'}
            You can revoke these permissions in Android Settings at any time.
          </Text>

          <Text style={[styles.subTitle, { color: theme.colors.textPrimary }]}>Optional Permissions</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            📇 READ_CONTACTS: Display caller names (app works without this){'\n'}
            🔔 POST_NOTIFICATIONS: Show sync status notifications
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Background Data Sync</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            Our app syncs call logs in the background every 15 minutes using Android's WorkManager API. This ensures your call history is up-to-date even when the app is closed.{'\n\n'}
            • Runs every 15 minutes automatically{'\n'}
            • Only syncs when internet is available{'\n'}
            • Shows notification after successful sync{'\n'}
            • Respects battery optimization settings
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Contact Us</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            If you have questions about this Privacy Policy:{'\n\n'}
            • Email: support@callytics.com{('\n')}
            • In-App: Settings → Help & Support → Privacy
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: theme.colors.textPrimary }]}>Your Consent</Text>
          <Text style={[styles.text, { color: theme.colors.textSecondary }]}>
            By using our app, you consent to this Privacy Policy. If you do not agree, please do not use the app.
          </Text>
        </View>

        <View style={[styles.footer, { borderTopColor: theme.colors.border }]}>
          <Text style={[styles.footerText, { color: theme.colors.textSecondary }]}>
            This Privacy Policy complies with Google Play Store policies, GDPR, and CCPA.
          </Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollView: {
    flex: 1,
  },
  content: {
    padding: 20,
    paddingBottom: 40,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    marginBottom: 8,
    textAlign: 'center',
  },
  updated: {
    fontSize: 13,
    marginBottom: 24,
    textAlign: 'center',
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 12,
  },
  subTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginTop: 12,
    marginBottom: 8,
  },
  text: {
    fontSize: 15,
    lineHeight: 24,
    marginBottom: 8,
  },
  footer: {
    marginTop: 32,
    paddingTop: 24,
    borderTopWidth: 1,
  },
  footerText: {
    fontSize: 13,
    textAlign: 'center',
    lineHeight: 20,
  },
});

export default PrivacyPolicyScreen;
