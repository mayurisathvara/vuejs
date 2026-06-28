import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  StatusBar,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  ActivityIndicator,
  Alert,
  BackHandler,
  Image,
} from 'react-native';
import { useAuth } from '../contexts/AuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { useBackHandler } from '../hooks/useBackHandler';
import { isValidAppLoginCode } from '../utils/date';
import { PHONE_FORMAT } from '../constants';

const LoginScreen: React.FC = () => {
  const [mobile, setMobile] = useState('');
  const [appLoginCode, setAppLoginCode] = useState('');
  const { login, isLoading } = useAuth();
  const { theme } = useTheme();

  // Prevent going back from login screen
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

  const validateInputs = (): boolean => {
    if (!mobile.trim()) {
      Alert.alert('Validation Error', 'Please enter your mobile number');
      return false;
    }

    if (mobile.length < PHONE_FORMAT.MAX_DIGITS) {
      Alert.alert('Validation Error', 'Please enter a valid mobile number');
      return false;
    }

    if (!appLoginCode.trim()) {
      Alert.alert('Validation Error', 'Please enter your app login code');
      return false;
    }

    if (!isValidAppLoginCode(appLoginCode)) {
      Alert.alert('Validation Error', 'Invalid app login code format. Expected format: ORG-XXXX-XXXX');
      return false;
    }

    return true;
  };

  const handleLogin = async () => {
    if (!validateInputs()) return;

    await login({
      mobile: mobile.trim(),
      app_login_code: appLoginCode.trim(),
    });

    // Navigation is handled automatically by auth state change in AppNavigator
  };

  const formatMobileNumber = (text: string) => {
    const cleaned = text.replace(/\D/g, '');
    return cleaned.slice(0, PHONE_FORMAT.MAX_DIGITS);
  };

  const formatAppLoginCode = (text: string, prevValue: string) => {
    const cleaned = text.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 11);
    const prevCleaned = prevValue.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
    // Compare cleaned lengths so autocomplete jumps (e.g. '' → 'DOS-1234-5678') are never
    // misread as deletion. Fall back to raw length only when cleaned lengths are equal,
    // which is the specific case of the user backspacing an auto-inserted dash.
    const isDeleting =
      cleaned.length < prevCleaned.length ||
      (cleaned.length === prevCleaned.length && text.length < prevValue.length);

    if (cleaned.length <= 3) {
      return cleaned.length === 3 && !isDeleting ? `${cleaned}-` : cleaned;
    }
    if (cleaned.length <= 7) {
      const part = `${cleaned.slice(0, 3)}-${cleaned.slice(3)}`;
      return cleaned.length === 7 && !isDeleting ? `${part}-` : part;
    }
    return `${cleaned.slice(0, 3)}-${cleaned.slice(3, 7)}-${cleaned.slice(7)}`;
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar 
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'} 
        backgroundColor={theme.colors.background} 
      />
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        <ScrollView
          contentContainerStyle={styles.scrollContainer}
          keyboardShouldPersistTaps="handled"
        >
          <View style={styles.header}>
            <Image 
              source={require('../assets/callytics_logo.png')}
              style={styles.logo}
              resizeMode="contain"
            />
            <Text style={[styles.title, { color: theme.colors.textPrimary }]}>Callytics</Text>
            <Text style={[styles.subtitle, { color: theme.colors.textSecondary }]}>Sign in to your account</Text>
          </View>

          <View style={styles.form}>
            <View style={styles.inputContainer}>
              <Text style={[styles.label, { color: theme.colors.textPrimary }]}>Mobile Number</Text>
              <View style={[
                styles.inputWrapper, 
                { 
                  borderColor: theme.colors.border,
                  backgroundColor: theme.colors.surface 
                }
              ]}>
                <Text style={[styles.countryCode, { color: theme.colors.textPrimary }]}>{PHONE_FORMAT.COUNTRY_CODE}</Text>
                <TextInput
                  style={[styles.input, { color: theme.colors.textPrimary }]}
                  value={mobile}
                  onChangeText={(text) => setMobile(formatMobileNumber(text))}
                  placeholder="Enter mobile number"
                  placeholderTextColor={theme.colors.textSecondary}
                  keyboardType="numeric"
                  maxLength={PHONE_FORMAT.MAX_DIGITS}
                  editable={!isLoading}
                />
              </View>
            </View>

            <View style={styles.inputContainer}>
              <Text style={[styles.label, { color: theme.colors.textPrimary }]}>App Login Code</Text>
              <View style={[
                styles.inputWrapper, 
                { 
                  borderColor: theme.colors.border,
                  backgroundColor: theme.colors.surface 
                }
              ]}>
                <TextInput
                  style={[styles.input, { color: theme.colors.textPrimary }]}
                  value={appLoginCode}
                  onChangeText={(text) => setAppLoginCode(formatAppLoginCode(text, appLoginCode))}
                  placeholder="e.g., DOS-1234-5678"
                  placeholderTextColor={theme.colors.textSecondary}
                  autoCapitalize="characters"
                  autoCorrect={false}
                  maxLength={13}
                  editable={!isLoading}
                />
              </View>
            </View>

            <TouchableOpacity
              style={[
                styles.loginButton, 
                { backgroundColor: theme.colors.primary },
                isLoading && { backgroundColor: theme.colors.disabled }
              ]}
              onPress={handleLogin}
              disabled={isLoading}
            >
              {isLoading ? (
                <ActivityIndicator color={theme.colors.onPrimary} size="small" />
              ) : (
                <Text style={[styles.loginButtonText, { color: theme.colors.onPrimary }]}>Sign In</Text>
              )}
            </TouchableOpacity>
          </View>

          <View style={styles.footer}>
            <Text style={[styles.footerText, { color: theme.colors.textSecondary }]}>
              By signing in, you agree to our Terms of Service and Privacy Policy
            </Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  keyboardView: {
    flex: 1,
  },
  scrollContainer: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 32,
  },
  header: {
    alignItems: 'center',
    marginBottom: 48,
  },
  logo: {
    width: 50,
    height: 50,
    marginBottom: 16,
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    textAlign: 'center',
  },
  form: {
    marginBottom: 32,
  },
  inputContainer: {
    marginBottom: 24,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 8,
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1.5,
    borderRadius: 12,
    paddingHorizontal: 16,
    height: 56,
  },
  countryCode: {
    fontSize: 16,
    fontWeight: '500',
    marginRight: 8,
    paddingRight: 8,
    borderRightWidth: 1,
  },
  input: {
    flex: 1,
    fontSize: 16,
    paddingVertical: 0,
  },
  loginButton: {
    borderRadius: 12,
    height: 56,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 8,
    elevation: 2,
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
  },
  loginButtonText: {
    fontSize: 16,
    fontWeight: '600',
  },
  footer: {
    alignItems: 'center',
    paddingTop: 24,
  },
  footerText: {
    fontSize: 12,
    textAlign: 'center',
    lineHeight: 18,
  },
});

export default LoginScreen;
