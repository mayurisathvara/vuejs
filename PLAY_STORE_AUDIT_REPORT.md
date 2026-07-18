# Callytics — Play Store & Production Audit Report

Date: 2026-07-06
Scope: Android app source review, build validation, manifest and policy audit, security posture review, release-readiness assessment.

## Summary

The app is not yet fully ready for Google Play submission. The core feature architecture is present and the onboarding/privacy flow is fairly solid, but there are several release blockers and compliance gaps that should be addressed before publishing.

### Current status
- Google Play readiness score: 56/100
- Production readiness score: 58/100
- Security score: 62/100
- Performance score: 72/100
- UI/UX score: 78/100

> Note: the current development API URL is still a local IP in [src/config.ts](src/config.ts). As requested, this is being kept for now; it must be replaced with the production domain before go-live.

## Verified findings

### 1) Critical — Release signing is not production-safe by default
- Category: Build & Release / Play Compliance
- Description: The release build falls back to the debug keystore if [android/app/keystore.properties](android/app/keystore.properties) is missing.
- Root cause: The signing config in [android/app/build.gradle](android/app/build.gradle) uses fallback credentials rather than forcing a production keystore.
- Affected files: [android/app/build.gradle](android/app/build.gradle)
- Exact location: Android signing block in the release config
- Recommended fix: Require a production keystore and fail the build if it is absent.
- Code snippet:
```gradle
release {
    signingConfig signingConfigs.release
    minifyEnabled enableProguardInReleaseBuilds
}
```
- Blocks Play approval: Yes

### 2) High — Sensitive app data is stored in plain local storage
- Category: Security / Privacy
- Description: Authentication tokens, mobile numbers, user profile data, and queued call-log data are stored with AsyncStorage, which is not encrypted.
- Root cause: Local persistence is implemented through [src/services/storage.ts](src/services/storage.ts) and [src/services/callLogStorage.ts](src/services/callLogStorage.ts) without secure storage or encryption.
- Affected files: [src/services/storage.ts](src/services/storage.ts), [src/services/callLogStorage.ts](src/services/callLogStorage.ts)
- Exact location: Storage helpers that write auth/session/call-log data
- Recommended fix: Move sensitive data to encrypted storage such as KeyStore-backed secure storage or a library designed for secure credentials.
- Code snippet:
```ts
await AsyncStorage.multiSet([
  [STORAGE_KEYS.AUTH_TOKEN, token],
  [STORAGE_KEYS.USER_MOBILE, mobile],
  [STORAGE_KEYS.USER_DATA, JSON.stringify(userData)],
]);
```
- Blocks Play approval: Potentially, depending on the sensitivity of the data and the policy review posture.

### 3) High — Background sync and call-state polling can be battery-heavy
- Category: Android Quality / Performance
- Description: The app polls every 3 seconds for call-state changes and also runs periodic background sync.
- Root cause: [src/services/callStateListener.ts](src/services/callStateListener.ts) uses a repeated interval loop and [App.tsx](App.tsx) configures background fetch aggressively.
- Affected files: [src/services/callStateListener.ts](src/services/callStateListener.ts), [App.tsx](App.tsx)
- Exact location: The interval-based listener and the BackgroundFetch configuration block
- Recommended fix: Reduce polling frequency, gate it behind actual call-state events where possible, and ensure the background service is limited to necessary windows.
- Code snippet:
```ts
private readonly checkInterval = 3000;
this.subscription = setInterval(async () => {
  // poll every 3 seconds
}, this.checkInterval);
```
- Blocks Play approval: Not directly, but it can create user-facing complaints and policy friction.

### 4) High — The app uses a foreground service for sync and should be carefully validated against policy expectations
- Category: Play Compliance / Background work
- Description: The manifest declares a foreground service and the Android service is started for sync work.
- Root cause: The app needs background sync, but foreground service usage must be justified, minimal, and clearly scoped.
- Affected files: [android/app/src/main/AndroidManifest.xml](android/app/src/main/AndroidManifest.xml), [android/app/src/main/java/io/callytics/app/CallLogSyncService.kt](android/app/src/main/java/io/callytics/app/CallLogSyncService.kt)
- Exact location: Foreground service declaration and service implementation
- Recommended fix: Keep the service minimal, ensure it is only used for necessary sync tasks, and document the behavior clearly.
- Code snippet:
```xml
<service android:name=".CallLogSyncService" android:exported="false" android:foregroundServiceType="dataSync" />
```
- Blocks Play approval: Potentially, if the service behavior is seen as excessive or intrusive.

