# Callytics — Complete Application Audit Report

**Date:** June 14, 2026  
**App Name:** Callytics (package: `io.callytics.app`)  
**Platform:** Android (React Native 0.80.1)  
**Auditor:** Claude Code  

---

## Table of Contents

1. [Project Structure Overview](#1-project-structure-overview)
2. [Complete API Documentation](#2-complete-api-documentation)
3. [Business Workflow Documentation](#3-business-workflow-documentation)
4. [Architecture Review](#4-architecture-review)
5. [Security Audit](#5-security-audit)
6. [Bug Report](#6-bug-report)
7. [Performance Audit](#7-performance-audit)
8. [Dead Code & Duplicate Code](#8-dead-code--duplicate-code)
9. [Permissions Review](#9-permissions-review)
10. [Background Services & Notifications Review](#10-background-services--notifications-review)
11. [State Management Review](#11-state-management-review)
12. [Google Play Store Compliance Audit](#12-google-play-store-compliance-audit)
13. [Complete Issue List with Severity](#13-complete-issue-list-with-severity)

---

## 1. Project Structure Overview

### Technology Stack

| Layer | Technology | Version |
|---|---|---|
| JS Framework | React Native | 0.80.1 |
| Language (JS) | TypeScript | 5.0.4 |
| Language (Android Native) | Kotlin | — |
| JS Engine | Hermes | enabled |
| Architecture | New Architecture (Fabric) | enabled |
| Navigation | React Navigation Stack | 7.x |
| HTTP Client | Axios | 1.12.2 |
| Local Storage | AsyncStorage | 2.2.x |
| Charts | react-native-chart-kit | 6.12.0 |
| Animations | Lottie | 7.3.4 |
| Background Tasks | react-native-background-fetch | 4.2.8 |
| Call Log Access | react-native-call-log | 2.1.2 |
| Notifications | react-native-push-notification | 8.1.1 |
| Icons | react-native-vector-icons | 10.3.0 |

### Directory Layout

```
MyFirstApp/
├── src/
│   ├── assets/               # Images and Lottie animations
│   ├── components/           # Reusable UI components (7 components)
│   │   └── analytics/        # Chart components (3 analytics charts)
│   ├── constants/index.ts    # App-wide constants
│   ├── contexts/             # React Context providers (5 contexts)
│   ├── hooks/                # Custom hooks (2 hooks)
│   ├── navigation/           # Stack and tab navigators
│   ├── screens/              # Screen components
│   │   ├── Onboarding/       # 4-step permission onboarding flow
│   │   └── Settings/         # 5 settings sub-screens
│   ├── services/             # Business logic and API layer
│   ├── theme/                # Design tokens (colors, spacing, typography)
│   ├── types/                # TypeScript interfaces
│   └── utils/                # Helper utilities
├── android/
│   └── app/src/main/java/io/callytics/app/   # 10 Kotlin files
├── index.js                  # Entry point + headless task registration
├── backgroundFetch.js        # Background fetch headless task
└── CallLogSyncTask.js        # Native service headless task
```

### Screens Inventory

| Screen | File | Purpose |
|---|---|---|
| Splash | `SplashScreen.tsx` | Animated intro |
| Login | `LoginScreen.tsx` | Mobile + App Login Code auth |
| Consent | `Onboarding/ConsentScreen.tsx` | Privacy consent gate |
| Contacts Permission | `Onboarding/ContactsPermission.tsx` | READ_CONTACTS request |
| Call Log Permission | `Onboarding/CallLogPermission.tsx` | READ_CALL_LOG request |
| Phone State Permission | `Onboarding/PhoneStatePermission.tsx` | READ_PHONE_STATE request |
| Notification Permission | `Onboarding/NotificationPermission.tsx` | POST_NOTIFICATIONS request |
| Dashboard (Home) | `HomeScreen.tsx` | Call statistics overview |
| Call Logs | `CallLogsScreen.tsx` | Paginated call log list |
| Analytics | `AnalyticsScreen.tsx` | 3 chart types |
| Settings | `SettingsScreen.tsx` | Settings hub |
| Profile | `Settings/ProfileScreen.tsx` | User info display |
| Auto Sync | `Settings/AutoSyncScreen.tsx` | Sync configuration |
| Privacy | `Settings/PrivacyScreen.tsx` | Privacy settings |
| Privacy Policy | `Settings/PrivacyPolicyScreen.tsx` | WebView policy page |
| Help & Support | `Settings/HelpSupportScreen.tsx` | Contact/FAQ |
| About | `Settings/AboutScreen.tsx` | App version info |
| WebView | `WebViewScreen.tsx` | Generic browser screen |

### Android Native Components (Kotlin)

| File | Type | Purpose |
|---|---|---|
| `MainActivity.kt` | Activity | Main app entry point |
| `MainApplication.kt` | Application | App init, registers CallModule |
| `CallModule.kt` | NativeModule | JS bridge to start CallService |
| `CallService.kt` | Foreground Service | Processes calls, uploads directly |
| `CallReceiver.kt` | BroadcastReceiver | Listens for phone state changes |
| `BootReceiver.kt` | BroadcastReceiver | Starts CallService on device boot |
| `CallLogBroadcastReceiver.kt` | BroadcastReceiver | Triggers WorkManager sync |
| `CallLogSyncService.kt` | HeadlessJsTaskService | Runs JS headless sync task |
| `CallLogSyncWorker.kt` | CoroutineWorker | Triggers RN sync via event |
| `UploadWorker.kt` | Worker | Retries failed uploads |

---

## 2. Complete API Documentation

### Base URL

| Environment | URL | Protocol |
|---|---|---|
| Development (current) | `http://192.168.1.5:8000/api` | HTTP (insecure) |
| Production (required) | Not yet configured | HTTPS (required) |

### Global Headers (all authenticated endpoints)

```
Content-Type: application/json
Authorization: Bearer <access_token>
```

Timeout: 30 seconds  
Auth token source: `AsyncStorage` key `auth_token`

---

### 2.1 Authentication

#### POST `/v1/app/login`

**Purpose:** Authenticate a SIM/user with mobile number and organization-issued app login code.

**Authentication:** None (public endpoint)

**Request Body:**
```json
{
  "mobile": "9876543210",
  "app_login_code": "ORG-XXXX-XXXX"
}
```

| Field | Type | Validation | Notes |
|---|---|---|---|
| `mobile` | string | 10 digits, numeric only | No country code prefix sent |
| `app_login_code` | string | Format `AAA-BBBB-CCCC` (3-4-4) | Auto-formatted in UI |

**Response (200 OK):**
```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "sim": {
    "id": 1,
    "mobile": "9876543210",
    "name": "John Doe",
    "organization_id": 1,
    "department_id": 2,
    "status": "active",
    "created_at": "2025-01-01T00:00:00.000Z",
    "updated_at": "2025-01-01T00:00:00.000Z",
    "deleted_at": null,
    "organization": {
      "id": 1,
      "name": "Acme Corp",
      "email": "admin@acme.com",
      "mobile": "9876543210",
      "app_login_code": "ACM-1234-5678",
      "description": "...",
      "status": "active",
      "created_at": "...",
      "updated_at": "..."
    },
    "department": {
      "id": 2,
      "name": "Sales",
      "organization_id": 1,
      "created_at": "...",
      "updated_at": "..."
    }
  }
}
```

**Stored After Login:**
- `auth_token` → `access_token`
- `user_mobile` → `sim.mobile`
- `user_data` → `{ id, name, mobile, department_name }`

**Error Handling:** 401 shows "Login Failed" alert; other errors use centralized handler.

---

#### POST `/v1/app/logout`

**Purpose:** Invalidate the server-side session token.

**Authentication:** Bearer token

**Request Body:** None

**Response:** Not explicitly typed (`void`). Failure is silently ignored — local logout proceeds regardless.

---

### 2.2 Call Log Sync

#### POST `/v1/app/call-logs/push`

**Purpose:** Upload a single call log entry to the server.

**Authentication:** Bearer token

**Request Body:**
```json
{
  "unique_id": "CL260128143052A7B9m2p8",
  "user_id": "42",
  "date_time": "2026-01-28 14:30:52",
  "call_status": "Answered",
  "caller_number": "9876543210",
  "call_type": "inbound",
  "caller_duration": "120",
  "conversation_duration": "120",
  "ring_duration": "0",
  "contact_status": "Saved",
  "name": "John Doe",
  "hangup_by": "user"
}
```

| Field | Type | Allowed Values |
|---|---|---|
| `unique_id` | string | `CL[YYMMDD][HHMMSS][HASH][RND]` — deterministic per call |
| `user_id` | string | Integer as string (`sim.id`) |
| `date_time` | string | `YYYY-MM-DD HH:mm:ss` — device local time |
| `call_status` | string | `"Answered"`, `"Missed"`, `"No Answer"` |
| `caller_number` | string | Raw phone number from device |
| `call_type` | string | `"inbound"`, `"outbound"` |
| `caller_duration` | string | Total seconds as string |
| `conversation_duration` | string | Talk time seconds as string |
| `ring_duration` | string | Ring time seconds as string |
| `contact_status` | string | `"Saved"`, `"Not Saved"` |
| `name` | string | Contact name or `"Unknown"` |
| `hangup_by` | string | `"user"`, `"remote"` |

**Retry Logic:**
- Max 3 retries with exponential backoff (1s, 2s, 4s)
- No retry on: 400, 401, 403, 422
- 422 (duplicate `unique_id`) is treated as already-synced — removed from queue

**Response (200 OK):**
```json
{
  "message": "Data received and logged."
}
```

**Response (422 Duplicate):**
```json
{
  "message": "The unique id has already been taken."
}
```

---

### 2.3 Dashboard

#### GET `/v1/app/dashboard`

**Purpose:** Fetch call statistics summary for a date range.

**Authentication:** Bearer token

**Query Parameters:**

| Parameter | Type | Format | Example |
|---|---|---|---|
| `start_date` | string | `YYYY-MM-DD` | `2026-01-28` |
| `end_date` | string | `YYYY-MM-DD` | `2026-01-28` |

**Response (200 OK):**
```json
{
  "status": true,
  "data": {
    "summary": {
      "total_calls": { "value": 42, "change": "+12%" },
      "answer_rate": { "value": "78%", "change": "+3%" },
      "avg_duration": { "value": "2m 30s", "change": "-5%" }
    },
    "outbound": {
      "answered": 20,
      "no_answer": 5,
      "total": 25,
      "change": "+8%"
    },
    "inbound": {
      "answered": 12,
      "missed": 5,
      "total": 17,
      "change": "+15%"
    },
    "alerts": {
      "missed_calls": { "value": 5 }
    }
  }
}
```

---

### 2.4 Call Logs List

#### GET `/v1/app/call-logs`

**Purpose:** Fetch paginated list of call logs from the server.

**Authentication:** Bearer token

**Query Parameters:**

| Parameter | Type | Required | Notes |
|---|---|---|---|
| `start_date` | string | Yes | `YYYY-MM-DD` |
| `end_date` | string | Yes | `YYYY-MM-DD` |
| `page` | number | Yes | Starts at 1 |
| `filter_type` | string | No | Omitted when `"all"`; otherwise `"inbound"`, `"outbound"`, `"missed"` |

**Response (200 OK):**
```json
{
  "status": true,
  "data": {
    "current_page": 1,
    "last_page": 5,
    "total": 100,
    "data": [
      {
        "id": 1,
        "unique_id": "CL260128143052A7B9m2p8",
        "time": "14:30:52",
        "date_time": "2026-01-28 14:30:52",
        "call_type": "inbound",
        "call_status": "Answered",
        "caller_number": "9876543210",
        "caller_duration": "00:02:00",
        "contact_name": "John Doe"
      }
    ]
  }
}
```

**Note:** The API returns `caller_duration` as `"HH:MM:SS"` format in the list response, but the push request sends it as raw seconds string. These are two different formats for the same field across endpoints.

---

### 2.5 Analytics

#### GET `/v1/app/analytics/daily-call-volume`

**Purpose:** Call volume for the last 7 days (no date params — always last 7 days).

**Authentication:** Bearer token

**Response (200 OK):**
```json
{
  "status": true,
  "data": {
    "avg_per_day": 6.5,
    "days": [
      { "date": "2026-01-22", "count": 8 },
      { "date": "2026-01-23", "count": 5 }
    ]
  }
}
```

---

#### GET `/v1/app/analytics/peak-hours`

**Purpose:** Identify peak call hours within a date range.

**Authentication:** Bearer token

**Query Parameters:** `start_date`, `end_date` (`YYYY-MM-DD`)

**Response (200 OK):**
```json
{
  "status": true,
  "data": { /* chart data structure — consumed by PeakCallHours component */ }
}
```

---

#### GET `/v1/app/analytics/missed-calls`

**Purpose:** Missed call breakdown for a date range.

**Authentication:** Bearer token

**Query Parameters:** `start_date`, `end_date` (`YYYY-MM-DD`)

**Response (200 OK):**
```json
{
  "status": true,
  "data": { /* chart data structure — consumed by MissedCallsChart component */ }
}
```

---

## 3. Business Workflow Documentation

### 3.1 Login Workflow

```
App Launch
  └─► SplashScreen (Lottie animation, 3 seconds)
        └─► AppNavigator checks:
              ├─ isAuthenticated = false  →  LoginScreen
              ├─ isAuthenticated = true, hasConsent = false  →  ConsentScreen
              ├─ isAuthenticated = true, hasConsent = true, onboarding incomplete  →  Permission Screens
              └─ isAuthenticated = true, hasConsent = true, onboarding done  →  MainTabs (Home)
```

**Login Screen Flow:**
1. User enters 10-digit mobile number (formatted, numeric only)
2. User enters App Login Code (auto-formatted to `ORG-XXXX-XXXX` pattern)
3. Client-side validation runs first
4. `POST /v1/app/login` is called
5. On success: token + user data stored in AsyncStorage
6. Auth state updates → AppNavigator redirects to Consent or Home

**Token Validation on App Start:**
- If token exists in storage, app calls `GET /v1/app/dashboard` to validate
- If API returns 401, storage is cleared and user is sent to Login
- If device is offline, the dashboard call fails with network error → user gets logged out even if token is valid

---

### 3.2 Onboarding / Consent Workflow

```
ConsentScreen
  ├─ "Cancel" → Exit app (BackHandler.exitApp)
  └─ "Agree & Continue" → updateConsent(true) → AppNavigator moves to:
        └─ ContactsPermission
              └─ CallLogPermission
                    └─ PhoneStatePermission
                          └─ NotificationPermission
                                └─ completeOnboarding() → Home (MainTabs)
```

**Consent Screen features:**
- Explains what data is collected (call logs: numbers, duration, timestamps)
- Links to Privacy Policy and Terms & Conditions via in-app WebView Modal
- Both legal URLs currently point to `http://192.168.1.5:8000/...` (localhost — broken in production)
- Consent value (`true`/`false`) stored in AsyncStorage as string
- No consent timestamp is recorded

**Permission Screens (4 screens):**
1. READ_CONTACTS — explains contact name enrichment
2. READ_CALL_LOG — explains call log sync feature
3. READ_PHONE_STATE — explains live call detection
4. POST_NOTIFICATIONS (Android 13+) — explains sync completion alerts

---

### 3.3 Dashboard Workflow

1. Screen mounts → `GET /v1/app/dashboard` with today's date range
2. Pull-to-refresh → re-fetches dashboard data
3. Date range picker → re-fetches with selected range
4. "Missed Calls" card tapped → emits `NAVIGATE_TO_CALL_LOGS` event with `filter: "Missed"`
5. Sync button → calls `manualSync()` → shows Toast on completion
6. Sync status card shows: pending count, last sync time, error state

**Dashboard Metrics Displayed:**
- Total Calls (with % change badge)
- Answer Rate (with % change)
- Average Duration (with % change)
- Outbound: Answered, No Answer, Total
- Inbound: Answered, Missed, Total
- Missed Calls Alert (tappable → navigates to Call Logs)
- Sync Status Card

---

### 3.4 Call Log Sync Workflow

This is the core feature. There are **two independent sync paths** (see Architecture Issues):

#### Path A — JS-Based Sync (Primary, Recommended)

```
Trigger Sources:
  ├─ App becomes active (AppState change)
  ├─ Network reconnects (NetInfo listener, 2s delay)
  ├─ Manual sync button tap
  ├─ CallStateListener polling (every 3s while app is in foreground)
  ├─ BackgroundFetch headless task (every 15 min, app killed)
  └─ CallLogSyncService headless task (triggered by WorkManager)

Step 1: processNewCallLogs()
  ├─ Check READ_CALL_LOG permission
  ├─ Read call logs from device since last processed timestamp
  ├─ For each log:
  │   ├─ Validate timestamp (range: Jan 2000 – Jan 2100)
  │   ├─ Generate deterministic unique_id: CL[YYMMDD][HHMMSS][HASH][RND]
  │   ├─ Determine: call_type, call_status, contact_status, hangup_by
  │   ├─ Skip if already in unsynced queue
  │   ├─ Skip if already in synced IDs list
  │   └─ Save to @call_logs:unsynced in AsyncStorage
  └─ Update last processed timestamp

Step 2: syncUnsyncedLogs()
  ├─ Check network connectivity
  ├─ Check auth token exists
  ├─ Read all unsynced logs from AsyncStorage
  ├─ For each log:
  │   ├─ POST /v1/app/call-logs/push
  │   ├─ On 200: mark as synced, add ID to synced list
  │   ├─ On 422 (duplicate): treat as synced, remove from queue
  │   ├─ On 400/422: permanent error — remove from queue
  │   └─ On retryable error (5xx, network): increment attempt counter
  │       └─ If attempts >= 5: remove from queue (give up)
  ├─ Show success Toast (if foreground)
  ├─ Update last sync timestamp
  └─ Show local notification (if background + permission granted)
```

#### Path B — Native Kotlin Sync (Legacy, Broken — see Bug #1)

```
CallReceiver (not registered in manifest → NEVER FIRES)
  └─ CallService.processLastCall()
        ├─ Reads call log from ContentProvider directly
        ├─ Reads auth token from RCTAsyncLocalStorage_V1 SharedPrefs
        ├─ POST /v1/app/call-logs/push (no auth header in UploadWorker)
        └─ On failure: saves to pending_uploads SharedPrefs
              └─ UploadWorker retries (but still no auth token)
```

---

### 3.5 Call Logs Screen Workflow

1. Screen mounts → `GET /v1/app/call-logs` for today, page 1, filter "All"
2. Scroll to end → load next page (appended to existing list)
3. Pull-to-refresh → reset to page 1, re-fetch
4. Date range change → reset to page 1, re-fetch
5. Filter change (All/Inbound/Outbound/Missed) → reset to page 1, re-fetch
6. Tap phone button on a call → opens device dialer
7. Receives `NAVIGATE_TO_CALL_LOGS` event → resets filter and date range to today

---

### 3.6 Analytics Workflow

1. Screen mounts → fetches all 3 chart datasets in parallel
2. Date range change → re-fetches peak hours and missed calls (daily volume is fixed to last 7 days)
3. Pull-to-refresh → re-fetches all 3 datasets
4. Three charts: Daily Call Volume (bar), Peak Hours (line/bar), Missed Calls (line/bar)

---

### 3.7 Settings / Profile Workflow

- **Profile:** Displays name, mobile, department from auth state (read-only)
- **Auto Sync:** Sync configuration screen
- **Privacy:** Privacy settings
- **Privacy Policy:** Opens WebView to `LEGAL_URLS.PRIVACY_POLICY`
- **Help & Support:** Email, website, FAQ (FAQ is placeholder), Bug Report (placeholder)
- **About:** Version info, Play Store rate/share links (currently pointing to `com.myfirstapp`)
- **Logout:** Calls `POST /v1/app/logout`, clears all local storage, navigates to Login

---

## 4. Architecture Review

### 4.1 Overall Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   React Native JS Layer                  │
│                                                         │
│  ┌─────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐  │
│  │  Auth   │  │ CallLog  │  │Onboarding│  │ Theme  │  │
│  │Context  │  │ Context  │  │ Context  │  │Context │  │
│  └────┬────┘  └────┬─────┘  └────┬─────┘  └────────┘  │
│       │            │              │                      │
│  ┌────▼────────────▼──────────────▼───────────────────┐ │
│  │              AppNavigator                          │ │
│  │  (Splash → Login → Consent → Permissions → Home)  │ │
│  └─────────────────────────────────────────────────┘  │
│                                                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  api.ts      │  │callLogSync.ts│  │callLogStorage│  │
│  │  (Axios)     │  │(Sync Service)│  │(AsyncStorage)│  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└──────────────────────────────────────────────────────────┘
          │                          │
          ▼ HTTP/HTTPS               ▼ AsyncStorage
   Backend API Server          Local Device Storage
          
┌─────────────────────────────────────────────────────────┐
│               Android Native Layer (Kotlin)             │
│                                                         │
│  CallLogSyncService (HeadlessJsTask) → JS sync task    │
│  CallLogSyncWorker (WorkManager)     → JS event emit   │
│  CallModule (NativeModule)           → starts CallSvc  │
│  ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─                     │
│  CallService   ──► HTTP direct upload  (not in manifest)│
│  CallReceiver  ──► triggers CallService (not in manifest)│
│  BootReceiver  ──► starts CallService  (not in manifest)│
│  CallLogBroadcastReceiver             (not in manifest) │
│  UploadWorker  ──► retries (no auth)                   │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Architecture Problems

#### Problem 1 — Dual Sync Architecture (CRITICAL)

The app has **two completely independent sync pipelines** that were never properly consolidated:

**Pipeline A (Active, JS-based):**
- `callLogSync.ts` → reads device call logs → stores in `AsyncStorage` → syncs via Axios with auth

**Pipeline B (Broken, Kotlin-based):**
- `CallReceiver` → `CallService` → reads device call logs → uploads directly via raw `HttpURLConnection` without auth

These pipelines use different storage backends:
- Pipeline A: `AsyncStorage` (RN managed)
- Pipeline B: `SharedPreferences` (`pending_uploads`, `call_prefs`)

Even if Pipeline B were registered in the manifest (which it is not), the same call could be uploaded twice — once by each pipeline.

#### Problem 2 — Polling Instead of Event-Driven Sync

`callStateListener.ts` polls for new call logs **every 3 seconds** continuously using `setInterval`. This:
- Prevents CPU from entering low-power state while the app is active
- Makes unnecessary AsyncStorage reads every 3 seconds even when no calls occur
- The correct approach is to listen for `READ_PHONE_STATE` events

#### Problem 3 — Token Validation Makes Extra Network Call on Startup

`AuthContext.checkAuthStatus()` validates the token by calling `GET /v1/app/dashboard` (a full data endpoint). If the device is offline, this fails with a network error and the user is **logged out** even though their token is valid. This should use a dedicated lightweight `/me` or `/validate` endpoint.

#### Problem 4 — No Offline-First Token Validation

If the user opens the app without internet and has a valid token, they are logged out. The token in storage should be trusted locally, and validation should happen lazily.

#### Problem 5 — O(n) Storage Operations

Every operation in `callLogStorage.ts` reads and writes the **entire JSON array** of unsynced logs:
- `addUnsyncedLog` → read all → parse → push → serialize → write all
- `removeSyncedLogs` → read all → parse → filter → serialize → write all
- `updateLogAttempt` → read all → parse → map → serialize → write all

With hundreds of pending logs, this becomes expensive. A key-value store with individual keys per log would be more efficient.

---

## 5. Security Audit

### 5.1 CRITICAL Security Issues

#### SEC-001 — Hardcoded Local IP Used as API Base URL (CRITICAL)

**File:** `src/services/api.ts:9` and `src/constants/index.ts:63-64`

```typescript
BASE_URL: process.env.API_BASE_URL || 'http://192.168.1.5:8000/api'
PRIVACY_POLICY: 'http://192.168.1.5:8000/privacy'
TERMS_CONDITIONS: 'http://192.168.1.5:8000/terms'
```

- The fallback is a **developer's local machine IP address**
- Uses **HTTP** (not HTTPS) — all traffic including auth tokens and phone numbers is sent in plaintext
- Any production build not explicitly setting `API_BASE_URL` environment variable will fail to connect
- React Native does not natively support `.env` files — `process.env.API_BASE_URL` will always be `undefined` at runtime unless a Babel plugin (like `react-native-dotenv`) is configured. **No such plugin is in package.json**. The fallback IP will always be used.

**Impact:** All production users will have no connectivity. All dev builds transmit sensitive data unencrypted.

---

#### SEC-002 — UploadWorker Makes Authenticated API Calls Without Auth Token (CRITICAL)

**File:** `android/app/src/main/java/io/callytics/app/UploadWorker.kt`

```kotlin
conn.setRequestProperty("Content-Type", "application/json")
// Missing: conn.setRequestProperty("Authorization", "Bearer $token")
conn.doOutput = true
```

The `UploadWorker` retries failed call log uploads from the `pending_uploads` SharedPrefs queue but **never attaches an Authorization header**. Every upload attempt will receive a 401 response from the API.

---

#### SEC-003 — Debug Keystore Committed to Git and Used as Release Signing Fallback (CRITICAL)

**File:** `android/app/build.gradle:111-115`

```groovy
release {
    storeFile keystorePropsFile.exists() ? file(keystoreProps['STORE_FILE']) : file('debug.keystore')
    storePassword keystorePropsFile.exists() ? keystoreProps['STORE_PASSWORD'] : 'android'
    // ...
}
```

The `debug.keystore` file is:
1. Committed to git (visible in repo)
2. Used as fallback for release signing when `keystore.properties` is absent

Any release APK built without a production keystore is signed with the public debug key. This means anyone can impersonate the app.

---

#### SEC-004 — Auth Token Read from Internal SharedPreferences (HIGH)

**File:** `android/app/src/main/java/io/callytics/app/CallService.kt:145`

```kotlin
val tokenPrefs = getSharedPreferences("RCTAsyncLocalStorage_V1", Context.MODE_PRIVATE)
val authToken = tokenPrefs.getString("auth_token", null)
```

`RCTAsyncLocalStorage_V1` is the **internal implementation detail** of an old version of `@react-native-async-storage`. The current package (`@react-native-async-storage/async-storage` v2.x) may store data differently. This read will silently return `null` and uploads will be skipped without error.

---

#### SEC-005 — Phone Numbers Transmitted Over HTTP (HIGH)

All call logs containing phone numbers, contact names, and call timestamps are uploaded via HTTP (not HTTPS). This is sensitive personal data transmitted in plaintext. See SEC-001.

---

#### SEC-006 — No Certificate Pinning (MEDIUM)

The app does not implement certificate pinning for API calls. A network attacker on the same network (e.g., corporate Wi-Fi) could intercept or modify API responses via a man-in-the-middle attack.

---

#### SEC-007 — Consent Timestamp Not Recorded (MEDIUM)

**File:** `src/services/storage.ts:110-117`

User consent is stored as a simple `"true"/"false"` string with no timestamp. For legal compliance (GDPR, Indian IT Act), consent records should include:
- When consent was given
- What version of the privacy policy was active
- What specifically was consented to

---

### 5.2 Medium Security Issues

#### SEC-008 — No Input Sanitization on API Response Data

API response data is rendered directly in JSX without sanitization. While this is low-risk in React Native (no HTML rendering), it is a good practice to validate data shapes.

#### SEC-009 — Token Stored in AsyncStorage (Informational)

AsyncStorage is not encrypted on Android. The auth token is stored in plaintext. On rooted devices, other apps could read it. Consider using `react-native-keychain` for token storage.

---

## 6. Bug Report

### BUG-001 — BootReceiver, CallReceiver, CallLogBroadcastReceiver, CallService NOT Registered in AndroidManifest.xml (CRITICAL)

**Severity:** CRITICAL  
**Files:** `android/app/src/main/AndroidManifest.xml`, all 4 Kotlin files

The following components are **defined in Kotlin** but **completely absent from `AndroidManifest.xml`**:

| Component | Type | Impact |
|---|---|---|
| `CallReceiver` | BroadcastReceiver | Never receives phone state events |
| `CallLogBroadcastReceiver` | BroadcastReceiver | Never fires on call state changes |
| `BootReceiver` | BroadcastReceiver | Never receives BOOT_COMPLETED |
| `CallService` | Service | Cannot be started (except from CallModule which crashes) |

The entire **native sync pipeline (Path B)** is non-functional because none of its entry points are registered.

`RECEIVE_BOOT_COMPLETED` is declared as a permission in the manifest but no receiver is registered to handle it — the permission is wasted.

---

### BUG-002 — callStateListener Uses setInterval Polling (HIGH)

**Severity:** HIGH  
**File:** `src/services/callStateListener.ts:48`

```typescript
this.subscription = setInterval(async () => {
  // Check for new call logs every 3 seconds
}, 3000);
```

This is an **active polling loop** that runs continuously while the app is in the foreground. It fires `processNewCallLogs()` every 3 seconds regardless of whether any call happened. This prevents the CPU from entering low-power state and will cause battery drain on extended use.

There is also a redundant double-guard:
```typescript
if (now - this.lastCheckTime < this.checkInterval) return;
```
This is checking the same 3-second interval twice (once via `setInterval` parameter, once manually), which is redundant.

---

### BUG-003 — `caller_duration` Format Mismatch in CallLogsScreen (HIGH)

**Severity:** HIGH  
**File:** `src/screens/CallLogsScreen.tsx:326`

```typescript
{item.caller_duration !== '00:00:00' && (
  <Text>{item.caller_duration.substring(3)}</Text>
)}
```

The push request sends `caller_duration` as raw **seconds** (e.g., `"120"`). The API list response appears to return it as `"HH:MM:SS"` format (the code uses `.substring(3)` to strip hours). This mismatch means:
- Duration is never `"00:00:00"` — it will always be a number like `"0"` or `"120"` — so the condition is almost always true
- `.substring(3)` on `"120"` returns `"20"` (wrong: shows only last digits instead of formatted time)

---

### BUG-004 — Offline Login Results in Valid-Token Logout (HIGH)

**Severity:** HIGH  
**File:** `src/contexts/AuthContext.tsx:43-68`

Token validation on app startup calls `GET /v1/app/dashboard`. If the device is offline, this throws a network error and the catch block clears auth data:

```typescript
} catch (error: any) {
  // Token validation failed (expired or invalid)
  await storageService.clearAuthData();  // ← clears even on network error
```

A user with a perfectly valid token who opens the app without internet will be **logged out** and lose all local pending sync data (which is cleared on logout).

---

### BUG-005 — CallModule.startCallMonitoring() Starts Unregistered Service (HIGH)

**Severity:** HIGH  
**File:** `android/app/src/main/java/io/callytics/app/CallModule.kt`

```kotlin
@ReactMethod
fun startCallMonitoring() {
    val serviceIntent = Intent(context, CallService::class.java)
    context.startForegroundService(serviceIntent)
}
```

`CallService` is not declared in `AndroidManifest.xml`. Calling `startForegroundService()` for an undeclared service will throw an **`IllegalArgumentException`** and crash the app with a `SecurityException` or similar.

---

### BUG-006 — showOverlayPopup Requests Undeclared Permission (MEDIUM)

**Severity:** MEDIUM  
**File:** `android/app/src/main/java/io/callytics/app/CallService.kt:233`

The `showOverlayPopup()` method (dead code) attempts to use `Settings.ACTION_MANAGE_OVERLAY_PERMISSION` which requires `android.permission.SYSTEM_ALERT_WINDOW`. This permission is **not declared in the manifest**. If this code were called, it would crash or be silently denied.

---

### BUG-007 — API Base URL Environment Variable Never Works (CRITICAL)

**Severity:** CRITICAL  
**File:** `src/services/api.ts:9`

```typescript
BASE_URL: process.env.API_BASE_URL || 'http://192.168.1.5:8000/api',
```

`process.env.API_BASE_URL` requires a Babel transform plugin (e.g., `babel-plugin-transform-inline-environment-variables` or `react-native-dotenv`) to inject env vars at build time. **Neither is present in `package.json`**. The value will always be `undefined`, so the hardcoded localhost IP is always used.

---

### BUG-008 — pushCallLogsBatch Has No Rate Limiting (MEDIUM)

**Severity:** MEDIUM  
**File:** `src/services/api.ts:148-150`

```typescript
const results = await Promise.allSettled(
  callLogs.map(log => apiClient.post('/v1/app/call-logs/push', log))
);
```

`pushCallLogsBatch` fires **all API calls simultaneously** with no concurrency limit. For a backlog of 500+ logs, this creates 500 simultaneous HTTP requests, which will overwhelm the server and likely cause timeouts for all of them.

Note: `pushCallLogsBatch` is not called in the current sync flow (which uses individual `pushCallLog` in a sequential for-loop). This function is unused dead code with a serious bug inside.

---

### BUG-009 — startAutoSync and stopAutoSync Are No-Ops (LOW)

**Severity:** LOW  
**File:** `src/contexts/CallLogContext.tsx:223-235`

```typescript
const startAutoSync = useCallback(() => {
  // [dev log only — no actual action]
}, []);

const stopAutoSync = useCallback(() => {
  // [dev log only — no actual action]
}, []);
```

These functions exist in the public interface but do nothing. Code calling them (including within `CallLogContext` itself) believes it is starting or stopping sync, but no sync is actually managed by these functions.

---

## 7. Performance Audit

### PERF-001 — Continuous 3-Second Polling (HIGH)

**File:** `src/services/callStateListener.ts`

`setInterval(async () => {...}, 3000)` runs while the app is active. Every tick:
1. Reads `LAST_PROCESSED_TIMESTAMP` from AsyncStorage
2. Calls `PermissionsAndroid.check(READ_CALL_LOG)`
3. If new logs exist, reads entire call log history
4. Reads all unsynced logs from AsyncStorage

This is 20+ async operations per minute while the user has the app open.

**Recommendation:** Use `TelephonyManager.LISTEN_CALL_STATE` via a native module to get event-driven callbacks.

---

### PERF-002 — Entire Unsynced Log Array Serialized on Every Operation (HIGH)

**File:** `src/services/callLogStorage.ts`

Every `addUnsyncedLog`, `removeSyncedLogs`, and `updateLogAttempt` call:
1. Reads the full JSON array from AsyncStorage
2. Parses it
3. Modifies it
4. Re-serializes the full array
5. Writes it back

For 1000 pending logs, each operation processes the full 1000-item array. Sequential operations within a single sync cycle (100 logs) cause 100 full read-modify-write cycles.

---

### PERF-003 — Token Validation Makes Extra API Call on Every Cold Start (MEDIUM)

**File:** `src/contexts/AuthContext.tsx:44-45`

```typescript
const today = new Date().toISOString().split('T')[0];
await dashboardAPI.fetchDashboard(today, today);
```

This calls a full data endpoint just to confirm the token is valid. It fetches unnecessary data and adds latency to cold start. A lightweight `GET /v1/app/me` endpoint would be appropriate here.

---

### PERF-004 — Sync Stats Polled Every 30 Seconds (LOW)

**File:** `src/contexts/CallLogContext.tsx:282`

```typescript
setInterval(() => { updateSyncStats(); }, SYNC_INTERVALS.STATS_UPDATE); // 30s
```

`updateSyncStats` reads from AsyncStorage every 30 seconds even when no sync activity is happening. This should be event-driven (update stats after sync completes, not on a timer).

---

### PERF-005 — No Pagination in Unsynced Log Processing (MEDIUM)

If a user goes offline for days and accumulates 2000+ unsynced logs, `syncUnsyncedLogs` loads all of them into memory at once, iterates through them sequentially, and re-writes the entire array after each operation. This can cause memory pressure and ANR (Application Not Responding) errors on low-memory devices.

---

## 8. Dead Code & Duplicate Code

### Dead Code

| Component | File | Reason Dead |
|---|---|---|
| `CallReceiver` | `android/.../CallReceiver.kt` | Not in AndroidManifest — never fires |
| `BootReceiver` | `android/.../BootReceiver.kt` | Not in AndroidManifest — never fires |
| `CallLogBroadcastReceiver` | `android/.../CallLogBroadcastReceiver.kt` | Not in AndroidManifest — never fires |
| `CallService` | `android/.../CallService.kt` | Not in AndroidManifest — starting it crashes app |
| `showOverlayPopup()` | `CallService.kt:233` | Defined but never called |
| `pushCallLogsBatch()` | `src/services/api.ts:145` | Defined but never called in sync flow |
| `startAutoSync()` | `src/contexts/CallLogContext.tsx:223` | No-op function |
| `stopAutoSync()` | `src/contexts/CallLogContext.tsx:231` | No-op function |
| `NAVIGATION_CHECK_INTERVAL` | `src/constants/index.ts:32` | Marked deprecated, unused |
| `UploadWorker` | `android/.../UploadWorker.kt` | Called by dead `CallService`; auth token missing anyway |

### Duplicate Code

| Duplication | Files | Issue |
|---|---|---|
| Dual sync pipeline | `callLogSync.ts` vs `CallService.kt` + `UploadWorker.kt` | Two independent implementations of the same feature |
| Auth token retrieval | `storage.ts:getAuthToken()` vs `CallService.kt:RCTAsyncLocalStorage_V1` | Two different mechanisms to read the same value |
| Notification permission check | `backgroundFetch.js`, `CallLogSyncTask.js`, `callStateListener.ts`, `callLogSync.ts` | Same 6-line permission check block duplicated 4 times |
| `hasNotificationPermission()` | `backgroundFetch.js:29` and `CallLogSyncTask.js:8` | Identical function, copy-pasted |
| Network error handling in sync result | `backgroundFetch.js:104` and `CallLogSyncTask.js:59` | Identical `error.includes('internet')` check blocks |

---

## 9. Permissions Review

### Declared Permissions

| Permission | Declared | Registered Receiver/Service | Sensitivity | Assessment |
|---|---|---|---|---|
| `INTERNET` | ✅ | N/A | Low | Required, justified |
| `ACCESS_NETWORK_STATE` | ✅ | N/A | Low | Required, justified |
| `READ_CALL_LOG` | ✅ | Used in JS layer | **HIGH** | Core feature, must justify to Play Store |
| `READ_PHONE_STATE` | ✅ | Used in JS layer | HIGH | Used for call state, justified |
| `READ_CONTACTS` | ✅ | Used in onboarding | Medium | Optional feature |
| `POST_NOTIFICATIONS` | ✅ | Used for sync notifications | Low | Optional feature |
| `FOREGROUND_SERVICE` | ✅ | `CallLogSyncService` | Low | Required for headless service |
| `FOREGROUND_SERVICE_DATA_SYNC` | ✅ | `CallLogSyncService` | Low | Required for data sync foreground type |
| `RECEIVE_BOOT_COMPLETED` | ✅ | **No receiver registered** | Low | **WASTED — BootReceiver not in manifest** |
| `WAKE_LOCK` | ✅ | Used by WorkManager implicitly | Low | May be required by background-fetch |

### Permissions Not Declared But Used

| Permission | Used In | Risk |
|---|---|---|
| `SYSTEM_ALERT_WINDOW` | `CallService.showOverlayPopup()` (dead code) | Would crash if called — not declared |

### Permission Summary

- `READ_CALL_LOG` is a **restricted permission** on Android. Since Android 10 (API 29), this permission is additionally scrutinized. Google Play requires explicit justification.
- The `RECEIVE_BOOT_COMPLETED` permission is declared but serves no purpose since `BootReceiver` is not in the manifest.

---

## 10. Background Services & Notifications Review

### Background Processing Architecture

The app uses three background mechanisms:

#### 1. react-native-background-fetch (Every 15 minutes)

Configured in `backgroundFetch.js`. Runs `BackgroundFetchHeadlessTask` when app is killed.  
**Status:** Working correctly. Checks auth, processes logs, syncs queue, shows notification.

#### 2. HeadlessJsTaskService + WorkManager (Post-call)

`CallLogSyncWorker` (WorkManager) → emits `CallLogSyncTrigger` event → JS layer runs `CallLogSyncTask`.  
**Status:** Partially working. WorkManager task exists, but the trigger (CallLogBroadcastReceiver) is not registered in the manifest, so it is never scheduled. The `CallLogSyncService` can be started by `CallLogSyncTask` directly if another mechanism triggers it.

#### 3. CallStateListener setInterval (3-second polling)

Active only when app is in foreground.  
**Status:** Working but inefficient. See PERF-001.

### Notification Channels

| Channel ID | Name | Importance | Used For |
|---|---|---|---|
| `call-log-sync` | (set by push-notification library) | High | Sync success/failure notifications |
| `call_log_sync_service` | "Call Log Sync Service" | Low | Foreground service persistent notification |
| `call_monitor_channel` | "Call Monitor" | Low | CallService monitoring notification (dead) |
| `call_data_push_channel` | "Callytics Data Push" | High | CallService upload success (dead) |

Two notification channels (`call_monitor_channel` and `call_data_push_channel`) are created by the dead `CallService` code. They will appear in the app's notification settings even though they are never used.

### Foreground Service

`CallLogSyncService` runs as a foreground service with a persistent "Syncing Call Logs" notification. This appears every time a background sync runs. On Android 13+, this requires the `FOREGROUND_SERVICE_DATA_SYNC` service type (declared correctly).

---

## 11. State Management Review

### Context Architecture

| Context | Purpose | Stored State |
|---|---|---|
| `AuthContext` | Auth state, login/logout | `isAuthenticated`, `isLoading`, `user`, `token`, `mobile` |
| `OnboardingContext` | Onboarding flow progress | `isOnboardingCompleted`, `isLoading`, `hasConsent` |
| `CallLogContext` | Sync state, auto-sync lifecycle | `isSyncing`, `lastSyncTime`, `pendingCount`, `error` |
| `ThemeContext` | Dark/light mode | `theme`, `colorScheme` |
| `NetworkContext` | Network connectivity | (wraps NetInfo) |

### State Management Issues

#### Issue 1 — Dual Source of Truth for Auth Token

The auth token exists in two places:
1. React state: `authState.token` in `AuthContext`
2. AsyncStorage: `auth_token` key

The Axios interceptor reads from AsyncStorage on every request. The React state token is not used for API calls. If they diverge, API calls could use an old token while the UI shows a new one.

#### Issue 2 — No Global State Library

State is managed entirely via React Context + `useState`. Context re-renders all consumers on any state change. For example, `CallLogContext` updates every 30 seconds (stats timer), which re-renders all components consuming it.

#### Issue 3 — Sync State Error Not Cleared on Success Path

In `CallLogContext.manualSync()`, if sync succeeds, `error` is cleared. But in the network-reconnect auto-sync path (line 165), error is only cleared if `syncResult.success` is true — if the result is `{ success: true, syncedCount: 0 }` (nothing to sync), the previous error string remains displayed in the UI.

#### Issue 4 — `isMountedRef` Pattern Used for async Safety

The context uses a `isMountedRef` to prevent setState after unmount. This is the old pattern. Modern React (18+) does not require this guard in most cases. The pattern is correctly implemented but is overly cautious.

---

## 12. Google Play Store Compliance Audit

### 12.1 Sensitive Permissions Declaration

| Risk | Description | Severity |
|---|---|---|
| `READ_CALL_LOG` permission | This is a **restricted sensitive permission**. Requires signed declaration in Play Console stating the core use case. App will be rejected without justification. | CRITICAL |
| `READ_PHONE_STATE` permission | Requires justification in Data Safety form | HIGH |
| `READ_CONTACTS` permission | Requires justification in Data Safety form | HIGH |
| No OTP/SMS feature | If only `READ_PHONE_STATE` is used to detect call state, must be clearly justified | MEDIUM |

### 12.2 Data Safety Form Requirements

The app **must** declare the following in the Play Console Data Safety section:

| Data Type | Collected | Transmitted | Purpose |
|---|---|---|---|
| Phone numbers | Yes | Yes (to API server) | Core analytics feature |
| Contact names | Yes | Yes (to API server) | Contact enrichment |
| Call timestamps | Yes | Yes (to API server) | Analytics |
| Call duration | Yes | Yes (to API server) | Analytics |
| Call type (inbound/outbound) | Yes | Yes (to API server) | Analytics |
| User mobile number | Yes | Yes (login credential) | Authentication |

**Failure to declare this data will result in app removal after launch.**

### 12.3 Privacy Policy Requirements

| Issue | Status | Risk |
|---|---|---|
| Privacy Policy URL points to localhost | `http://192.168.1.5:8000/privacy` | CRITICAL — app will be rejected |
| Privacy Policy must be public HTTPS URL | Not configured | CRITICAL |
| Privacy Policy must be accessible without installing app | Cannot be — pointing to local IP | CRITICAL |
| Privacy Policy content must match data actually collected | Unknown — policy content not reviewed | HIGH |

### 12.4 Background Processing Scrutiny

Apps using `READ_CALL_LOG` + `RECEIVE_BOOT_COMPLETED` + background services receive enhanced review from Google. The app must clearly explain:
- Why it reads call logs in the background
- What data is stored and for how long
- Who receives the data (organization's server)

### 12.5 Other Compliance Issues

| Issue | Risk |
|---|---|
| Package ID `com.myfirstapp` (Go-Live checklist notes `io.callytics.app` is actual ID in build.gradle — confirm which is final) | HIGH — cannot change after first release |
| Consent screen exists but consent timestamp not recorded | MEDIUM — legal risk |
| "FAQ coming soon" and "Bug report coming soon" placeholders in Help screen | LOW — appears unfinished |
| App version `0.0.1` in package.json | LOW — update before submission |
| Legal URLs, support email, and Play Store links are placeholder values | HIGH — must update before submission |

---

## 13. Complete Issue List with Severity

### Severity Definitions

- **CRITICAL** — App won't work, crashes, security breach, or Play Store rejection
- **HIGH** — Major functionality broken or significant security risk
- **MEDIUM** — Feature partially broken or moderate risk
- **LOW** — Minor issue, code quality, or cleanup

---

### Critical Issues (Fix Before Any Release)

| # | Category | Issue | File(s) |
|---|---|---|---|
| C-01 | Security | API Base URL is `http://192.168.1.5:8000/api` — hardcoded dev IP, HTTP not HTTPS | `src/services/api.ts:9` |
| C-02 | Security | Privacy Policy & Terms URLs point to localhost — will be broken for all users | `src/constants/index.ts:63-64` |
| C-03 | Security | `UploadWorker` sends call logs with no auth token — all uploads will return 401 | `android/.../UploadWorker.kt` |
| C-04 | Security | `debug.keystore` committed to git, used as release signing fallback | `android/app/build.gradle:103-115`, `android/app/debug.keystore` |
| C-05 | Bug | `process.env.API_BASE_URL` never works — no Babel env plugin configured | `src/services/api.ts:9`, `package.json` |
| C-06 | Bug | `CallReceiver`, `BootReceiver`, `CallLogBroadcastReceiver`, `CallService` not in manifest — entire native sync path non-functional | `android/.../AndroidManifest.xml` |
| C-07 | Bug | `CallModule.startCallMonitoring()` starts service not in manifest — crashes app | `android/.../CallModule.kt`, `android/.../CallService.kt` |
| C-08 | Bug | Offline startup logs out user with valid token (network error misread as auth error) | `src/contexts/AuthContext.tsx:56-67` |
| C-09 | Compliance | `READ_CALL_LOG` permission requires Play Console declaration and justification | `android/.../AndroidManifest.xml` |
| C-10 | Compliance | Privacy Policy must be a public HTTPS URL for Play Store submission | `src/constants/index.ts:63` |

---

### High Severity Issues

| # | Category | Issue | File(s) |
|---|---|---|---|
| H-01 | Architecture | Dual sync pipeline — Kotlin path and JS path are completely independent, same call could upload twice | `CallService.kt` vs `callLogSync.ts` |
| H-02 | Security | Auth token read from `RCTAsyncLocalStorage_V1` SharedPrefs — unreliable, version-specific | `android/.../CallService.kt:145` |
| H-03 | Bug | `caller_duration` format mismatch — push sends raw seconds, list returns HH:MM:SS — wrong data displayed | `src/screens/CallLogsScreen.tsx:326` |
| H-04 | Performance | `callStateListener` polls every 3 seconds with setInterval — causes battery drain | `src/services/callStateListener.ts:48` |
| H-05 | Performance | All unsynced log storage operations read/write entire JSON array — O(n) for each op | `src/services/callLogStorage.ts` |
| H-06 | Compliance | Data Safety form must declare: phone numbers, contact names, timestamps, durations are collected + transmitted | Play Console |
| H-07 | Compliance | Support email, Play Store URL, and app version are all placeholder values | Multiple settings screens |

---

### Medium Severity Issues

| # | Category | Issue | File(s) |
|---|---|---|---|
| M-01 | Security | No HTTPS enforcement / certificate pinning | Network layer |
| M-02 | Security | Consent stored without timestamp — legally insufficient | `src/services/storage.ts:110` |
| M-03 | Bug | `showOverlayPopup()` uses `SYSTEM_ALERT_WINDOW` not declared in manifest | `android/.../CallService.kt:233` |
| M-04 | Bug | `pushCallLogsBatch` fires all requests simultaneously — no concurrency limit | `src/services/api.ts:148` |
| M-05 | Bug | Error state in `CallLogContext` not cleared when auto-sync finds nothing to sync | `src/contexts/CallLogContext.tsx:160-170` |
| M-06 | Architecture | Token validation on startup calls full data endpoint, not a lightweight validate endpoint | `src/contexts/AuthContext.tsx:44` |
| M-07 | Performance | `updateSyncStats` polled every 30 seconds instead of event-driven | `src/contexts/CallLogContext.tsx:282` |
| M-08 | Performance | No pagination in unsynced log processing — loads all logs into memory at once | `src/services/callLogSync.ts:452` |
| M-09 | Compliance | `RECEIVE_BOOT_COMPLETED` declared but no receiver is registered — permission is wasted | `AndroidManifest.xml:21` |
| M-10 | Dead Code | `startAutoSync()` and `stopAutoSync()` are public API no-ops | `src/contexts/CallLogContext.tsx:223-235` |

---

### Low Severity Issues

| # | Category | Issue | File(s) |
|---|---|---|---|
| L-01 | Dead Code | `pushCallLogsBatch` — defined but never called | `src/services/api.ts:145` |
| L-02 | Dead Code | `NAVIGATION_CHECK_INTERVAL` constant — deprecated, unused | `src/constants/index.ts:32` |
| L-03 | Dead Code | Duplicate `hasNotificationPermission()` in `backgroundFetch.js` and `CallLogSyncTask.js` | Both files |
| L-04 | Dead Code | Two dead notification channels from `CallService` show in system notification settings | `android/.../CallService.kt:43,213` |
| L-05 | Code Quality | `startAutoSync`/`stopAutoSync` are exported as public interface but are no-ops | `src/contexts/CallLogContext.tsx` |
| L-06 | Compliance | "FAQ coming soon" and "Bug report coming soon" placeholder alerts in Help screen | `src/screens/Settings/HelpSupportScreen.tsx` |
| L-07 | Security | Auth token stored in unencrypted AsyncStorage — readable on rooted devices | `src/services/storage.ts` |
| L-08 | Code Quality | `callStateListener` has redundant double-guard for 3-second interval | `src/services/callStateListener.ts:46-53` |
| L-09 | Compliance | `package.json` version is `"0.0.1"` — update before submission | `package.json:3` |
| L-10 | Compliance | About screen has hardcoded dates (Nov 23, 2025) and build number (100) | `src/screens/Settings/AboutScreen.tsx` |

---

## Summary Statistics

| Severity | Count |
|---|---|
| Critical | 10 |
| High | 7 |
| Medium | 10 |
| Low | 10 |
| **Total** | **37** |

### Priority Order for Fixes

1. **C-01, C-02, C-05** — Fix all hardcoded URLs and add HTTPS before any real device testing
2. **C-04** — Generate production keystore, remove debug keystore from git
3. **C-06** — Register all Android components in manifest (or remove unused ones)
4. **C-07** — Fix `CallModule` or remove it
5. **C-03** — Add auth token to `UploadWorker` (or delete the whole dead native sync path)
6. **C-08** — Fix offline startup logout bug
7. **H-03** — Fix `caller_duration` format mismatch
8. **H-04** — Replace polling with event-driven call detection
9. **H-01** — Remove dead native sync path to eliminate dual-upload risk
10. **C-09, C-10, H-06, H-07** — Complete Play Store compliance items before submission

---

*Audit completed: June 14, 2026*  
*Total files reviewed: 45+ source files*  
*App name: Callytics | Package: io.callytics.app | Platform: Android*
