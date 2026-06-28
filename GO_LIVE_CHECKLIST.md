# Callytics — Go Live Checklist

**Prepared:** June 6, 2026  
**App:** Callytics (Call Log Analytics & Sync)  
**Platform:** Android (Google Play Store)

---

## How to Use This Document

Go through each item before submitting to Play Store.  
Mark each item ✅ when done.  
Items marked 🔴 are blockers — the app will not work or will be rejected without fixing them.

---

## 🔴 CRITICAL — Fix First (App Won't Work Without These)

| Done | # | What to Change | Current Value | File | Line |
|------|---|---------------|---------------|------|------|
| ☐ | 1 | **API Base URL** | `http://192.168.1.5:8000/api` | `src/services/api.ts` | 9 |
| ☐ | 2 | **Privacy Policy URL** | `http://192.168.1.5:8000/privacy` | `src/constants/index.ts` | 63 |
| ☐ | 3 | **Terms & Conditions URL** | `http://192.168.1.5:8000/terms` | `src/constants/index.ts` | 64 |
| ☐ | 4 | **Production Keystore** | Using `debug.keystore` for release builds | `android/app/build.gradle` | 103 |
| ☐ | 5 | **Android Package ID** | `com.myfirstapp` — confirm this is your final ID (cannot be changed after first release) | `android/app/build.gradle` | 82 |

### Notes on Critical Items

**Item 1 — API Base URL:**  
Change `http://192.168.1.5:8000/api` to your production server URL.  
Example: `https://api.callytics.com/api`  
Also change from HTTP to HTTPS.

**Items 2 & 3 — Legal URLs:**  
Host the privacy policy and terms on a public domain first.  
Then update both values in `src/constants/index.ts`:
```
PRIVACY_POLICY: 'https://yourcompany.com/privacy'
TERMS_CONDITIONS: 'https://yourcompany.com/terms'
```

**Item 4 — Production Keystore:**  
Generate a release keystore using:
```
keytool -genkey -v -keystore callytics-release.keystore -alias callytics -keyalg RSA -keysize 2048 -validity 10000
```
Then update `android/app/build.gradle` release signing config.  
⚠️ Store the keystore file and passwords safely — losing them means you can never update the app.

**Item 5 — Package ID:**  
`com.myfirstapp` is your current ID. If you want to change it (e.g., to `com.callytics.app`), do it NOW before the first release — it can never be changed after publishing.

---

## 🟡 REQUIRED — Must Update Before Submission

| Done | # | What to Change | Current Value | File | Line |
|------|---|---------------|---------------|------|------|
| ☐ | 6 | **Support Email (mailto link)** | `support@callytics.com` | `src/screens/Settings/HelpSupportScreen.tsx` | 10 |
| ☐ | 7 | **Support Email (display text)** | `support@callytics.com` | `src/screens/Settings/HelpSupportScreen.tsx` | 31 |
| ☐ | 8 | **Support Website URL** | `https://callytics.com/support` | `src/screens/Settings/HelpSupportScreen.tsx` | 14 |
| ☐ | 9 | **Support Hours** | Mon–Fri 9AM–6PM, Sat–Sun 10AM–4PM | `src/screens/Settings/HelpSupportScreen.tsx` | 106–107 |
| ☐ | 10 | **Play Store URL (Rate Us button)** | `id=com.myfirstapp` | `src/screens/Settings/AboutScreen.tsx` | 23 |
| ☐ | 11 | **Play Store URL (Share App button)** | `id=com.myfirstapp` | `src/screens/Settings/AboutScreen.tsx` | 27 |
| ☐ | 12 | **Android Version Code** | `1` (increment for every new release) | `android/app/build.gradle` | 85 |
| ☐ | 13 | **Android Version Name** | `"1.0"` | `android/app/build.gradle` | 86 |
| ☐ | 14 | **package.json Version** | `"0.0.1"` | `package.json` | 3 |

---

## 🔵 ACCURACY — Update to Match Reality

