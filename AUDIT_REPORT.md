# Callytics — Code Audit Report
**Date:** 2026-06-28  
**Audited by:** Claude Code (Lead RN Architect / Senior Android Engineer)  
**Stack:** React Native 0.80.1 · React 19 · TypeScript 5.0.4 · Hermes · New Architecture (Fabric)  
**Package:** `io.callytics.app`  
**Dev API:** `http://192.168.1.6:8000/api` — update to HTTPS before go-live  

---

## Overall Health Score: 8.0 / 10

The codebase is in solid production shape after the June 2026 refactor. Core features are clean, the offline auth fix is correct, background sync is working, and TypeScript is strict. The remaining issues are non-blocking but should be addressed before Play Store submission.

---

## 1. Architecture Overview

```
App.tsx (ErrorBoundary → GestureHandlerRoot → Providers → AppNavigator)
│
├── Providers (ThemeContext, NetworkContext, AuthContext, OnboardingContext, CallLogContext)
├── Navigation: SplashScreen → Login → Onboarding Flow → MainTabs
│                                                              ├── HomeScreen (dashboard)
│                                                              ├── CallLogsScreen (paginated)
│                                                              ├── AnalyticsScreen (charts)
│                                                              └── SettingsScreen
│
├── Services Layer
│   ├── api.ts (axios, auth interceptor, retry-with-backoff)
│   ├── callLogSync.ts (read device logs → queue → upload)
│   ├── callLogStorage.ts (AsyncStorage FIFO queue + synced-IDs cap)
│   ├── callStateListener.ts (setInterval 3s poll → processNewCallLogs)
│   ├── notificationService.ts (push notification configure + show)
│   └── storage.ts (auth + consent + generic AsyncStorage wrappers)
│
├── Background Sync Pipeline
│   ├── react-native-background-fetch (15 min, WorkManager on Android)
│   │   ├── in-app: App.tsx → callLogSyncService
│   │   └── headless: src/workers/backgroundFetch.js → callLogSyncService
│   └── WorkManager trigger via DeviceEventEmitter('CallLogSyncTrigger')
│       └── android/.../CallLogSyncService.kt (HeadlessJsTaskService → callLogSync.js)
│
└── Config
    ├── src/config.ts (API_BASE_URL gated by __DEV__, LEGAL_URLS)
    └── src/constants/index.ts (sync intervals, retry config, limits)
```

---

## 2. Findings by Severity

### CRITICAL — Must fix before Play Store submission

#### C1 — Dev IP hardcoded as current API URL
**File:** [src/config.ts:15](src/config.ts)  
**Issue:** `DEV_API_URL = 'http://192.168.1.6:8000/api'` is the active URL for all `__DEV__` builds. Release builds use `PROD_API_URL` from `process.env.API_BASE_URL || 'https://api.callytics.io/api'`. Before building a release APK, confirm the production HTTPS URL is correct and set.  
**Action:** Before go-live, set the real prod URL in `PROD_API_URL` or set `$env:API_BASE_URL` in the build shell.

#### C2 — `tsconfig.json` excludes a file that does not exist
**File:** [tsconfig.json:22](tsconfig.json)  
**Issue:** `"exclude": ["src/screens/HomeScreen.old.tsx"]` — this file does not exist on disk. The entry is a dangling reference. TypeScript silently ignores it but it is confusing and suggests the old file was excluded rather than deleted.  
**Action:** Remove the exclusion entry from `tsconfig.json`.

---

### HIGH — Fix before release, may cause subtle bugs or rejects

#### H1 — `dynamic require()` inside function bodies in AuthContext
**File:** [src/contexts/AuthContext.tsx:167](src/contexts/AuthContext.tsx), [line 217](src/contexts/AuthContext.tsx)  
**Issue:** `const { callLogStorage } = require('../services/callLogStorage')` is called inside `logout()` and inside the token-expired handler. Dynamic `require()` skips the module graph; if Metro fails to bundle the module statically it will fail at runtime in production. Static imports at the top of the file are safe and correct.
```typescript
// Current (risky)
const { callLogStorage } = require('../services/callLogStorage');

// Fix — add at top of file alongside other imports
import { callLogStorage } from '../services/callLogStorage';
```
**Action:** Move both to static top-level imports.

