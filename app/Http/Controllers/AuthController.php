<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::with(['organization', 'team'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user account is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact administrator.'
            ], 403);
        }

        // Check organization status for non-admin users
        if ($user->role !== 'admin' && $user->organization_id) {
            if (!$user->organization) {
                return response()->json([
                    'message' => 'Organization not found. Please contact administrator.'
                ], 403);
            }

            if ($user->organization->status !== 'active') {
                return response()->json([
                    'message' => 'Your organization has been deactivated. Please contact administrator.'
                ], 403);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    /**
     * Register a new organization (role = organization).
     * Creating an Organization automatically:
     *  - creates the linked User with role=organization  (via Organization::boot)
     *  - assigns the 14-day Free Trial plan             (via Organization::boot)
     *  - creates the OrganizationSetting row
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:organizations,email|unique:users,email',
            'mobile'                => 'required|string|max:20',
            'industry'              => 'required|string|max:100',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Generate a unique numeric app login code
        do {
            $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\Organization::where('app_login_code', $code)->exists());

        // Creating the Organization triggers boot():
        //   → syncUser()  → creates User with role=organization
        //   → assignTrialPlan() → creates 14-day free trial subscription
        $organization = \App\Models\Organization::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'mobile'         => $request->mobile,
            'industry'       => $request->industry,
            'password'       => Hash::make($request->password),
            'app_login_code' => $code,
            'status'         => 'active',
        ]);

        // Create default organization settings
        \App\Models\OrganizationSetting::firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'callback_window_hours' => 48,
                'date_formate'          => 'Y-m-d',
                'enable_manager_role'   => false,
                'enable_working_hours'  => false,
                'working_hours'         => null,
            ]
        );

        // Retrieve the auto-created user
        $user = User::where('organization_id', $organization->id)
            ->where('role', 'organization')
            ->first();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'    => $user->load('organization'),
            'token'   => $token,
            'message' => 'Registration successful. Your 14-day free trial has started.',
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['organization', 'team']);
        
        // Get organization date format
        $dateFormat = 'Y-m-d'; // default
        if ($user->organization_id && $user->organization) {
            $orgSetting = \App\Models\OrganizationSetting::where('organization_id', $user->organization_id)->first();
            if ($orgSetting && $orgSetting->date_formate) {
                $dateFormat = $orgSetting->date_formate;
            }
        }
        $user->date_format = $dateFormat;
        
        return response()->json($user);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,manager,user',
            'organization_id' => 'nullable|string|max:255',
            'profile' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'organization_id' => $request->organization_id,
            'profile' => $request->profile,
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
}
