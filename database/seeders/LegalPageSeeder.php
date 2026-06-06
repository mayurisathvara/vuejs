<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        LegalPage::updateOrCreate(
            ['type' => 'privacy'],
            [
                'title'          => 'Privacy Policy',
                'last_updated'   => 'June 2026',
                'effective_date' => 'June 2026',
                'intro_text'     => 'Callytics ("we", "us", "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, store, share, and protect information when you use: (a) The Callytics web-based admin panel (Dashboard), (b) The Callytics Android mobile application, (c) The Callytics Developer API. By using any of our services, you agree to the collection and use of information as described in this Privacy Policy.',
                'sections'       => [
                    [
                        'heading' => '1. Information We Collect',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '1.1 Account and Organization Information'],
                            ['type' => 'paragraph', 'text' => 'When an Organization registers, we collect:'],
                            ['type' => 'list', 'items' => [
                                'Organization name, email address, mobile number, industry type',
                                'Admin user name and email address',
                                'App login code (system-generated)',
                                'Subscription plan and billing details (payment processed by Razorpay)',
                            ]],
                            ['type' => 'subheading', 'text' => '1.2 User Information (Web Dashboard)'],
                            ['type' => 'paragraph', 'text' => 'For users added under an Organization (Managers, Agents), we collect:'],
                            ['type' => 'list', 'items' => [
                                'Full name, email address, mobile number',
                                'Role (Admin, Organization, Manager, User)',
                                'Team/department assignment',
                                'Profile image (optional)',
                            ]],
                            ['type' => 'subheading', 'text' => '1.3 Call Log Data (Mobile App)'],
                            ['type' => 'paragraph', 'text' => 'The Callytics mobile app collects the following call metadata from field agents\' Android devices:'],
                            ['type' => 'list', 'items' => [
                                'Phone number (caller/recipient)',
                                'Call type: Inbound, Outbound, or Missed',
                                'Call status: Answered, Missed, Not Answered',
                                'Call duration (total, conversation, ring)',
                                'Date and time of call',
                                'Contact name (if READ_CONTACTS permission is granted)',
                                'Contact status: Saved or Not Saved',
                            ]],
                            ['type' => 'paragraph', 'text' => 'We do NOT collect:'],
                            ['type' => 'list', 'items' => [
                                'Call audio recordings',
                                'SMS or message content',
                                'Location data',
                                'Browsing history',
                            ]],
                            ['type' => 'subheading', 'text' => '1.4 Device Information (Mobile App)'],
                            ['type' => 'paragraph', 'text' => 'To support troubleshooting and performance optimization, we may collect:'],
                            ['type' => 'list', 'items' => [
                                'Device model and manufacturer',
                                'Operating system version',
                                'App version',
                                'Internet connectivity status',
                            ]],
                            ['type' => 'subheading', 'text' => '1.5 SIM Card Information (Admin Panel)'],
                            ['type' => 'paragraph', 'text' => 'Organizations may register SIM cards on the platform. We collect:'],
                            ['type' => 'list', 'items' => [
                                'SIM phone numbers',
                                'SIM assignment to specific users/teams',
                                'SIM status (active/inactive)',
                            ]],
                            ['type' => 'subheading', 'text' => '1.6 Usage Data (Web Dashboard)'],
                            ['type' => 'paragraph', 'text' => 'We may collect standard server logs including:'],
                            ['type' => 'list', 'items' => [
                                'IP address, browser type, and operating system',
                                'Pages visited and features used within the dashboard',
                                'Timestamps of access and activity',
                            ]],
                            ['type' => 'subheading', 'text' => '1.7 Payment Information'],
                            ['type' => 'paragraph', 'text' => 'Billing is handled by Razorpay. We do not store full card numbers or CVV details on our servers. We receive and store:'],
                            ['type' => 'list', 'items' => [
                                'Payment status, transaction ID, and payment method type',
                                'Invoice records for subscription and add-on purchases',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '2. How We Collect Information',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '2.1 Directly from you'],
                            ['type' => 'paragraph', 'text' => 'When you register an account, add users, configure settings, make payments, or contact us for support.'],
                            ['type' => 'subheading', 'text' => '2.2 From your mobile devices (Mobile App)'],
                            ['type' => 'paragraph', 'text' => 'Through Android system APIs with your explicit permission:'],
                            ['type' => 'list', 'items' => [
                                'READ_CALL_LOG: reads call history from the device',
                                'READ_PHONE_STATE: detects when calls start and end',
                                'READ_CONTACTS: reads contact names (optional, only if permission granted)',
                                'POST_NOTIFICATIONS: shows sync status to the user',
                            ]],
                            ['type' => 'subheading', 'text' => '2.3 Automatically via the mobile app'],
                            ['type' => 'paragraph', 'text' => 'Call log data is synced automatically in the background after each call using Android\'s Foreground Service. A visible notification is shown during sync. The sync also runs periodically (every 15 minutes) to catch any missed logs.'],
                            ['type' => 'subheading', 'text' => '2.4 Via the Developer API'],
                            ['type' => 'paragraph', 'text' => 'If your Organization uses the Developer API, data accessed through the API is logged for security auditing purposes.'],
                        ],
                    ],
                    [
                        'heading' => '3. How We Use Your Information',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'We use the collected information to:'],
                            ['type' => 'subheading', 'text' => '3.1 Provide the Platform and Services'],
                            ['type' => 'list', 'items' => [
                                'Operate the admin dashboard, mobile app, and API',
                                'Display call logs, analytics, and reports to authorized users',
                                'Enable SIM card management and team organization',
                            ]],
                            ['type' => 'subheading', 'text' => '3.2 Deliver Analytics and Insights'],
                            ['type' => 'list', 'items' => [
                                'Generate call reports, summary reports, and AI-powered call insights',
                                'Provide daily call volume, peak hours, missed call analysis, and call type distribution data',
                            ]],
                            ['type' => 'subheading', 'text' => '3.3 Manage Subscriptions and Billing'],
                            ['type' => 'list', 'items' => [
                                'Process payments via Razorpay',
                                'Issue invoices and payment receipts',
                                'Enforce plan-based feature access (e.g., Developer API on Advance Plan)',
                            ]],
                            ['type' => 'subheading', 'text' => '3.4 Account and Security Management'],
                            ['type' => 'list', 'items' => [
                                'Authenticate users via email/password and API tokens (Laravel Sanctum)',
                                'Prevent unauthorized access and detect suspicious activity',
                                'Manage email verification and password resets',
                            ]],
                            ['type' => 'subheading', 'text' => '3.5 Communication'],
                            ['type' => 'list', 'items' => [
                                'Send transactional emails (verification, password reset, invoice)',
                                'Send subscription expiry and renewal reminders',
                                'Notify administrators of account or system events',
                            ]],
                            ['type' => 'subheading', 'text' => '3.6 Improve Our Services'],
                            ['type' => 'list', 'items' => [
                                'Analyze usage patterns to enhance features and performance',
                                'Debug technical issues and improve app stability',
                            ]],
                            ['type' => 'subheading', 'text' => '3.7 Legal Compliance'],
                            ['type' => 'list', 'items' => [
                                'Comply with applicable laws and regulations',
                                'Respond to lawful requests from government or regulatory authorities',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '4. How We Store and Protect Your Information',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '4.1 Data Storage'],
                            ['type' => 'list', 'items' => [
                                'All data is stored on secure servers (hosted on AWS infrastructure)',
                                'Data is retained as long as your account is active',
                                'Upon account termination, data is deleted within 30 days unless retention is required by law',
                            ]],
                            ['type' => 'subheading', 'text' => '4.2 Security Measures'],
                            ['type' => 'list', 'items' => [
                                'All data transmission is encrypted using HTTPS (TLS)',
                                'API authentication is managed via Laravel Sanctum Bearer tokens',
                                'Passwords are hashed using bcrypt and never stored in plain text',
                                'API tokens are revoked on logout and password change',
                                'Access to data is role-restricted (Admin, Organization, Manager, User)',
                                'Regular security reviews are conducted',
                            ]],
                            ['type' => 'subheading', 'text' => '4.3 Mobile App (Offline Storage)'],
                            ['type' => 'list', 'items' => [
                                'The mobile app temporarily stores unsynced call logs in a local SQLite database on the device until they are successfully uploaded',
                                'This ensures no data loss when the device is offline',
                                'Synced logs are removed from local storage after successful upload',
                            ]],
                            ['type' => 'subheading', 'text' => '4.4 Data Breach Notification'],
                            ['type' => 'paragraph', 'text' => 'In the event of a security breach that may compromise your personal data, we will notify affected Organizations within a reasonable timeframe in accordance with applicable law.'],
                        ],
                    ],
                    [
                        'heading' => '5. Data Sharing and Disclosure',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'We DO NOT sell, rent, or trade your personal data or call log data to any third party. We may share data only in the following circumstances:'],
                            ['type' => 'subheading', 'text' => '5.1 Service Providers'],
                            ['type' => 'paragraph', 'text' => 'We use trusted third-party service providers who process data on our behalf:'],
                            ['type' => 'list', 'items' => [
                                'Razorpay: for payment processing',
                                'AWS: for cloud infrastructure and storage',
                                'AWS SES: for transactional email delivery',
                            ]],
                            ['type' => 'paragraph', 'text' => 'These providers are contractually bound to protect your data and use it only to provide services to us.'],
                            ['type' => 'subheading', 'text' => '5.2 Legal Requirements'],
                            ['type' => 'paragraph', 'text' => 'We may disclose your data if required to do so by law, court order, or governmental/regulatory authority, or when we believe disclosure is necessary to protect our rights or the safety of others.'],
                            ['type' => 'subheading', 'text' => '5.3 Business Transfer'],
                            ['type' => 'paragraph', 'text' => 'In the event of a merger, acquisition, or sale of all or part of our business, your data may be transferred. We will notify you before your data is transferred and becomes subject to a different privacy policy.'],
                            ['type' => 'subheading', 'text' => '5.4 With Your Consent'],
                            ['type' => 'paragraph', 'text' => 'We may share your data in any other way with your explicit prior consent.'],
                        ],
                    ],
                    [
                        'heading' => '6. Mobile App Permissions — Detailed Explanation',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '6.1 READ_CALL_LOG (Required)'],
                            ['type' => 'list', 'items' => [
                                'Purpose: Read call history from the device',
                                'Used for: Syncing call logs to the Callytics server for analytics',
                                'Control: Can be revoked in Android Settings → App Permissions',
                                'Note: App cannot sync call logs without this permission',
                            ]],
                            ['type' => 'subheading', 'text' => '6.2 READ_PHONE_STATE (Required)'],
                            ['type' => 'list', 'items' => [
                                'Purpose: Detect when a call starts and ends',
                                'Used for: Triggering automatic call log sync immediately after a call',
                                'Control: Can be revoked in Android Settings → App Permissions',
                                'Note: App cannot auto-sync without this permission; manual sync still available',
                            ]],
                            ['type' => 'subheading', 'text' => '6.3 READ_CONTACTS (Optional)'],
                            ['type' => 'list', 'items' => [
                                'Purpose: Read contact names from the phone\'s address book',
                                'Used for: Displaying caller names in call logs instead of numbers only',
                                'Control: Can be revoked; app functions normally without this',
                                'Note: Contact names are synced to the server only if this permission is granted',
                            ]],
                            ['type' => 'subheading', 'text' => '6.4 POST_NOTIFICATIONS'],
                            ['type' => 'list', 'items' => [
                                'Purpose: Show notifications to the user',
                                'Used for: Displaying sync status ("Syncing Call Logs...", "Synced")',
                                'Control: Can be disabled in Android Settings → Notifications',
                                'Note: Notifications are required by Android for the foreground sync service to remain visible',
                            ]],
                            ['type' => 'subheading', 'text' => '6.5 FOREGROUND_SERVICE / FOREGROUND_SERVICE_DATA_SYNC'],
                            ['type' => 'list', 'items' => [
                                'Purpose: Run a short-lived sync task in the background',
                                'Used for: Uploading call logs within 30–60 seconds after a call ends',
                                'Control: Managed by the app automatically; stops immediately after sync',
                                'Note: This is not a continuous background service. It starts only when syncing and stops as soon as the upload is complete',
                            ]],
                            ['type' => 'subheading', 'text' => '6.6 RECEIVE_BOOT_COMPLETED'],
                            ['type' => 'list', 'items' => [
                                'Purpose: Resume background sync after device reboot',
                                'Used for: Ensuring call log sync resumes automatically after the device is restarted',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '7. Background Data Sync',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'The Callytics mobile app syncs call logs using multiple strategies:'],
                            ['type' => 'list', 'items' => [
                                'Event-Driven Sync: Triggered automatically when a call ends. Uses a Foreground Service (visible to the user via a status notification) that runs for 30–60 seconds and stops automatically after the upload completes.',
                                'Periodic Sync: Runs approximately every 15 minutes using Android\'s WorkManager. This catches any logs missed during the event-driven sync (e.g., when the device was offline).',
                                'Manual Sync: Available in the app. Users can tap "Sync Now" at any time.',
                                'Retry with Backoff: If a sync fails (e.g., no internet), the app retries automatically with exponential backoff (2s → 4s → 8s) and then defers to the periodic 15-minute job.',
                            ]],
                            ['type' => 'paragraph', 'text' => 'The app stores unsynced call logs locally in an offline queue (SQLite) to ensure no data is lost when the device is offline.'],
                            ['type' => 'paragraph', 'text' => 'The app respects Android\'s battery optimization settings and does NOT request battery optimization exemptions.'],
                        ],
                    ],
                    [
                        'heading' => '8. Your Rights',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '8.1 Access'],
                            ['type' => 'paragraph', 'text' => 'You have the right to request a copy of the personal data we hold about you and your Organization.'],
                            ['type' => 'subheading', 'text' => '8.2 Correction'],
                            ['type' => 'paragraph', 'text' => 'You have the right to update or correct inaccurate personal information through the admin dashboard or by contacting us.'],
                            ['type' => 'subheading', 'text' => '8.3 Deletion'],
                            ['type' => 'paragraph', 'text' => 'You have the right to request deletion of your account and associated data. We will delete your data within 30 days of a valid deletion request, except where retention is required by law.'],
                            ['type' => 'subheading', 'text' => '8.4 Opt-Out of Background Sync (Mobile App)'],
                            ['type' => 'paragraph', 'text' => 'You may revoke Android permissions at any time via your device settings. Revoking READ_CALL_LOG or READ_PHONE_STATE will stop automatic call log syncing.'],
                            ['type' => 'subheading', 'text' => '8.5 Export'],
                            ['type' => 'paragraph', 'text' => 'You may export your call log data from the admin dashboard in CSV or PDF format.'],
                            ['type' => 'subheading', 'text' => '8.6 Withdraw Consent'],
                            ['type' => 'paragraph', 'text' => 'Where processing is based on consent (e.g., READ_CONTACTS permission), you may withdraw consent at any time without affecting the lawfulness of processing before withdrawal.'],
                            ['type' => 'subheading', 'text' => '8.7 How to Exercise Your Rights'],
                            ['type' => 'paragraph', 'text' => 'To exercise any of the above rights, contact us at: Email: support@callytics.com'],
                        ],
                    ],
                    [
                        'heading' => '9. Data Retention',
                        'blocks'  => [
                            ['type' => 'list', 'items' => [
                                'Account data and call logs are retained for as long as your Organization\'s account is active.',
                                'Upon account termination or deletion request, all data is permanently deleted within 30 days.',
                                'Billing records and invoices may be retained for up to 7 years to comply with financial and tax regulations.',
                                'Synced call log IDs stored locally on the mobile device are cleaned up periodically to prevent excessive local storage use.',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '10. Children\'s Privacy',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'The Callytics Platform is intended for use by businesses and their employees. It is not directed at children under the age of 13.'],
                            ['type' => 'paragraph', 'text' => 'We do not knowingly collect personal information from children under 13. If we learn that a child under 13 has provided us with personal information, we will delete it promptly.'],
                        ],
                    ],
                    [
                        'heading' => '11. Cookies and Tracking (Web Dashboard)',
                        'blocks'  => [
                            ['type' => 'list', 'items' => [
                                'The web admin dashboard may use session-based authentication tokens stored in the browser\'s local storage for maintaining user sessions.',
                                'We do not use third-party advertising cookies or tracking pixels.',
                                'Standard server access logs (IP address, browser type, pages visited) may be collected for security and performance monitoring.',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '12. International Data Transfers',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'Our servers are hosted on AWS infrastructure. If you are accessing the Platform from outside India, your data may be transferred to and processed in India or other countries where our infrastructure is located.'],
                            ['type' => 'paragraph', 'text' => 'We take appropriate measures to ensure that international transfers of personal data are conducted in accordance with applicable data protection laws.'],
                        ],
                    ],
                    [
                        'heading' => '13. Third-Party Links and Services',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'The Platform may contain links to third-party services (e.g., payment gateway). These third parties have their own privacy policies, and we are not responsible for their practices.'],
                            ['type' => 'paragraph', 'text' => 'We encourage you to review the privacy policies of any third-party services you use in connection with Callytics.'],
                        ],
                    ],
                    [
                        'heading' => '14. Legal Compliance',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'This Privacy Policy is designed to comply with:'],
                            ['type' => 'list', 'items' => [
                                'Google Play Store Developer Policies and Data Safety requirements',
                                'Android Permission and Background Service guidelines',
                                'General Data Protection Regulation (GDPR) — for users in the EU',
                                'California Consumer Privacy Act (CCPA) — for users in California',
                                'Information Technology Act, 2000 and IT (Amendment) Act, 2008 — India',
                                'Personal Data Protection principles applicable in India',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '15. Changes to This Privacy Policy',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal requirements, or other factors.'],
                            ['type' => 'paragraph', 'text' => 'We will notify you of material changes by:'],
                            ['type' => 'list', 'items' => [
                                'Updating the "Last Updated" date at the top of this document',
                                'Sending an email notification to registered Organization admins',
                                'Displaying an in-app notification on next login',
                            ]],
                            ['type' => 'paragraph', 'text' => 'Your continued use of the Platform after the effective date of any changes constitutes your acceptance of the updated Privacy Policy.'],
                        ],
                    ],
                    [
                        'heading' => '16. Contact Us',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'If you have any questions, concerns, or requests related to this Privacy Policy or the way we handle your data, please contact us:'],
                            ['type' => 'contact', 'email' => 'support@callytics.com', 'note' => 'We aim to respond to all privacy-related inquiries within 7 business days.'],
                        ],
                    ],
                ],
            ]
        );

        LegalPage::updateOrCreate(
            ['type' => 'terms'],
            [
                'title'          => 'Terms and Conditions',
                'last_updated'   => 'June 2026',
                'effective_date' => 'June 2026',
                'intro_text'     => 'Please read these Terms and Conditions carefully before using the Callytics Platform, mobile application, or API services. By accessing or using any part of our services, you agree to be bound by these Terms.',
                'sections'       => [
                    [
                        'heading' => '1. Definitions',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'In these Terms and Conditions:'],
                            ['type' => 'list', 'items' => [
                                '"Callytics", "we", "us", "our" — refers to the Callytics platform and its operators.',
                                '"Platform" — refers to the Callytics web-based admin panel, mobile application (Android), and Developer API collectively.',
                                '"Services" — means all features, tools, and functionalities provided by Callytics, including call analytics, SIM management, team management, reporting, and the Developer API.',
                                '"Organization" — means a business entity that has registered a Callytics account.',
                                '"User" — means any individual who accesses or uses the Platform on behalf of an Organization, including Admins, Managers, and Agents.',
                                '"Subscription" — means a paid plan that grants an Organization access to specific features of the Platform.',
                                '"Call Log Data" — means call records including phone numbers, call duration, call type (inbound/outbound/missed), timestamps, and contact names.',
                                '"Developer API" — means the Callytics REST API (v1/org) available to Organizations on the Advance Plan for programmatic data access.',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '2. Acceptance of Terms',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'By registering an account, accessing the web dashboard, installing the mobile application, or using the Developer API, you confirm that:'],
                            ['type' => 'list', 'items' => [
                                'You have read, understood, and agree to these Terms',
                                'You have the legal authority to bind yourself and/or your Organization to these Terms',
                                'You are at least 18 years of age',
                            ]],
                            ['type' => 'paragraph', 'text' => 'If you do not agree with any part of these Terms, you must not use the Platform.'],
                            ['type' => 'paragraph', 'text' => 'We reserve the right to update these Terms at any time. We will notify registered Organizations of material changes via email or in-app notification. Continued use of the Platform after changes take effect constitutes acceptance of the updated Terms.'],
                        ],
                    ],
                    [
                        'heading' => '3. Account Registration and Responsibilities',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '3.1 Registration'],
                            ['type' => 'paragraph', 'text' => 'To use the Platform, Organizations must register by providing a valid business name, email address, mobile number, and industry type. You agree to provide accurate, current, and complete information.'],
                            ['type' => 'subheading', 'text' => '3.2 Account Security'],
                            ['type' => 'paragraph', 'text' => 'You are responsible for:'],
                            ['type' => 'list', 'items' => [
                                'Maintaining the confidentiality of your account credentials and API tokens',
                                'All activities that occur under your account',
                                'Immediately notifying us of any unauthorized access or security breach',
                            ]],
                            ['type' => 'subheading', 'text' => '3.3 Email Verification'],
                            ['type' => 'paragraph', 'text' => 'All accounts require email verification before access is granted. We reserve the right to suspend unverified accounts.'],
                            ['type' => 'subheading', 'text' => '3.4 Organization Admin'],
                            ['type' => 'paragraph', 'text' => 'The Organization Admin is responsible for managing users, assigning roles (Manager, Agent), assigning SIM cards, and ensuring compliance with these Terms by all users under their account.'],
                            ['type' => 'subheading', 'text' => '3.5 One Account Per Organization'],
                            ['type' => 'paragraph', 'text' => 'Each Organization may maintain one primary account. Multiple organizations must register separately.'],
                        ],
                    ],
                    [
                        'heading' => '4. Subscriptions and Billing',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '4.1 Plans'],
                            ['type' => 'paragraph', 'text' => 'Callytics offers subscription plans (including a Basic Plan and an Advance Plan) with different features and SIM card limits. Feature availability depends on the active plan.'],
                            ['type' => 'subheading', 'text' => '4.2 Payments'],
                            ['type' => 'paragraph', 'text' => 'Payments are processed securely through Razorpay. By subscribing, you authorize us to charge the applicable fees to your selected payment method.'],
                            ['type' => 'subheading', 'text' => '4.3 Renewal'],
                            ['type' => 'paragraph', 'text' => 'Subscriptions must be renewed manually before the expiry date. You will receive renewal reminders before your subscription expires. We do not automatically charge your card after expiry.'],
                            ['type' => 'subheading', 'text' => '4.4 Refund Policy'],
                            ['type' => 'paragraph', 'text' => 'Subscription fees are generally non-refundable once paid. Refund requests may be considered at our sole discretion in cases of verified technical failures on our part.'],
                            ['type' => 'subheading', 'text' => '4.5 SIM Add-ons'],
                            ['type' => 'paragraph', 'text' => 'Additional SIM card slots may be purchased as add-ons subject to the pricing applicable at the time of purchase.'],
                            ['type' => 'subheading', 'text' => '4.6 Plan Downgrades / Upgrades'],
                            ['type' => 'paragraph', 'text' => 'Plan changes take effect on the next billing cycle unless otherwise stated. Access to features associated with a higher-tier plan will be removed if you downgrade.'],
                            ['type' => 'subheading', 'text' => '4.7 Feature Gating'],
                            ['type' => 'paragraph', 'text' => 'Certain features, including the Developer API, are exclusively available on the Advance Plan. Attempting to access gated features without the required plan will result in a 403 (Forbidden) response.'],
                            ['type' => 'subheading', 'text' => '4.8 Suspension for Non-Payment'],
                            ['type' => 'paragraph', 'text' => 'We reserve the right to suspend or limit access to your account if subscription fees remain unpaid after the expiry date.'],
                        ],
                    ],
                    [
                        'heading' => '5. Permitted Use of the Platform',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'The Platform is intended solely for legitimate business purposes, including monitoring and analyzing call activity of your own field agents and team members who have provided consent.'],
                            ['type' => 'paragraph', 'text' => 'You agree NOT to:'],
                            ['type' => 'list', 'items' => [
                                'Use the Platform to monitor or record calls without the knowledge and consent of all parties involved, where required by applicable law',
                                'Collect, upload, or process personal data of individuals who have not provided consent',
                                'Reverse-engineer, decompile, or attempt to extract source code from the Platform',
                                'Use the Developer API to build products that compete directly with Callytics without prior written consent',
                                'Share API tokens or credentials with unauthorized third parties',
                                'Use the Platform for any unlawful, fraudulent, or harmful purpose',
                                'Upload malicious code, viruses, or any content that interferes with Platform operations',
                                'Exceed API rate limits or attempt to circumvent rate limiting mechanisms',
                                'Impersonate another user, organization, or Callytics staff',
                            ]],
                            ['type' => 'paragraph', 'text' => 'We reserve the right to investigate suspected violations and suspend or terminate accounts found to be in breach of these Terms.'],
                        ],
                    ],
                    [
                        'heading' => '6. Mobile Application',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'The Callytics mobile application (Android) is provided to field agents assigned by their Organization. Agents log in using their mobile number and password.'],
                            ['type' => 'paragraph', 'text' => 'The mobile application collects and syncs the following data:'],
                            ['type' => 'list', 'items' => [
                                'Call log metadata: phone number, call duration, call type, timestamp',
                                'Contact names (only with explicit user permission)',
                                'Device information for troubleshooting purposes',
                            ]],
                            ['type' => 'paragraph', 'text' => 'Call log data is synced to the Callytics server automatically in the background after each call, using an Android Foreground Service that is visible to the user via a status notification.'],
                            ['type' => 'paragraph', 'text' => 'The app requires the following Android permissions:'],
                            ['type' => 'list', 'items' => [
                                'READ_CALL_LOG (required): to read call history',
                                'READ_PHONE_STATE (required): to detect call start/end events',
                                'READ_CONTACTS (optional): to display caller names',
                                'POST_NOTIFICATIONS: to show sync status notifications',
                            ]],
                            ['type' => 'paragraph', 'text' => 'Users may revoke permissions at any time through Android device settings. Revoking required permissions will disable call log sync functionality.'],
                            ['type' => 'paragraph', 'text' => 'The mobile application is intended for Android devices only. We do not currently provide an iOS application.'],
                        ],
                    ],
                    [
                        'heading' => '7. Developer API',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '7.1 Eligibility'],
                            ['type' => 'paragraph', 'text' => 'Access to the Developer API (/api/v1/org) is exclusively available to Organizations on the Advance Plan.'],
                            ['type' => 'subheading', 'text' => '7.2 API Tokens'],
                            ['type' => 'paragraph', 'text' => 'API access tokens are issued upon authentication via the /api/v1/org/auth/login endpoint. Tokens must be kept confidential. You are responsible for all API activity performed using your token.'],
                            ['type' => 'subheading', 'text' => '7.3 Rate Limiting'],
                            ['type' => 'paragraph', 'text' => 'API requests are subject to rate limits. Exceeding these limits will result in HTTP 429 (Too Many Requests) responses. We reserve the right to adjust rate limits at any time.'],
                            ['type' => 'subheading', 'text' => '7.4 Permitted Use'],
                            ['type' => 'paragraph', 'text' => 'The Developer API may be used to:'],
                            ['type' => 'list', 'items' => [
                                'Retrieve call log data for your Organization',
                                'Build internal tools, dashboards, or integrations for your own business',
                            ]],
                            ['type' => 'subheading', 'text' => '7.5 Prohibited API Use'],
                            ['type' => 'paragraph', 'text' => 'You may not use the API to:'],
                            ['type' => 'list', 'items' => [
                                'Access data of other organizations',
                                'Build unauthorized third-party products or services',
                                'Scrape or bulk-download data for purposes unrelated to your business',
                                'Circumvent plan-based access controls',
                            ]],
                            ['type' => 'subheading', 'text' => '7.6 API Availability'],
                            ['type' => 'paragraph', 'text' => 'We strive to maintain high API availability but do not guarantee uninterrupted access. Scheduled maintenance will be communicated in advance where possible.'],
                        ],
                    ],
                    [
                        'heading' => '8. Intellectual Property',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'All content, software, code, design, logos, and materials on the Platform are the intellectual property of Callytics and are protected by applicable copyright and intellectual property laws.'],
                            ['type' => 'paragraph', 'text' => 'We grant you a limited, non-exclusive, non-transferable license to access and use the Platform solely for your organization\'s internal business purposes in accordance with these Terms.'],
                            ['type' => 'paragraph', 'text' => 'You retain ownership of the Call Log Data uploaded to the Platform. By using the Platform, you grant us a limited license to store, process, and display this data solely to provide the Services to you.'],
                            ['type' => 'paragraph', 'text' => 'You must not reproduce, distribute, modify, or create derivative works of any Platform content without our prior written consent.'],
                        ],
                    ],
                    [
                        'heading' => '9. Data and Privacy',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'Our collection, use, and storage of personal data is governed by our Privacy Policy (provided as a separate document). By using the Platform, you consent to our Privacy Policy.'],
                            ['type' => 'paragraph', 'text' => 'You are responsible for ensuring that:'],
                            ['type' => 'list', 'items' => [
                                'Your field agents and team members are informed about and have consented to call log data being collected and synced via the mobile application',
                                'Your use of the Platform complies with applicable data protection laws in your jurisdiction',
                            ]],
                            ['type' => 'paragraph', 'text' => 'We implement industry-standard security measures including HTTPS encryption for all data in transit and secure server infrastructure for data at rest.'],
                            ['type' => 'paragraph', 'text' => 'We do not sell your data or your users\' data to third parties.'],
                            ['type' => 'paragraph', 'text' => 'In the event of a data breach that affects your data, we will notify you in accordance with applicable law.'],
                        ],
                    ],
                    [
                        'heading' => '10. Service Availability and Uptime',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'We aim to maintain high availability of the Platform but do not guarantee 100% uptime. The Platform is provided "as is" and "as available."'],
                            ['type' => 'paragraph', 'text' => 'We reserve the right to perform scheduled maintenance, upgrades, and changes to the Platform. We will provide advance notice for planned downtime where practicable.'],
                            ['type' => 'paragraph', 'text' => 'We are not liable for any losses arising from service interruptions, planned or unplanned.'],
                        ],
                    ],
                    [
                        'heading' => '11. Disclaimers and Limitations of Liability',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'The Platform is provided "as is" without warranties of any kind, either express or implied, including but not limited to warranties of merchantability, fitness for a particular purpose, or non-infringement.'],
                            ['type' => 'paragraph', 'text' => 'We do not warrant that:'],
                            ['type' => 'list', 'items' => [
                                'The Platform will meet all of your requirements',
                                'The Platform will be error-free, uninterrupted, or free of viruses',
                                'AI-generated call insights will be accurate in all cases',
                            ]],
                            ['type' => 'paragraph', 'text' => 'To the maximum extent permitted by law, Callytics shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, goodwill, or business opportunities, arising out of or in connection with your use of the Platform.'],
                            ['type' => 'paragraph', 'text' => 'Our total aggregate liability to you for any claims arising under these Terms shall not exceed the total fees paid by you to Callytics in the three (3) months immediately preceding the event giving rise to the claim.'],
                        ],
                    ],
                    [
                        'heading' => '12. Termination',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '12.1 By You'],
                            ['type' => 'paragraph', 'text' => 'You may terminate your account at any time by contacting us. Upon termination, your access to the Platform will cease and your data will be retained for 30 days before permanent deletion, unless required by law.'],
                            ['type' => 'subheading', 'text' => '12.2 By Us'],
                            ['type' => 'paragraph', 'text' => 'We may suspend or terminate your account immediately if:'],
                            ['type' => 'list', 'items' => [
                                'You breach any provision of these Terms',
                                'You engage in fraudulent or illegal activity',
                                'Your subscription expires and is not renewed within the grace period',
                                'We cease to offer the Platform',
                            ]],
                            ['type' => 'subheading', 'text' => '12.3 Effect of Termination'],
                            ['type' => 'paragraph', 'text' => 'On termination, your license to use the Platform ends immediately. We are not liable for any loss resulting from termination of your account in accordance with these Terms.'],
                        ],
                    ],
                    [
                        'heading' => '13. Indemnification',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'You agree to indemnify, defend, and hold harmless Callytics, its officers, employees, and partners from any claims, liabilities, damages, losses, and expenses (including legal fees) arising from:'],
                            ['type' => 'list', 'items' => [
                                'Your use of the Platform in violation of these Terms',
                                'Your violation of any applicable laws or regulations',
                                'Your breach of any third-party rights, including data privacy rights of your employees or agents',
                            ]],
                        ],
                    ],
                    [
                        'heading' => '14. Governing Law and Dispute Resolution',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'These Terms shall be governed by and construed in accordance with the laws of India, without regard to conflict of law principles.'],
                            ['type' => 'paragraph', 'text' => 'Any disputes arising out of or in connection with these Terms shall first be attempted to be resolved through good-faith negotiation. If unresolved within 30 days, disputes shall be subject to the exclusive jurisdiction of the courts located in India.'],
                        ],
                    ],
                    [
                        'heading' => '15. General Provisions',
                        'blocks'  => [
                            ['type' => 'subheading', 'text' => '15.1 Entire Agreement'],
                            ['type' => 'paragraph', 'text' => 'These Terms, together with our Privacy Policy, constitute the entire agreement between you and Callytics regarding your use of the Platform.'],
                            ['type' => 'subheading', 'text' => '15.2 Severability'],
                            ['type' => 'paragraph', 'text' => 'If any provision of these Terms is found to be unenforceable, the remaining provisions shall continue in full force and effect.'],
                            ['type' => 'subheading', 'text' => '15.3 Waiver'],
                            ['type' => 'paragraph', 'text' => 'Failure by us to enforce any provision of these Terms shall not be deemed a waiver of our right to do so in the future.'],
                            ['type' => 'subheading', 'text' => '15.4 Assignment'],
                            ['type' => 'paragraph', 'text' => 'You may not assign your rights or obligations under these Terms without our prior written consent. We may assign our rights and obligations at any time.'],
                            ['type' => 'subheading', 'text' => '15.5 Force Majeure'],
                            ['type' => 'paragraph', 'text' => 'We shall not be liable for any failure or delay in performance due to causes beyond our reasonable control, including natural disasters, internet outages, government actions, or third-party service failures.'],
                        ],
                    ],
                    [
                        'heading' => '16. Contact Us',
                        'blocks'  => [
                            ['type' => 'paragraph', 'text' => 'For questions about these Terms and Conditions, please contact us at:'],
                            ['type' => 'contact', 'email' => 'support@callytics.com'],
                        ],
                    ],
                ],
            ]
        );
    }
}
