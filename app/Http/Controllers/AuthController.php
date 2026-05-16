<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::with(['organization', 'team'])->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            Log::warning('Failed web login attempt', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'ua'    => $request->userAgent(),
            ]);

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact administrator.',
            ], 403);
        }

        if ($user->role !== 'admin' && $user->organization_id) {
            if (! $user->organization) {
                return response()->json([
                    'message' => 'Organization not found. Please contact administrator.',
                ], 403);
            }

            if ($user->organization->status !== 'active') {
                return response()->json([
                    'message' => 'Your organization has been deactivated. Please contact administrator.',
                ], 403);
            }
        }

        // Revoke all existing tokens before issuing a new one (prevent token accumulation)
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('Successful web login', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        return response()->json([
            'user'    => $user,
            'token'   => $token,
            'message' => 'Login successful',
        ]);
    }

    /**
     * Register a new organization (role = organization).
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:organizations,email|unique:users,email',
            'mobile'                => ['required', 'digits:10'],
            'industry'              => 'required|string|max:100',
            'password'              => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Generate a unique cryptographically-random 8-char alphanumeric app login code
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(6)), 0, 8));
        } while (\App\Models\Organization::where('app_login_code', $code)->exists());

        $organization = \App\Models\Organization::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'mobile'         => $request->mobile,
            'industry'       => $request->industry,
            'password'       => Hash::make($request->password),
            'app_login_code' => $code,
            'status'         => 'active',
        ]);

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

        $user  = User::where('organization_id', $organization->id)->where('role', 'organization')->first();
        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('New organization registered', [
            'organization_id' => $organization->id,
            'email'           => $organization->email,
            'ip'              => $request->ip(),
        ]);

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

        return response()->json(['message' => 'Logout successful']);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['organization', 'team']);

        $dateFormat = 'Y-m-d';
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
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'profile' => $request->profile,
        ]);

        return response()->json(['user' => $user, 'message' => 'Profile updated successfully']);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password'          => 'required|string',
            'new_password'              => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            Log::warning('Failed password change attempt', [
                'user_id' => $user->id,
                'ip'      => $request->ip(),
            ]);

            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        // Revoke all other tokens after a password change (security best practice)
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
