# Privacy Policy for Callytics

**Last Updated: November 9, 2025**

## Introduction

Callytics ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our mobile application.

## Information We Collect

### 1. Call Log Data
- **What we collect**: Call timestamps, phone numbers, call duration, call type (incoming/outgoing/missed)
- **Why we collect it**: To provide call analytics and sync your call history across devices
- **How we collect it**: Through Android's Call Log API with your explicit permission

### 2. Contact Information (Optional)
- **What we collect**: Contact names from your phone's address book
- **Why we collect it**: To display caller names in your call history
- **How we collect it**: Only with your explicit permission

### 3. Device Information
- **What we collect**: Device model, operating system version, app version
- **Why we collect it**: For troubleshooting and improving app performance
- **How we collect it**: Automatically when you use the app

### 4. Network Information
- **What we collect**: Internet connectivity status
- **Why we collect it**: To determine when to sync call logs
- **How we collect it**: Through standard Android APIs

## How We Use Your Information

1. **Provide App Functionality**: Display and sync your call history
2. **Analytics**: Understand call patterns and provide insights
3. **Improve Service**: Fix bugs and enhance user experience
4. **Sync Across Devices**: Keep your call history synchronized

## How We Store Your Information

- All data is transmitted securely using HTTPS encryption
- Call logs are stored on secure servers with industry-standard encryption
- We retain your data only as long as your account is active
- You can request deletion of your data at any time

## Data Sharing

**We DO NOT:**
- ❌ Sell your call log data to third parties
- ❌ Share your information with advertisers
- ❌ Use your data for marketing purposes
- ❌ Access your data without your permission

**We MAY share data only:**
- ✅ With your explicit consent
- ✅ To comply with legal obligations
- ✅ To protect our rights or safety

## Your Rights

You have the right to:
- **Access**: View all data we have about you
- **Correct**: Update incorrect information
- **Delete**: Request permanent deletion of your account and data
- **Opt-Out**: Disable automatic call log syncing at any time
- **Export**: Download your call log data

## Permissions Explained

### Required Permissions

**READ_CALL_LOG**
- Purpose: Read call history from your device
- Used for: Syncing call logs to your account
- Control: You can revoke this permission in Android Settings

**READ_PHONE_STATE**
- Purpose: Detect when calls start and end
- Used for: Automatic call log detection
- Control: You can revoke this permission in Android Settings

### Optional Permissions

**READ_CONTACTS**
- Purpose: Display caller names in call history
- Used for: Enhanced call log display
- Control: App works without this permission

**POST_NOTIFICATIONS**
- Purpose: Show sync status notifications
- Used for: Informing you when call logs are synced
- Control: You can disable notifications in Android Settings

## Background Data Sync

Our app syncs call logs in the background every 15 minutes using Android's WorkManager API. This ensures your call history is up-to-date even when the app is closed.

**How it works:**
- Runs every 15 minutes automatically
- Only syncs when internet is available
- Shows notification after successful sync
- Respects battery optimization settings

## Children's Privacy

Our app is not intended for children under 13. We do not knowingly collect information from children under 13.

## Changes to This Policy

We may update this Privacy Policy from time to time. We will notify you of any changes by:
- Updating the "Last Updated" date
- Showing an in-app notification
- Sending an email (if you provided one)

## Data Security

We implement appropriate security measures including:
- HTTPS encryption for all data transmission
- Secure server infrastructure
- Regular security audits
- Access controls and authentication

## Contact Us

If you have questions about this Privacy Policy or want to exercise your rights, contact us:

- **Email**: support@callytics.com
- **Address**: [Your Business Address]
- **In-App**: Settings → Help & Support → Privacy

## Your Consent

By using our app, you consent to this Privacy Policy. If you do not agree, please do not use the app.

## Legal Compliance

This Privacy Policy complies with:
- Google Play Store policies
- Android data usage guidelines
- General Data Protection Regulation (GDPR)
- California Consumer Privacy Act (CCPA)

---

**For Play Store Reviewers:**

This app uses sensitive permissions (READ_CALL_LOG, READ_PHONE_STATE) for its core functionality of call log analytics and synchronization. Users explicitly grant these permissions during onboarding, and the app clearly explains why each permission is needed. Users can revoke permissions at any time through Android Settings. All data is stored securely and never sold to third parties.