#### H2 — `error: any` still used in critical service paths
**Files:** Multiple  
TypeScript strict mode is enabled (`noImplicitAny: true`) but `catch (error: any)` is used in:

| File | Lines |
|------|-------|
| [src/services/api.ts](src/services/api.ts) | 18, 121, 160, 204, 233, 263, 294 |
| [src/contexts/AuthContext.tsx](src/contexts/AuthContext.tsx) | 56, 145 |
| [src/services/callLogSync.ts](src/services/callLogSync.ts) | 365, 494, 564, 591 |
| [src/screens/HomeScreen.tsx](src/screens/HomeScreen.tsx) | 95 |
| [src/screens/CallLogsScreen.tsx](src/screens/CallLogsScreen.tsx) | 93 |
| [src/screens/AnalyticsScreen.tsx](src/screens/AnalyticsScreen.tsx) | 44, 71, 98 |

**Fix:** Replace `catch (err: any)` with `catch (err: unknown)` and access properties safely via `err instanceof Error ? err.message : String(err)`.

#### H3 — `storage.ts` userData typed as `any`
**File:** [src/services/storage.ts:12](src/services/storage.ts)  
**Issue:** `storeAuthData(token, mobile, userData: any)` and `getUserData(): Promise<any>` lose all type safety. The `User` type is already defined in `src/types/index.ts`.
```typescript
// Fix
getUserData: async (): Promise<User | null>
storeAuthData: async (token: string, mobile: string, userData: User): Promise<void>
```

#### H4 — `AnalyticsScreen.tsx` state typed as `any`
**File:** [src/screens/AnalyticsScreen.tsx:17-19](src/screens/AnalyticsScreen.tsx)  
```typescript
const [dailyVolumeData, setDailyVolumeData] = useState<any>(null);
const [peakHoursData, setPeakHoursData] = useState<any>(null);
const [missedCallsData, setMissedCallsData] = useState<any>(null);
```
**Action:** Define interfaces matching each API response shape and type these state variables properly.

---

### MEDIUM — Code quality, maintainability, architecture

#### M1 — Service layer (`callLogSync.ts`) imports UI component (`Toast`)
**File:** [src/services/callLogSync.ts:4](src/services/callLogSync.ts)  
**Issue:** `import Toast from 'react-native-toast-message'` inside a service. Services should not know about UI. The `headlessMode` boolean partially mitigates this (Toast is skipped in headless mode), but the coupling remains.  
**Action:** Remove Toast from `callLogSync.ts`. Callers (CallLogContext, App.tsx) already show toasts based on sync results.

#### M2 — `showSyncNotification` in `callLogSync.ts` duplicates permission check
**File:** [src/services/callLogSync.ts:638-646](src/services/callLogSync.ts)  
**Issue:** `showSyncNotification()` has its own inline `PermissionsAndroid.check(POST_NOTIFICATIONS)` — identical to `notificationPermission.js` which was created specifically to avoid this duplication.  
**Action:** Import and use `hasNotificationPermission` from `src/workers/notificationPermission.js`.

#### M3 — `callStateListener.ts` has its own `hasNotificationPermission()` copy
**File:** [src/services/callStateListener.ts:14-25](src/services/callStateListener.ts)  
**Issue:** Same as M2 — private method duplicating the shared helper.  
**Action:** Import from `notificationPermission.js`.

#### M4 — `useEffect` missing dependencies in `HomeScreen` and `AnalyticsScreen`
**Files:** [src/screens/HomeScreen.tsx:130](src/screens/HomeScreen.tsx), [src/screens/AnalyticsScreen.tsx:142](src/screens/AnalyticsScreen.tsx)  
**Issue:** Both screens call a function inside `useEffect([])` where the function is defined outside `useCallback`, creating a stale closure. React exhaustive-deps lint rule flags both.  
**Action:** Wrap `fetchDashboard` / `fetchAllAnalytics` in `useCallback` with proper deps, then add to the `useEffect` dependency array.

