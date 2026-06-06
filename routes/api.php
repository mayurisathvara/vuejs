<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\CallReportController;
use App\Http\Controllers\SummaryReportController;
use App\Http\Controllers\DashboardAnalyticsController;
use App\Http\Controllers\GeneratedReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ImpersonationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =========================================
// Web/VueJS API Routes (Existing Routes)
// =========================================

// Public routes — strict rate limits to prevent brute-force & credential stuffing
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Email verification resend — 3 per minute to prevent abuse
Route::middleware('throttle:3,1')->post('/email/resend', [AuthController::class, 'resendVerification']);

// Password reset — send link (3/min) and apply token (5/min)
Route::middleware('throttle:3,1')->post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::middleware('throttle:5,1')->post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile routes — moderate rate limit
    Route::middleware('throttle:30,1')->group(function () {
        Route::put('/user/profile', [AuthController::class, 'updateProfile']);
        Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    });

    // User management routes - Admin, Organization, Manager only
    Route::middleware(['role:admin,organization,manager'])->group(function () {
        Route::get('/users/organizations', [UserController::class, 'getOrganizations']);
        Route::get('/users/teams', [UserController::class, 'getTeamsByOrganization']);
        Route::put('/users/{user}/status', [UserController::class, 'updateStatus']);

        // Assign SIMs routes
        Route::get('/users/{user}/assign-sims', [UserController::class, 'getAssignSimsData']);
        Route::post('/users/sims/by-teams', [UserController::class, 'getSimsByTeams']);
        Route::get('/users/{user}/assigned-sims', [UserController::class, 'getAssignedSims']);
        Route::post('/users/{user}/assign-sims', [UserController::class, 'assignSims']);
        Route::get('/users/{user}/available-sims', [UserController::class, 'getAvailableSims']);

        Route::apiResource('users', UserController::class);
        Route::get('/users', [UserController::class, 'index']);
    });

    // Call Reports - All authenticated users
    Route::middleware(['role:admin,organization,manager,user'])->group(function () {
        Route::get('/call-reports/options', [CallReportController::class, 'options']);
        Route::get('/call-reports', [CallReportController::class, 'index']);
        Route::get('/call-reports/export', [CallReportController::class, 'export']);

        // Summary Report - All authenticated users
        Route::get('/summary-reports/options', [SummaryReportController::class, 'options']);
        Route::get('/summary-reports', [SummaryReportController::class, 'index']);
        Route::get('/summary-reports/export', [SummaryReportController::class, 'export']);

        // Dashboard analytics (Vue dashboard)
        Route::get('/dashboard/options', [DashboardAnalyticsController::class, 'options']);
        Route::get('/dashboard/summary', [DashboardAnalyticsController::class, 'summary']);
        Route::get('/dashboard/daily-call-volume', [DashboardAnalyticsController::class, 'dailyCallVolume']);
    });

    // Organization settings - Admin and Organization
    Route::middleware(['role:admin,organization'])->group(function () {
        Route::get('/organizations/{organization}/settings', [\App\Http\Controllers\OrganizationSettingController::class, 'show']);
        Route::put('/organizations/{organization}/settings', [\App\Http\Controllers\OrganizationSettingController::class, 'update']);
    });

    // Impersonation stop — any authenticated user (called with the org impersonation token)
    // Must be defined before the parameterized start route to prevent path ambiguity.
    Route::post('/admin/impersonate/stop', [ImpersonationController::class, 'stop']);

    // Organization management routes - Admin only
    Route::middleware(['role:admin'])->group(function () {
        // Impersonation start — admin creates a token for the target org's user account
        Route::post('/admin/impersonate/{organization}', [ImpersonationController::class, 'start']);

        Route::put('/organizations/{organization}/status', [OrganizationController::class, 'updateStatus']);
        Route::apiResource('organizations', OrganizationController::class);
        Route::get('/organizations', [OrganizationController::class, 'index']);
    });

    // Team management routes - Admin and Organization only
    Route::middleware(['role:admin,organization'])->group(function () {
        Route::get('/teams/organizations', [TeamController::class, 'getOrganizations']);
        Route::apiResource('teams', TeamController::class);
        Route::get('/teams', [TeamController::class, 'index']);
    });

    // Excluded Numbers - Admin, Organization, Manager
    Route::middleware(['role:admin,organization,manager'])->group(function () {
        Route::get('/excluded-numbers/organizations', [\App\Http\Controllers\ExcludedNumberController::class, 'getOrganizations']);
        Route::post('/excluded-numbers/import-csv', [\App\Http\Controllers\ExcludedNumberController::class, 'importCsv']);
        Route::apiResource('excluded-numbers', \App\Http\Controllers\ExcludedNumberController::class);
    });

    // SIM management routes - Admin and Organization only
    Route::middleware(['role:admin,organization'])->group(function () {
        Route::put('/sims/{sim}/status', [\App\Http\Controllers\SimController::class, 'updateStatus']);
        Route::post('/sims/swap', [\App\Http\Controllers\SimController::class, 'swap']);
        Route::get('/sims/teams/by-organization', [\App\Http\Controllers\SimController::class, 'getTeams']);
        Route::post('/sims/bulk-delete', [\App\Http\Controllers\SimController::class, 'bulkDelete']);
        Route::post('/sims/import-csv', [\App\Http\Controllers\SimController::class, 'importCsv']);
        Route::apiResource('sims', \App\Http\Controllers\SimController::class);
        Route::get('/sims', [\App\Http\Controllers\SimController::class, 'index']);
    });

    // Subscription stats — authenticated org/manager/user can view their own plan
    Route::middleware(['role:admin,organization,manager,user'])->group(function () {
        Route::get('/subscription/stats', [\App\Http\Controllers\SubscriptionController::class, 'stats']);
    });

    // Organization billing/subscription page — payment endpoints get strict rate limits
    Route::middleware(['role:organization'])->group(function () {
        Route::get('/subscription/overview', [\App\Http\Controllers\SubscriptionController::class, 'overview']);
        Route::get('/subscription/renewal-data', [\App\Http\Controllers\SubscriptionController::class, 'renewalData']);
        Route::get('/subscription/payments', [\App\Http\Controllers\SubscriptionController::class, 'paymentHistory']);
        Route::get('/subscription/invoices/{subscription}', [\App\Http\Controllers\SubscriptionController::class, 'invoiceView']);
        Route::get('/subscription/invoices/{subscription}/download', [\App\Http\Controllers\SubscriptionController::class, 'invoiceDownload']);
        Route::get('/subscription/addon-payments', [\App\Http\Controllers\SubscriptionController::class, 'addonPaymentHistory']);
        Route::get('/subscription/addon-invoices/{addon}', [\App\Http\Controllers\SubscriptionController::class, 'addonInvoiceView']);
        Route::get('/subscription/addon-invoices/{addon}/download', [\App\Http\Controllers\SubscriptionController::class, 'addonInvoiceDownload']);

        // Payment order creation — 10 attempts per minute per user
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/subscription/renew/order', [\App\Http\Controllers\SubscriptionController::class, 'createRenewalOrder']);
            Route::post('/subscription/addon-sim/order', [\App\Http\Controllers\SubscriptionController::class, 'createAddonOrder']);
        });

        // Payment verification — 5 attempts per minute (tighter; replaying is suspicious)
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('/subscription/renew/verify', [\App\Http\Controllers\SubscriptionController::class, 'verifyRenewalPayment']);
            Route::post('/subscription/addon-sim/verify', [\App\Http\Controllers\SubscriptionController::class, 'verifyAddonPayment']);
        });
    });

    // Notifications — all authenticated users
    Route::middleware(['role:admin,organization,manager,user'])->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    });

    // Generated reports — all authenticated users, throttled on queue endpoints
    Route::middleware(['role:admin,organization,manager,user'])->group(function () {
        Route::get('/generated-reports', [GeneratedReportController::class, 'index']);
        Route::get('/generated-reports/{report}/download', [GeneratedReportController::class, 'download']);
        Route::delete('/generated-reports/{report}', [GeneratedReportController::class, 'destroy']);

        Route::middleware('throttle:20,1')->group(function () {
            Route::post('/call-reports/queue-export', [GeneratedReportController::class, 'queueCallReport']);
            Route::post('/summary-reports/queue-export', [GeneratedReportController::class, 'queueSummaryReport']);
        });
    });

    // Admin subscription management
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('/admin/plans', \App\Http\Controllers\PlanController::class);
        Route::get('/admin/subscriptions', [\App\Http\Controllers\SubscriptionController::class, 'index']);
        Route::get('/admin/organizations/{organization}/subscription', [\App\Http\Controllers\SubscriptionController::class, 'show']);
        Route::put('/admin/organizations/{organization}/subscription', [\App\Http\Controllers\SubscriptionController::class, 'assign']);
        Route::patch('/admin/organizations/{organization}/subscription/sim-limit', [\App\Http\Controllers\SubscriptionController::class, 'adjustSimLimit']);
    });
});

