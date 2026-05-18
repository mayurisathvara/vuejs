<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
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

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message'           => 'Please verify your email address before logging in.',
                'email_not_verified' => true,
                'email'             => $user->email,
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
                PasswordRule::min(8)
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

        // Generate name-prefixed code matching admin UI format: {3-LETTER-PREFIX}-XXXX-XXXX
        $chars     = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no I/O/1/0 to avoid confusion
        $nameClean = strtoupper(preg_replace('/[^a-zA-Z]/', '', $request->name));
        if (strlen($nameClean) >= 3) {
            $prefix = substr($nameClean, 0, 3);
        } elseif (strlen($nameClean) > 0) {
            $prefix = $nameClean;
            while (strlen($prefix) < 3) {
                $prefix .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } else {
            $prefix = 'ORG';
        }

        do {
            $raw = '';
            for ($i = 0; $i < 8; $i++) {
                $raw .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = $prefix . '-' . substr($raw, 0, 4) . '-' . substr($raw, 4, 4);
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

        $user = User::where('organization_id', $organization->id)->where('role', 'organization')->first();

        // Queue the verification email (non-blocking).
        \App\Jobs\SendVerificationEmail::dispatch($user);

        Log::info('New organization registered', [
            'organization_id' => $organization->id,
            'email'           => $organization->email,
            'ip'              => $request->ip(),
        ]);

        return response()->json([
            'message'               => 'Registration successful! Please check your email to verify your account.',
            'requires_verification' => true,
            'email'                 => $user->email,
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
                PasswordRule::min(8)
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

    // -------------------------------------------------------------------------
    // Email verification
    // -------------------------------------------------------------------------

    /**
     * GET /email/verify/{id}/{hash}  (signed web route)
     *
     * Validates the signed link, marks the user as verified, then redirects
     * back to the SPA login page with a status query param the Vue app can read.
     */
    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $appUrl = rtrim(config('app.url'), '/');

        if (! $request->hasValidSignature()) {
            $status = $request->has('expires') ? 'expired' : 'invalid';
            return redirect("{$appUrl}/verify-email?status={$status}");
        }

        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect("{$appUrl}/verify-email?status=invalid");
        }

        if ($user->hasVerifiedEmail()) {
            return redirect("{$appUrl}/login?verified=already");
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        Log::info('Email verified', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        return redirect("{$appUrl}/login?verified=1");
    }

    /**
     * POST /api/email/resend
     *
     * Re-send the verification email for an unverified account.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'If that email is registered, we sent a new verification link.']);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'This email is already verified. You can log in.'], 422);
        }

        \App\Jobs\SendVerificationEmail::dispatch($user);

        return response()->json(['message' => 'Verification email sent. Please check your inbox.']);
    }

    // -------------------------------------------------------------------------
    // Password reset
    // -------------------------------------------------------------------------

    /**
     * POST /api/forgot-password
     *
     * Generates a reset token and queues the email. Always returns 200 to
     * prevent email-enumeration attacks.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Password::createToken($user);
            \App\Jobs\SendPasswordResetEmail::dispatch($user->email, $token);

            Log::info('Password reset requested', [
                'email' => $user->email,
                'ip'    => $request->ip(),
            ]);
        }

        return response()->json([
            'message' => "If {$request->email} is registered, we've sent a password reset link.",
        ]);
    }

    /**
     * POST /api/reset-password
     *
     * Validates the token and updates the password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token'                 => 'required|string',
            'email'                 => 'required|email|max:255',
            'password'              => [
                'required',
                'confirmed',
                PasswordRule::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete(); // revoke all Sanctum tokens on password change
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Log::info('Password reset successfully', [
                'email' => $request->email,
                'ip'    => $request->ip(),
            ]);

            return response()->json(['message' => 'Password reset successfully. Please log in with your new password.']);
        }

        $errors = [
            Password::INVALID_TOKEN   => 'This reset link is invalid or has expired.',
            Password::INVALID_USER    => 'No account found with this email address.',
            Password::RESET_THROTTLED => 'Too many reset attempts. Please wait a moment before trying again.',
        ];

        return response()->json([
            'message' => $errors[$status] ?? 'Unable to reset password. Please request a new link.',
        ], 422);
    }
}