| Done | # | What to Change | Current Value | File | Line |
|------|---|---------------|---------------|------|------|
| ☐ | 15 | **About Screen — App Version** | `1.0.0` | `src/screens/Settings/AboutScreen.tsx` | 17 |
| ☐ | 16 | **About Screen — Build Number** | `100` | `src/screens/Settings/AboutScreen.tsx` | 18 |
| ☐ | 17 | **About Screen — Last Updated Date** | `November 23, 2025` | `src/screens/Settings/AboutScreen.tsx` | 19 |
| ☐ | 18 | **Copyright Year** | `© 2025 Callytics. All rights reserved.` | `src/screens/Settings/AboutScreen.tsx` | 152 |
| ☐ | 19 | **App Name (Android)** | `Callytics` — confirm spelling/casing | `android/app/src/main/res/values/strings.xml` | 2 |
| ☐ | 20 | **App Display Name** | `Callytics` | `app.json` | 3 |

---

## ⚪ IMPLEMENT OR REMOVE — Before Launch

| Done | # | Feature | Current State | File | Line |
|------|---|---------|---------------|------|------|
| ☐ | 21 | **FAQ Button** | Shows "FAQ section coming soon!" alert | `src/screens/Settings/HelpSupportScreen.tsx` | 18 |
| ☐ | 22 | **Report a Bug Button** | Shows "Bug reporting feature coming soon!" alert | `src/screens/Settings/HelpSupportScreen.tsx` | 22 |

> Either implement these features or hide/remove the buttons before going live.

---

## 📋 PLAY STORE SUBMISSION CHECKLIST

These are things to do inside Google Play Console — not code changes.

| Done | # | Task |
|------|---|------|
| ☐ | 23 | Create Google Play Developer account |
| ☐ | 24 | Create new app in Play Console |
| ☐ | 25 | Fill in **Privacy Policy URL** in store listing (must be public HTTPS URL) |
| ☐ | 26 | Fill in **Data Safety Form** — declare call logs, phone numbers, timestamps are collected |
| ☐ | 27 | Fill in **Permissions Declaration Form** for `READ_CALL_LOG` — explain it is the core feature |
| ☐ | 28 | Set **App Category** to Business or Productivity |
| ☐ | 29 | Upload **screenshots** (min 2, max 8 per device type) |
| ☐ | 30 | Upload **feature graphic** (1024 × 500 px) |
| ☐ | 31 | Upload **app icon** (512 × 512 px, PNG) |
| ☐ | 32 | Write **app description** — mention call log sync and analytics clearly |
| ☐ | 33 | Generate and upload **signed release APK or AAB** (using production keystore, not debug) |
| ☐ | 34 | Set **content rating** (complete the questionnaire) |
| ☐ | 35 | Set **target countries** |
| ☐ | 36 | Set **app price** (Free or Paid) |

---

## 🔒 SECURITY REMINDERS

| Done | # | Task |
|------|---|------|
| ☐ | 37 | Never commit production keystore to git — add to `.gitignore` |
| ☐ | 38 | Never commit keystore passwords to git — use environment variables |
| ☐ | 39 | Ensure API server uses HTTPS (not HTTP) in production |
| ☐ | 40 | Ensure Privacy Policy and Terms pages are served over HTTPS |

---

## 📁 Quick Reference — Files to Edit for Go-Live

```
src/
├── constants/index.ts              → LEGAL_URLS (privacy & terms URLs)
├── services/api.ts                 → API base URL
└── screens/
    └── Settings/
        ├── AboutScreen.tsx         → version, build, date, copyright, Play Store URL
        └── HelpSupportScreen.tsx   → support email, website, hours

android/app/
├── build.gradle                    → versionCode, versionName, package ID, keystore
└── src/main/res/values/strings.xml → app name

package.json                        → version
app.json                            → name, displayName
```

---

## 📝 VALUES TO CONFIRM WITH YOUR TEAM

Before going live, confirm the actual values for:

1. **Production API URL** — `https://???/api`
2. **Privacy Policy public URL** — `https://???/privacy`
3. **Terms & Conditions public URL** — `https://???/terms`
4. **Support email** — Is `support@callytics.com` your real inbox?
5. **Support website** — Is `callytics.com/support` live?
6. **Support hours** — Do the listed hours match your team's actual availability?
7. **Final package ID** — Is `com.myfirstapp` the intended production ID?
8. **App version** — What version number to show users (e.g., `1.0.0`)?

---

*Document generated: June 6, 2026*  
*App: Callytics | Platform: Android*