// =========================================
// Third-Party Organization API - Version 1
// =========================================

Route::prefix('v1/org')->group(function () {

    // Public: organization login — strict throttle
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/login', [\App\Http\Controllers\Api\V1\Organization\AuthController::class, 'login']);
    });

    // Protected: require a valid Bearer token + organization role + api plan feature
    Route::middleware(['auth:sanctum', 'role:organization', 'plan.feature:api'])->group(function () {
        // Call logs — filtered to the authenticated organization automatically
        Route::get('/call-logs', [\App\Http\Controllers\Api\V1\Organization\CallLogController::class, 'index']);
    });
});

// =========================================
// Mobile API Routes - Version 1
// =========================================

Route::prefix('v1/app')->group(function () {

    // Public routes — strict throttle on login
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login']);
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'sim.auth'])->group(function () {
        // Auth routes
        Route::post('/logout', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'logout']);
        Route::post('/change-password', [\App\Http\Controllers\Api\V1\Auth\ChangePasswordController::class, 'changePassword']);

        // Profile routes
        Route::get('/profile', [\App\Http\Controllers\Api\V1\User\ProfileController::class, 'show']);

        // Call Log routes
        Route::post('/call-logs/push', [\App\Http\Controllers\Api\V1\CallLog\CallLogController::class, 'push']);
        Route::get('/call-logs', [\App\Http\Controllers\Api\V1\CallLog\CallLogController::class, 'list']);

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Dashboard\DashboardController::class, 'dashboard']);

        // Analytics
        Route::get('/analytics/call-type-distribution', [\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'callTypeDistribution']);
        Route::get('/analytics/daily-call-volume', [\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'dailyCallVolume']);
        Route::get('/analytics/peak-hours', [\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'peakHours']);
        Route::get('/analytics/missed-calls', [\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'missedCallsAnalysis']);

        // SIM Verification — OTP endpoints with dedicated throttle
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/sim/start', [\App\Http\Controllers\Api\V1\Auth\OtpController::class, 'start']);
            Route::get('/sim/status/{id}', [\App\Http\Controllers\Api\V1\Auth\OtpController::class, 'status']);
        });
    });

    // SIM webhook — separate from auth middleware, but tightly throttled
    Route::middleware('throttle:60,1')->post(
        '/sim/webhook',
        [\App\Http\Controllers\Api\V1\Auth\OtpController::class, 'webhook']
    );
});

