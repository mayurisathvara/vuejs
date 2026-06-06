import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
} from 'react-native';
import { WebView } from 'react-native-webview';
import { useRoute, RouteProp } from '@react-navigation/native';
import { useTheme } from '../contexts/ThemeContext';
import { SettingsStackParamList } from '../navigation/SettingsStack';

type WebViewScreenRouteProp = RouteProp<SettingsStackParamList, 'WebView'>;

const WebViewScreen: React.FC = () => {
  const { theme } = useTheme();
  const route = useRoute<WebViewScreenRouteProp>();
  const { url } = route.params;
  const [loading, setLoading] = useState(true);
  const [hasError, setHasError] = useState(false);
  const [loadKey, setLoadKey] = useState(0);

  const handleRetry = () => {
    setHasError(false);
    setLoading(true);
    setLoadKey(k => k + 1);
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.colors.background }]}>
      <StatusBar
        barStyle={theme.colorScheme === 'dark' ? 'light-content' : 'dark-content'}
        backgroundColor={theme.colors.background}
      />

      {hasError ? (
        <View style={styles.errorContainer}>
          <Text style={[styles.errorTitle, { color: theme.colors.textPrimary }]}>
            Failed to load page
          </Text>
          <Text style={[styles.errorMessage, { color: theme.colors.textSecondary }]}>
            Please check your internet connection and try again.
          </Text>
          <TouchableOpacity
            style={[styles.retryButton, { backgroundColor: theme.colors.primary }]}
            onPress={handleRetry}
            activeOpacity={0.8}
          >
            <Text style={[styles.retryText, { color: theme.colors.onPrimary }]}>Retry</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <>
          <WebView
            key={loadKey}
            source={{ uri: url }}
            onLoadStart={() => setLoading(true)}
            onLoadEnd={() => setLoading(false)}
            onError={() => {
              setLoading(false);
              setHasError(true);
            }}
            style={styles.webview}
          />
          {loading && (
            <View style={[styles.loadingOverlay, { backgroundColor: theme.colors.background }]}>
              <ActivityIndicator size="large" color={theme.colors.primary} />
            </View>
          )}
        </>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  webview: {
    flex: 1,
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    justifyContent: 'center',
    alignItems: 'center',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
  },
  errorTitle: {
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorMessage: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  retryButton: {
    paddingVertical: 12,
    paddingHorizontal: 32,
    borderRadius: 20,
  },
  retryText: {
    fontSize: 15,
    fontWeight: '600',
  },
});

export default WebViewScreen;
