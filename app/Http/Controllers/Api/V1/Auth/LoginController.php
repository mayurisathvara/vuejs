<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Sim;

class LoginController extends Controller
{
    /**
     * Handle mobile app login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'app_login_code' => 'required|string',
        ]);

        $org = Organization::query()
            ->where('app_login_code', trim((string) $request->app_login_code))
            ->where('status', 'active')
            ->first();

        if (!$org) {
            return response()->json([
                'message' => 'Invalid organization code.'
            ], 401);
        }

        $sim = Sim::query()
            ->where('organization_id', $org->id)
            ->where('mobile', trim((string) $request->mobile))
            ->first();

        if (!$sim) {
            return response()->json([
                'message' => 'Invalid SIM mobile or organization code.'
            ], 401);
        }

        // Revoke previous tokens (single device login style)
        $sim->tokens()->delete();

        $token = $sim->createToken('sim-api-token')->plainTextToken;

        $sim->load(['organization', 'team']);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'sim' => $sim,
        ]);
    }

    /**
     * Handle logout request for authenticated mobile app user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ], 200);
    }
}