### 5) Medium — TypeScript build is currently failing due to a missing Jest type definition
- Category: Build & Quality
- Description: Type-checking fails with TS2688 because the project references the Jest type library but it is not installed.
- Root cause: The TypeScript configuration in [tsconfig.json](tsconfig.json) expects Jest types but [package.json](package.json) does not include the matching package.
- Affected files: [tsconfig.json](tsconfig.json), [package.json](package.json)
- Exact location: Compiler options and package deps
- Recommended fix: Install the missing type package or remove the Jest type reference if tests are not used.
- Code snippet:
```json
"types": ["jest"]
```
- Blocks Play approval: No, but it blocks release automation and confidence.

### 6) Medium — The privacy policy and legal links are present, but Play Console declarations still need verification
- Category: Play Compliance / Privacy
- Description: The app has privacy/terms links and a consent screen, which is positive, but the final Play Store data-safety and privacy disclosures must be verified against the actual permission and data-collection behavior.
- Root cause: The UI and docs exist, but the compliance declarations and legal URLs must be validated end to end before submission.
- Affected files: [src/screens/Onboarding/ConsentScreen.tsx](src/screens/Onboarding/ConsentScreen.tsx), [src/config.ts](src/config.ts), [PRIVACY_POLICY.md](PRIVACY_POLICY.md)
- Exact location: Legal URL wiring and consent flow
- Recommended fix: Validate the final URLs, make sure the policy text matches the code paths, and complete Play Console data-safety sections.
- Code snippet:
```ts
LEGAL_URLS.PRIVACY_POLICY
LEGAL_URLS.TERMS_CONDITIONS
```
- Blocks Play approval: Yes, if the final Play Console declarations are incomplete.

### 7) Medium — The app still uses the archived push notification library
- Category: Android Quality / Maintenance
- Description: [package.json](package.json) uses a library that is no longer actively maintained.
- Root cause: The app depends on react-native-push-notification, which is archived and may become a future compatibility risk.
- Affected files: [package.json](package.json), [src/services/notificationService.ts](src/services/notificationService.ts)
- Exact location: Notification service implementation and dependency declaration
- Recommended fix: Migrate to a maintained library such as Notifee before the next major Android release cycle.
- Code snippet:
```json
"react-native-push-notification": "^8.1.1"
```
- Blocks Play approval: No, but it is a maintenance risk.

### 8) Low — WebView is loaded without explicit hardening settings
- Category: Security
- Description: The app uses WebView to display privacy/terms content, but it is not explicitly hardened for production.
- Root cause: [src/screens/WebViewScreen.tsx](src/screens/WebViewScreen.tsx) and [src/screens/Onboarding/ConsentScreen.tsx](src/screens/Onboarding/ConsentScreen.tsx) load remote URLs without additional security hardening such as JS restrictions and clear origin handling.
- Affected files: [src/screens/WebViewScreen.tsx](src/screens/WebViewScreen.tsx), [src/screens/Onboarding/ConsentScreen.tsx](src/screens/Onboarding/ConsentScreen.tsx)
- Exact location: WebView component usage
- Recommended fix: Disable unnecessary JS, restrict file access, and ensure only approved legal URLs are opened.
- Code snippet:
```tsx
<WebView source={{ uri: url }} />
```
- Blocks Play approval: No.

## Prioritized bug list

1. Configure a real production keystore and enforce signing for release builds. (Critical)
2. Replace plain local storage for auth/session/call-log data with secure storage. (High)
3. Review and reduce the background polling/sync behavior to avoid battery and policy concerns. (High)
4. Validate the foreground service behavior and ensure it remains minimal and policy-compliant. (High)
5. Fix the TypeScript/Jest typing issue so release automation is reliable. (Medium)
6. Finalize Play Console privacy/data-safety declarations and confirm that the in-app disclosures match the app behavior. (Medium)

## Remaining tasks before release

- Replace the local development API IP with the production domain.
- Configure production signing and upload the keystore to the release pipeline.
- Complete Play Console data safety, app content, privacy policy, and permission declarations.
- Harden local token/session storage.
- Review battery and background work behavior.
- Run a full release build and install test on a physical device/emulator.
- Validate the real login, logout, onboarding, permission flow, and sync path on Android 15.

## Notes on the local API domain

The current local IP in [src/config.ts](src/config.ts) is acceptable for local development only. For production, it should be replaced with the final HTTPS domain before building and publishing the app.