#### M5 — `navigate()` helper in AppNavigator typed as `params?: any`
**File:** [src/navigation/AppNavigator.tsx:32](src/navigation/AppNavigator.tsx)  
```typescript
// Current
export function navigate(name: keyof RootStackParamList, params?: any)

// Fix
export function navigate<T extends keyof RootStackParamList>(
  name: T,
  params?: RootStackParamList[T]
)
```

#### M6 — `createComponentStyles(colors: any)` in `theme/components.ts`
**File:** [src/theme/components.ts:5](src/theme/components.ts)  
**Action:** Define a `ThemeColors` interface (or extract from ThemeContext) and replace `any`.

#### M7 — `CallLogPushResponse.data?: any` in types
**File:** [src/types/index.ts:115](src/types/index.ts)  
**Action:** Define the actual response shape or use `Record<string, unknown>`.

#### M8 — Inline IIFE for Sync Status card in HomeScreen
**File:** [src/screens/HomeScreen.tsx:432-465](src/screens/HomeScreen.tsx)  
**Issue:** `{(() => { ... })()}` is hard to read and recreated on every render.  
**Action:** Extract to a named `SyncStatusCard` component or a simple local `renderSyncCard()` function.

#### M9 — `HomeScreen.tsx` has dead style `statUpdate`
**File:** [src/screens/HomeScreen.tsx:607-609](src/screens/HomeScreen.tsx)  
```typescript
statUpdate: {
  fontSize: 11,
  color: '#999',
},
```
Defined but never referenced in JSX.  
**Action:** Delete it.

#### M10 — `package.json` version is `"0.0.1"`
**File:** `package.json`  
**Issue:** Will appear stale. Actual release versioning is controlled by `android/version.properties` which is correct — just ensure consistency.

---

### LOW — Minor cleanliness issues

#### L1 — Notification title in `callLogSync.ts` uses emoji
**File:** [src/services/callLogSync.ts:651](src/services/callLogSync.ts)  
`title: '✅ Call Logs Synced'` — emoji in notification titles can render inconsistently across Android versions and OEMs.  
**Action:** Remove emoji from notification strings.

#### L2 — `EventEmitter` callbacks typed as `any[]`
**File:** [src/utils/eventEmitter.ts:6](src/utils/eventEmitter.ts)  
Minor — could use generics for type-safe event emission but current 2-event scope makes this low priority.

#### L3 — `App.tsx` Toast config has 100+ lines of inline styles
**File:** [App.tsx:104-198](App.tsx)  
**Action:** Extract to `src/components/ToastConfig.tsx` to keep App.tsx readable.

#### L4 — Background sync notifications play sound on every call
**Files:** [src/services/callStateListener.ts:71](src/services/callStateListener.ts), [src/workers/backgroundFetch.js:46](src/workers/backgroundFetch.js)  
`playSound: true` on sync success notifications may irritate users if calls are frequent.  
**Action:** Consider `playSound: false` for routine sync confirmations.

#### L5 — `android/app/build.gradle` — WorkManager and Coroutines dependencies worth verifying
**File:** [android/app/build.gradle:156-157](android/app/build.gradle)  
`work-runtime-ktx` and `kotlinx-coroutines-android` — `CallLogSyncService.kt` doesn't directly use either. `react-native-background-fetch` uses WorkManager internally so `work-runtime-ktx` may be a required transitive dep. Verify with `./gradlew :app:dependencies` before removing.

---

## 3. What is Working Well

