<?php

namespace App\Http\Controllers\Api\V1\Organization;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Organization login — only users with role = "organization" are allowed.
     * Returns a Bearer token for use with the third-party Call Log API.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::with('organization')
            ->where('email', $request->email)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Only organization role users are permitted
        if ($user->role !== 'organization') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only organization accounts can use this API.',
            ], 403);
        }

        // User account must be active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact administrator.',
            ], 403);
        }

        // Organization itself must be active
        if (! $user->organization || $user->organization->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your organization has been deactivated. Please contact administrator.',
            ], 403);
        }

        // Organization's active plan must include the 'api' feature
        $stats    = SubscriptionService::getStats($user->organization->id);
        $features = $stats['features'] ?? [];

        if (! in_array('api', $features, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Your current plan does not include Developer API access. Please upgrade to the Advance plan.',
            ], 403);
        }

        // Revoke any previous org-api tokens to ensure one active token at a time
        $user->tokens()->where('name', 'org-api-token')->delete();

        $token = $user->createToken('org-api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'organization' => [
                    'id'   => $user->organization->id,
                    'name' => $user->organization->name,
                ],
            ],
        ]);
    }
}
