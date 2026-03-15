<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SimVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * STEP 1:
     * Start SIM verification (after login)
     */
    public function start(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'subscription_id' => 'nullable|string'
        ]);

        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $verificationId = Str::random(12);
        $token = Str::upper(Str::random(6));  // VERIFY X8P3Q7

        SimVerification::create([
            'verification_id' => $verificationId,
            'mobile' => $request->mobile,
            'token_hash' => Hash::make($token),
            'expires_at' => Carbon::now()->addMinutes(2),
            'status' => 'pending',
            'subscription_id' => $request->subscription_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'SIM verification token generated',
            'data' => [
                'verification_id' => $verificationId,
                'token' => $token,
                'expires_in' => 120
            ]
        ]);
    }


    /**
     * STEP 2:
     * SMS provider webhook → receives SMS sent by device SIM
     */
    public function webhook(Request $request)
    {
        $request->validate([
            'from' => 'required|string',
            'message' => 'required|string'
        ]);

        // Extract token: "VERIFY XYZ123"
        preg_match('/VERIFY\s+([A-Z0-9]+)/', $request->message, $match);

        if (!isset($match[1])) {
            return response()->json(['status' => false, 'message' => 'Invalid message'], 400);
        }

        $receivedToken = $match[1];

        // Find active verification request
        $records = SimVerification::where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($records as $verification) {

            if (Hash::check($receivedToken, $verification->token_hash)) {

                // Ensure SIM sender == user's mobile number
                if ($this->normalize($verification->mobile) !== $this->normalize($request->from)) {
                    return response()->json(['status' => false, 'message' => 'Mobile mismatch'], 400);
                }

                // Update user
                $user = User::where('mobile', $verification->mobile)->first();
                if ($user) {
                    $user->sim_verified_at = now();
                    $user->sim_verified_mobile = $request->from;

                    // Save subscription_id selected by user
                    $user->sim_subscription_id = $verification->subscription_id;
                    $user->save();
                }

                // Mark verification successful
                $verification->update([
                    'status' => 'verified',
                    'sender_number' => $request->from
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'SIM verified successfully',
                    'verification_id' => $verification->verification_id
                ]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Invalid or expired token'], 400);
    }


    /**
     * STEP 3:
     * Client-side polling
     */
    public function status($verificationId)
    {
        $verification = SimVerification::where('verification_id', $verificationId)->first();

        return response()->json([
            'status' => true,
            'verified' => $verification && $verification->status === 'verified'
        ]);
    }


    /**
     * Normalize phone number for comparison
     */
    private function normalize($num)
    {
        return ltrim(preg_replace('/\D/', '', $num), '0');
    }
}