| Area | Status | Notes |
|------|--------|-------|
| Offline auth | Correct | Only 401/403 clears auth; network errors keep user logged in |
| ErrorBoundary | Present | Wraps entire app; shows recovery UI with dev stack trace |
| Sync concurrency | Guarded | `isSyncing` + `isProcessingLogs` flags prevent parallel runs |
| Queue deduplication | Robust | Checks synced-IDs set + existing unsynced queue before adding |
| Queue cleanup | Implemented | Removes logs older than 7 days or with >10 failed attempts |
| Retry with backoff | Correct | Exponential delay; skips 400/401/403/422 |
| Headless mode | Correct | `processNewCallLogs(true)` skips permission requests in background |
| Background sync | Dual path | BackgroundFetch (15 min) + WorkManager trigger (CallLogSyncService) |
| FlatList optimization | Applied | `removeClippedSubviews`, `maxToRenderPerBatch=15`, `windowSize=10` |
| Event-driven stats | Applied | No timer polling; stats update only after sync operations |
| API URL config | Safe | `__DEV__` gates dev IP vs prod HTTPS; single source of truth |
| LEGAL_URLS | Fixed | Both links resolved from `config.ts` with HTTPS fallbacks |
| Versioning | Correct | `versionCode`/`versionName` read from `android/version.properties` |
| ProGuard | Enabled | `enableProguardInReleaseBuilds = true` |
| Signing config | Correct | Falls back to debug keystore if `keystore.properties` absent |
| Token expiry | Graceful | Event-driven via `appEvents` + `isLoggingOut` guard prevents double-logout |
| Notification permission | Handled | Android 13+ `POST_NOTIFICATIONS` checked before any notification |
| `caller_duration` formats | Fixed | Handles both `HH:MM:SS` from list API and raw seconds from push |
| Autocomplete login code | Fixed | `isDeleting` based on cleaned-length comparison |
| Dead code | Cleaned | 13 dead files removed; 2 unused packages removed |

---

## 4. Current File Inventory

```
src/
├── config.ts                          API_BASE_URL, LEGAL_URLS
├── constants/index.ts                 Sync intervals, retry config, limits
├── types/index.ts                     All shared TypeScript types
│
├── assets/
│   ├── animations/splash.json
│   └── callytics_logo.png
│
├── components/
│   ├── analytics/
│   │   ├── DailyCallVolume.js
│   │   ├── MissedCallsChart.tsx
│   │   └── PeakCallHours.js
│   ├── AppHeader.tsx
│   ├── CallLogSyncButton.tsx
│   ├── DateRangeFilter.tsx
│   ├── ErrorBoundary.tsx
│   ├── FilterBar.js
│   ├── NetworkErrorPage.tsx
│   ├── NetworkWrapper.tsx
│   └── PermissionScreen.tsx
│
├── contexts/
│   ├── AuthContext.tsx
│   ├── CallLogContext.tsx
│   ├── NetworkContext.tsx
│   ├── OnboardingContext.tsx
│   └── ThemeContext.tsx
│
├── hooks/
│   ├── useBackHandler.ts
│   └── usePermissions.ts
│
├── navigation/
│   ├── AppNavigator.tsx
│   ├── MainTabs.tsx
│   └── SettingsStack.tsx
│
├── screens/
│   ├── AnalyticsScreen.tsx
│   ├── CallLogsScreen.tsx
│   ├── HomeScreen.tsx
│   ├── LoginScreen.tsx
│   ├── SettingsScreen.tsx
│   ├── SplashScreen.tsx
│   ├── WebViewScreen.tsx
│   ├── Onboarding/
│   │   ├── CallLogPermission.tsx
│   │   ├── ConsentScreen.tsx
│   │   ├── ContactsPermission.tsx
│   │   ├── NotificationPermission.tsx
│   │   └── PhoneStatePermission.tsx
│   └── Settings/
│       ├── AboutScreen.tsx
│       ├── HelpSupportScreen.tsx
│       ├── PrivacyPolicyScreen.tsx
│       ├── PrivacyScreen.tsx
│       └── ProfileScreen.tsx
│
├── services/
│   ├── api.ts                         Axios, interceptors, all API calls
│   ├── callLogStorage.ts              AsyncStorage queue (FIFO, 2000-ID cap)
│   ├── callLogSync.ts                 Core sync service
│   ├── callStateListener.ts           3s poll for new calls
│   ├── notificationService.ts         Push notification configure/show
│   └── storage.ts                     Auth + consent storage wrappers
│
├── theme/
│   ├── colors.ts
│   ├── components.ts
│   ├── index.ts
│   ├── spacing.ts
│   └── typography.ts
│
├── types/
│   ├── index.ts
│   ├── react-native-call-log.d.ts
│   └── react-native-push-notification.d.ts
│
├── utils/
│   ├── date.ts
│   ├── errorHandler.ts
│   ├── eventEmitter.ts
│   └── uniqueIdGenerator.ts
│
└── workers/
    ├── backgroundFetch.js             BackgroundFetch headless task
    ├── callLogSync.js                 HeadlessJS task (WorkManager path)
    └── notificationPermission.js      Shared permission helper

android/
├── app/build.gradle
├── version.properties
└── app/src/main/
    ├── AndroidManifest.xml
    └── java/io/callytics/app/
        ├── MainActivity.kt
        ├── MainApplication.kt
        └── CallLogSyncService.kt      HeadlessJsTaskService bridge
```

