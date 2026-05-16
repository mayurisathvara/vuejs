<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;
use App\Models\Sim;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'mobile'         => 'required|string|max:20',
            'app_login_code' => 'required|string|max:20',
        ]);

        $mobile   = trim((string) $request->mobile);
        $code     = trim((string) $request->app_login_code);

        // Resolve org first, then SIM — use a single generic error to prevent
        // enumeration of valid org codes or mobile numbers (IDOR prevention).
        $org = Organization::where('app_login_code', $code)
            ->where('status', 'active')
            ->first();

        $sim = $org
            ? Sim::where('organization_id', $org->id)->where('mobile', $mobile)->first()
            : null;

        if (! $org || ! $sim) {
            Log::warning('Failed mobile app login', [
                'mobile' => $mobile,
                'ip'     => $request->ip(),
                'ua'     => $request->userAgent(),
            ]);

            // Generic message — do NOT reveal which field was wrong
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($sim->status !== 'active') {
            return response()->json([
                'message' => 'Your SIM is inactive. Please contact your administrator.',
            ], 403);
        }

        // Single-device: revoke all previous tokens before issuing a new one
        $sim->tokens()->delete();
        $token = $sim->createToken('sim-api-token')->plainTextToken;

        $sim->load(['organization', 'team']);

        Log::info('Successful mobile app login', [
            'sim_id'          => $sim->id,
            'organization_id' => $sim->organization_id,
            'ip'              => $request->ip(),
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'sim'          => $sim,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}
