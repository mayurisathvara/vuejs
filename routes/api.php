<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\DepartmentController;

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
    
    // User management routes
    Route::get('/users/organizations', [UserController::class, 'getOrganizations']);
    Route::get('/users/departments', [UserController::class, 'getDepartmentsByOrganization']);
    Route::put('/users/{user}/status', [UserController::class, 'updateStatus']);
    Route::apiResource('users', UserController::class);
    Route::get('/users', [UserController::class, 'index']);
    
    // Organization management routes
    Route::put('/organizations/{organization}/status', [OrganizationController::class, 'updateStatus']);
    Route::apiResource('organizations', OrganizationController::class);
    Route::get('/organizations', [OrganizationController::class, 'index']);
    
    // Department management routes
    Route::get('/departments/organizations', [DepartmentController::class, 'getOrganizations']);
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/departments', [DepartmentController::class, 'index']);
});
