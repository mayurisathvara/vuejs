import React, { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Alert, SafeAreaView, StatusBar, ScrollView, BackHandler, Modal, ActivityIndicator } from 'react-native';
import { WebView } from 'react-native-webview';
import { useTheme } from '../../contexts/ThemeContext';
import { useOnboarding } from '../../contexts/OnboardingContext';
import { useBackHandler } from '../../hooks/useBackHandler';
import { LEGAL_URLS } from '../../config';

const ConsentScreen: React.FC = () => {
  const { theme } = useTheme();
  const { updateConsent } = useOnboarding();
  const [webViewUrl, setWebViewUrl] = useState<string | null>(null);
  const [webViewTitle, setWebViewTitle] = useState('');
  const [webViewLoading, setWebViewLoading] = useState(true);

  const openWebView = (url: string, title: string) => {
    setWebViewUrl(url);
    setWebViewTitle(title);
    setWebViewLoading(true);
  };

  // Prevent going back from consent screen
  useBackHandler({
    enabled: true,
    onBackPress: () => {
      // Show exit confirmation
      Alert.alert(
        'Exit App',
        'Are you sure you want to exit the app?',
        [
          {
            text: 'Cancel',
            style: 'cancel',
          },
          {
            text: 'Exit',
            style: 'destructive',
            onPress: () => BackHandler.exitApp(),
          },
        ]
      );
      return true; // Prevent default back behavior
    },
  });

  const handleAgree = async () => {
    try {
      await updateConsent(true);
    } catch (error) {
      Alert.alert('Error', 'Failed to save your consent. Please try again.');
    }
  };

  const handleCancel = async () => {
    Alert.alert(
      'Decline Consent',
      'Without consent, you cannot use this app. The app will close.',
      [
        { text: 'Go Back', style: 'cancel' },
        {
          text: 'Exit App',
          style: 'destructive',
          onPress: () => BackHandler.exitApp(),
        },
      ]
    );
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <ScrollView contentContainerStyle={styles.container}>
        <View style={[styles.card, { backgroundColor: theme.colors.surface }]}>
          <View style={styles.badgeRow}>
            <View style={[styles.badge, { backgroundColor: theme.colors.primary + '20', borderColor: theme.colors.primary }]}>
              <Text style={[styles.badgeText, { color: theme.colors.primary }]}>Before you continue</Text>
            </View>
          </View>

          <View style={[styles.illustration, { backgroundColor: theme.colors.background }]}>
            <Text style={styles.illustrationText}>🛡️</Text>
          </View>

          <Text style={[styles.title, { color: theme.colors.textPrimary }]}>We respect your privacy</Text>
          <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>We need your consent to collect and sync your call history to provide analytics.</Text>

          <View style={styles.bullets}>
            <View style={styles.bulletItem}>
              <Text style={styles.bulletIcon}>📊</Text>
              <Text style={[styles.bulletText, { color: theme.colors.textPrimary }]}>This app collects call logs (numbers, duration, timestamps) to show you helpful call statistics and analytics.</Text>
            </View>
            <View style={styles.bulletItem}>
              <Text style={styles.bulletIcon}>☁️</Text>
              <Text style={[styles.bulletText, { color: theme.colors.textPrimary }]}>Your call data will be synced to our servers using encrypted HTTPS connection.</Text>
            </View>
            <View style={styles.bulletItem}>
              <Text style={styles.bulletIcon}>🔒</Text>
              <Text style={[styles.bulletText, { color: theme.colors.textPrimary }]}>Your data is secured and will never be sold to third parties.</Text>
            </View>
          </View>

          <View style={styles.linksRow}>
            <TouchableOpacity
              onPress={() => openWebView(LEGAL_URLS.PRIVACY_POLICY, 'Privacy Policy')}
              activeOpacity={0.7}
            >
              <Text style={[styles.privacyLinkText, { color: theme.colors.primary }]}>
                Privacy Policy
              </Text>
            </TouchableOpacity>
            <Text style={[styles.linkSeparator, { color: theme.colors.textSecondary }]}>•</Text>
            <TouchableOpacity
              onPress={() => openWebView(LEGAL_URLS.TERMS_CONDITIONS, 'Terms & Conditions')}
              activeOpacity={0.7}
            >
              <Text style={[styles.privacyLinkText, { color: theme.colors.primary }]}>
                Terms &amp; Conditions
              </Text>
            </TouchableOpacity>
          </View>

          <View style={[styles.divider, { backgroundColor: theme.colors.divider }]} />

          <View style={styles.actions}>
            <TouchableOpacity 
              style={[
                styles.button, 
                styles.buttonSecondary, 
                { 
                  backgroundColor: 'transparent', 
                  borderColor: theme.colors.border || '#E5E7EB',
                  flex: 0.8,
                }
              ]} 
              onPress={handleCancel}
              activeOpacity={0.7}
            >
              <Text style={[styles.buttonSecondaryText, { color: theme.colors.textSecondary }]}>Cancel</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={[
                styles.button, 
                styles.buttonPrimary, 
                { 
                  backgroundColor: theme.colors.primary,
                  flex: 1.2,
                }
              ]} 
              onPress={handleAgree}
              activeOpacity={0.8}
            >
              <Text style={[styles.buttonPrimaryText, { color: theme.colors.onPrimary }]}>Agree & Continue</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>

      {/* WebView Modal for Privacy Policy / Terms */}
      <Modal
        visible={webViewUrl !== null}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setWebViewUrl(null)}
      >
        <SafeAreaView style={{ flex: 1, backgroundColor: theme.colors.background }}>
          <View style={[styles.modalHeader, { backgroundColor: theme.colors.background, borderBottomColor: theme.colors.border }]}>
            <Text style={[styles.modalHeaderText, { color: theme.colors.textPrimary }]}>{webViewTitle}</Text>
            <TouchableOpacity onPress={() => setWebViewUrl(null)} style={styles.closeButton}>
              <Text style={[styles.closeButtonText, { color: theme.colors.primary }]}>Close</Text>
            </TouchableOpacity>
          </View>
          {webViewUrl && (
            <>
              <WebView
                source={{ uri: webViewUrl }}
                onLoadStart={() => setWebViewLoading(true)}
                onLoadEnd={() => setWebViewLoading(false)}
                style={{ flex: 1 }}
              />
              {webViewLoading && (
                <View style={[styles.webViewLoading, { backgroundColor: theme.colors.background }]}>
                  <ActivityIndicator size="large" color={theme.colors.primary} />
                </View>
              )}
            </>
          )}
        </SafeAreaView>
      </Modal>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  card: {
    borderRadius: 16,
    padding: 32,
    width: '100%',
    maxWidth: 420,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 8,
  },
  badgeRow: {
    width: '100%',
    marginBottom: 16,
    alignItems: 'center',
  },
  badge: {
    borderWidth: 1,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 999,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '600',
  },
  illustration: {
    width: 80,
    height: 80,
    borderRadius: 40,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 24,
  },
  illustrationText: {
    fontSize: 32,
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    textAlign: 'center',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 15,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 20,
  },
  bullets: {
    width: '100%',
    gap: 12,
    marginBottom: 16,
  },
  bulletItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
  },
  bulletIcon: {
    fontSize: 18,
    marginTop: 2,
  },
  bulletText: {
    flex: 1,
    fontSize: 15,
    lineHeight: 22,
  },
  linksRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 8,
    marginBottom: 16,
    gap: 8,
  },
  privacyLinkText: {
    fontSize: 14,
    fontWeight: '600',
    textDecorationLine: 'underline',
  },
  linkSeparator: {
    fontSize: 14,
  },
  webViewLoading: {
    ...StyleSheet.absoluteFillObject,
    justifyContent: 'center',
    alignItems: 'center',
  },
  divider: {
    height: 1,
    width: '100%',
    marginVertical: 16,
  },
  actions: {
    flexDirection: 'row',
    width: '100%',
    gap: 16,
  },
  button: {
    flex: 1,
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 40,
  },
  buttonPrimary: {
    elevation: 0,
    shadowOpacity: 0,
  },
  buttonSecondary: {
    borderWidth: 2,
  },
  buttonPrimaryText: {
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
  },
  buttonSecondaryText: {
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
  },
  modalHeaderText: {
    fontSize: 18,
    fontWeight: '700',
  },
  closeButton: {
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  closeButtonText: {
    fontSize: 16,
    fontWeight: '600',
  },
});

export default ConsentScreen;