---

## 5. Dependencies

### Production (23 packages)
| Package | Version | Purpose |
|---------|---------|---------|
| `react` | 19.1.0 | Core |
| `react-native` | 0.80.1 | Core |
| `@react-native-async-storage/async-storage` | ^2.2.0 | Persistent queue + auth storage |
| `@react-native-community/netinfo` | ^11.4.1 | Network state detection |
| `@react-navigation/native` | ^7.1.17 | Navigation |
| `@react-navigation/stack` | ^7.4.8 | Stack navigator |
| `axios` | ^1.12.2 | HTTP client |
| `lottie-react-native` | ^7.3.4 | Splash animation |
| `react-native-background-fetch` | ^4.2.8 | 15-min background sync |
| `react-native-call-log` | ^2.1.2 | Read device call log |
| `react-native-chart-kit` | ^6.12.0 | Analytics charts |
| `react-native-date-picker` | ^5.0.13 | Date range filter |
| `react-native-gesture-handler` | ^2.28.0 | Gesture support |
| `react-native-linear-gradient` | ^2.8.3 | Dashboard hero card |
| `react-native-paper` | ^5.14.5 | UI components |
| `react-native-permissions` | ^5.4.2 | Permission handling |
| `react-native-push-notification` | ^8.1.1 | Local notifications (archived — see below) |
| `react-native-safe-area-context` | ^5.6.1 | Safe area insets |
| `react-native-screens` | ^4.16.0 | Native screen management |
| `react-native-svg` | ^15.15.0 | SVG support for charts |
| `react-native-toast-message` | ^2.3.3 | Toast UI |
| `react-native-vector-icons` | ^10.3.0 | Icons |
| `react-native-webview` | ^13.16.1 | Privacy/Terms webview |

**Deprecated package:** `react-native-push-notification` is **archived** (no longer maintained). Recommended replacement: `notifee` by Invertase. Not blocking now but plan migration before the library falls behind React Native versions.

---

## 6. Android Permissions Audit

| Permission | Required | Why |
|-----------|---------|-----|
| `INTERNET` | Yes | API sync |
| `ACCESS_NETWORK_STATE` | Yes | Network check before sync |
| `READ_CALL_LOG` | Yes — core feature | Read device call log |
| `READ_PHONE_STATE` | Yes — core feature | Phone state detection |
| `READ_CONTACTS` | Yes | Contact name resolution |
| `POST_NOTIFICATIONS` | Yes (Android 13+) | Sync success/failure alerts |
| `FOREGROUND_SERVICE` | Yes | BackgroundFetch foreground service |
| `FOREGROUND_SERVICE_DATA_SYNC` | Yes | Foreground service type declaration |
| `RECEIVE_BOOT_COMPLETED` | Yes | BackgroundFetch `startOnBoot: true` |
| `WAKE_LOCK` | Yes | BackgroundFetch keeps CPU awake during sync |

All permissions are necessary and justified. No over-declared permissions found.

---

## 7. TypeScript Status

**Current:** 0 errors with `npx tsc --noEmit --types react --skipLibCheck`

**Remaining `any` count (approximate):** ~30 usages across 9 files — all in catch blocks and API response handling. These are not TypeScript errors today but violate the intent of `noImplicitAny`.

**Pre-existing issue (not introduced by this project):**  
`TS2688: Cannot find type definition file for 'jest'` — `@react-native/typescript-config` includes `types: ["jest"]` but `@types/jest` is not installed.  
Fix: `npm install -D @types/jest`

---

## 8. Build Status

| Check | Status |
|-------|--------|
| TypeScript | 0 errors (`--skipLibCheck`) |
| Android Gradle | Clean |
| ProGuard | Enabled for release |
| Signing | Configured (falls back to debug if keystore absent) |
| Active Kotlin files | 3 (MainActivity, MainApplication, CallLogSyncService) |
| Android Manifest | All declared components have matching Kotlin classes |
| `version.properties` | Present — bump before release |

