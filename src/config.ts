/**
 * App configuration.
 *
 * HOW TO SET PRODUCTION VALUES:
 *   1. Before building a release APK, set the environment variable:
 *        $env:API_BASE_URL = "https://api.yourcallyticsserver.com/api"
 *      then run:  npx react-native run-android --variant=release
 *
 *   2. OR: replace the PROD_API_URL constant below with the actual production URL.
 *
 * NOTE: process.env vars are inlined at build time by @react-native/babel-preset.
 * They will be undefined if not set in the shell before building.
 */

const DEV_API_URL = 'http://192.168.1.6:8000/api';
const PROD_API_URL = process.env.API_BASE_URL || 'https://api.callytics.io/api';

export const API_BASE_URL: string = __DEV__ ? DEV_API_URL : PROD_API_URL;

export const LEGAL_URLS = {
  PRIVACY_POLICY: process.env.PRIVACY_POLICY_URL || 'https://callytics.io/privacy',
  TERMS_CONDITIONS: process.env.TERMS_URL || 'https://callytics.io/terms',
} as const;
