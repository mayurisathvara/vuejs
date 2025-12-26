<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CallReportController;
use App\Http\Controllers\DashboardAnalyticsController;

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

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Profile routes
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    
    // User management routes - Admin, Organization, Manager only
    Route::middleware(['role:admin,organization,manager'])->group(function () {
        Route::get('/users/organizations', [UserController::class, 'getOrganizations']);
        Route::get('/users/departments', [UserController::class, 'getDepartmentsByOrganization']);
        Route::put('/users/{user}/status', [UserController::class, 'updateStatus']);
        
        // Assign SIMs routes
        Route::get('/users/{user}/assign-sims', [UserController::class, 'getAssignSimsData']);
        Route::post('/users/sims/by-departments', [UserController::class, 'getSimsByDepartments']);
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

        // Dashboard analytics (Vue dashboard)
        Route::get('/dashboard/options', [DashboardAnalyticsController::class, 'options']);
        Route::get('/dashboard/summary', [DashboardAnalyticsController::class, 'summary']);
        Route::get('/dashboard/daily-call-volume', [DashboardAnalyticsController::class, 'dailyCallVolume']);
    });
    
    // Organization management routes - Admin only
    Route::middleware(['role:admin'])->group(function () {
        Route::put('/organizations/{organization}/status', [OrganizationController::class, 'updateStatus']);
        Route::apiResource('organizations', OrganizationController::class);
        Route::get('/organizations', [OrganizationController::class, 'index']);
    });
    
    // Department management routes - Admin and Organization only
    Route::middleware(['role:admin,organization'])->group(function () {
        Route::get('/departments/organizations', [DepartmentController::class, 'getOrganizations']);
        Route::apiResource('departments', DepartmentController::class);
        Route::get('/departments', [DepartmentController::class, 'index']);
    });
    
    // SIM management routes - Admin and Organization only
    Route::middleware(['role:admin,organization'])->group(function () {
        Route::get('/sims/departments/by-organization', [\App\Http\Controllers\SimController::class, 'getDepartments']);
        Route::post('/sims/bulk-delete', [\App\Http\Controllers\SimController::class, 'bulkDelete']);
        Route::post('/sims/import-csv', [\App\Http\Controllers\SimController::class, 'importCsv']);
        Route::apiResource('sims', \App\Http\Controllers\SimController::class);
        Route::get('/sims', [\App\Http\Controllers\SimController::class, 'index']);
    });
});

// =========================================
// Mobile API Routes - Version 1
// =========================================

Route::prefix('v1/app')->group(function () {
    
    // Public routes
    Route::post('/login', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/logout', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'logout']);
        Route::post('/change-password', [\App\Http\Controllers\Api\V1\Auth\ChangePasswordController::class, 'changePassword']);
        
        // Profile routes
        Route::get('/profile', [\App\Http\Controllers\Api\V1\User\ProfileController::class, 'show']);
        
        // Call Log routes
        Route::post('/call-logs/push',[\App\Http\Controllers\Api\V1\CallLog\CallLogController::class, 'push']);
		Route::get('/call-logs',[\App\Http\Controllers\Api\V1\CallLog\CallLogController::class, 'list']);
		
		//Dashboard 
		Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Dashboard\DashboardController::class, 'dashboard']);
		
		//Analytics
		Route::get('/analytics/call-type-distribution', [\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'callTypeDistribution']);
		Route::get('/analytics/daily-call-volume',[\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'dailyCallVolume']);
		Route::get('/analytics/peak-hours',[\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'peakHours']);
		Route::get('/analytics/missed-calls',[\App\Http\Controllers\Api\V1\Analytics\AnalyticsController::class, 'missedCallsAnalysis']);
		
		// SIM Verification Routes
		Route::post('/sim/start', [\App\Http\Controllers\Api\V1\Auth\OtpController::class, 'start']);
		Route::post('/sim/webhook', [\App\Http\Controllers\Api\V1\Auth\OtpController::class, 'webhook']);
		Route::get('/sim/status/{id}', [\App\Http\Controllers\Api\V1\Auth\OtpController::class, 'status']);



		
    });
    
});



//Mobile Application Routes