---

## 9. Play Store Readiness

| Item | Status | Action |
|------|--------|--------|
| Package name | `io.callytics.app` | Ready |
| Permissions declared | All correct | Ready |
| Background execution | WorkManager compliant | Ready |
| ProGuard | Enabled | Ready |
| Versioning | `versionCode: 1` / `versionName: 1.0.0` | Bump before each release |
| Production API URL | Dev IP active | Set HTTPS URL before release build |
| Privacy policy link | `callytics.io/privacy` | Verify URL is live |
| Terms link | `callytics.io/terms` | Verify URL is live |
| App icon | Present | Verify 512x512 for Play Console |
| Signing keystore | Needs `keystore.properties` | Generate production keystore |
| `android:allowBackup="false"` | Set | Good for security |

---

## 10. Remaining Technical Debt

| Priority | Issue | Effort |
|----------|-------|--------|
| High | Fix `error: any` in all catch blocks → `error: unknown` | Small |
| High | Replace `require()` calls in AuthContext with static imports | Trivial |
| High | Type `storage.ts` userData as `User` | Trivial |
| High | Update prod API URL before release | Trivial |
| Medium | Wrap `fetchDashboard`/`fetchAllAnalytics` in `useCallback` | Small |
| Medium | Remove Toast from `callLogSync.ts` service | Small |
| Medium | Deduplicate `hasNotificationPermission` in callStateListener + callLogSync | Small |
| Medium | Type `AnalyticsScreen` state variables properly | Small |
| Medium | Extract Toast config from App.tsx | Small |
| Medium | Type `navigate()` params in AppNavigator | Trivial |
| Low | Remove dead `statUpdate` style in HomeScreen | Trivial |
| Low | Fix tsconfig exclude for non-existent `HomeScreen.old.tsx` | Trivial |
| Low | Remove emoji from notification title strings | Trivial |
| Low | Plan migration away from `react-native-push-notification` (archived) | Large |
| Low | Add `@types/jest` devDependency | Trivial |

---

## 11. Suggested Future Improvements

| Priority | Improvement | Why |
|----------|-------------|-----|
| High | Replace `setInterval` in `callStateListener` with native `PhoneStateListener` broadcast | Zero battery drain between calls; current 3s poll runs 24/7 |
| High | Add Detox E2E test: Login → call log visible → manual sync | Prevent regressions on the core user journey |
| Medium | Migrate auth storage from `AsyncStorage` to `react-native-mmkv` | 10x faster, synchronous reads, no async overhead on startup |
| Medium | Replace `react-native-push-notification` with `notifee` | Actively maintained; better Android 14 compat |
| Medium | Add a `/v1/app/ping` or `/v1/app/me` endpoint for token validation | `checkAuthStatus` currently calls `fetchDashboard` — heavy just for auth check |
| Medium | CI pipeline with Fastlane (version bump + signed AAB) | Manual builds are error-prone before each release |
| Low | Extract `ToastConfig` to its own component | Removes ~100 lines of inline styles from App.tsx |
| Low | Add `ThemeColors` interface, replace `colors: any` in theme/components.ts | Completes TypeScript cleanup |
| Low | Add type-safe generics to EventEmitter | Future-proof as event count grows |

---

## 12. Before Go-Live Checklist

```
[ ] Replace DEV_API_URL with HTTPS production URL in src/config.ts
[ ] Bump VERSION_CODE and VERSION_NAME in android/version.properties
[ ] Create production keystore and add keystore.properties
[ ] Verify https://callytics.io/privacy is live and accessible
[ ] Verify https://callytics.io/terms is live and accessible
[ ] Build signed release APK: npx react-native run-android --variant=release
[ ] Test release APK on a real device (not emulator) for background sync
[ ] Upload to Play Console internal test track first
[ ] Fill in Play Console store listing (screenshots, description, content rating)
[ ] Submit READ_CALL_LOG permission declaration form in Play Console
```

---

*Generated 2026-06-28 — Callytics v1.0.0 — Package io.callytics.app*
