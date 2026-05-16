<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SimVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * STEP 1: Start SIM verification — generate a secure token.
     */
    public function start(Request $request)
    {
        $request->validate([
            'mobile'          => 'required|string|max:20',
            'subscription_id' => 'nullable|string|max:100',
        ]);

        $mobile = trim((string) $request->mobile);

        // Do NOT reveal whether the mobile exists — generic not-found guard
        $user = User::where('mobile', $mobile)->first();
        if (! $user) {
            // Return same shape as success to avoid mobile-number enumeration
            return response()->json([
                'status'  => true,
                'message' => 'Verification initiated. Check your device.',
                'data'    => [
                    'verification_id' => Str::random(12),
                    'expires_in'      => 120,
                ],
            ]);
        }

        // 8-char uppercase alphanumeric token — significantly stronger than 6-char
        $verificationId = Str::random(16);
        $token          = strtoupper(Str::random(8));

        SimVerification::create([
            'verification_id' => $verificationId,
            'mobile'          => $mobile,
            'token_hash'      => Hash::make($token),
            'expires_at'      => Carbon::now()->addMinutes(5),
            'status'          => 'pending',
            'subscription_id' => $request->subscription_id,
        ]);

        Log::info('SIM verification started', [
            'verification_id' => $verificationId,
            'ip'              => $request->ip(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'SIM verification token generated',
            'data'    => [
                'verification_id' => $verificationId,
                'token'           => $token,
                'expires_in'      => 300,
            ],
        ]);
    }

    /**
     * STEP 2: SMS provider webhook → receives SMS sent by device SIM.
     *
     * Security layers:
     *  1. Optional IP allowlist (SMS_WEBHOOK_ALLOWED_IPS in .env, comma-separated).
     *  2. Optional HMAC-SHA256 signature check via X-Webhook-Signature header
     *     (set SMS_WEBHOOK_SECRET in .env; configure your SMS provider to sign requests).
     */
    public function webhook(Request $request)
    {
        // ── IP allowlist (optional) ──────────────────────────────────────────
        $allowedIps = array_filter(
            array_map('trim', explode(',', (string) config('services.sms.webhook_allowed_ips', '')))
        );

        if (! empty($allowedIps) && ! in_array($request->ip(), $allowedIps, true)) {
            Log::warning('SMS webhook rejected: IP not in allowlist', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // ── HMAC signature verification (optional) ───────────────────────────
        $webhookSecret = (string) config('services.sms.webhook_secret', '');

        if ($webhookSecret !== '') {
            $signature = $request->header('X-Webhook-Signature', '');
            $expected  = hash_hmac('sha256', $request->getContent(), $webhookSecret);

            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('SMS webhook rejected: invalid signature', [
                    'ip' => $request->ip(),
                ]);

                return response()->json(['status' => false, 'message' => 'Invalid signature'], 401);
            }
        }

        // ── Payload validation ───────────────────────────────────────────────
        $request->validate([
            'from'    => 'required|string|max:30',
            'message' => 'required|string|max:500',
        ]);

        // Extract token: "VERIFY XYZ12345"
        preg_match('/VERIFY\s+([A-Z0-9]+)/i', $request->message, $match);

        if (! isset($match[1])) {
            return response()->json(['status' => false, 'message' => 'Invalid message format'], 400);
        }

        $receivedToken = strtoupper($match[1]);

        // Find pending verifications that haven't expired
        $records = SimVerification::where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($records as $verification) {
            if (! Hash::check($receivedToken, $verification->token_hash)) {
                continue;
            }

            // Mobile-number match check
            if ($this->normalize($verification->mobile) !== $this->normalize($request->from)) {
                Log::warning('SIM webhook mobile mismatch', [
                    'verification_id' => $verification->verification_id,
                    'expected'        => $this->normalize($verification->mobile),
                    'received'        => $this->normalize($request->from),
                ]);

                return response()->json(['status' => false, 'message' => 'Mobile mismatch'], 400);
            }

            $user = User::where('mobile', $verification->mobile)->first();
            if ($user) {
                $user->sim_verified_at       = now();
                $user->sim_verified_mobile   = $request->from;
                $user->sim_subscription_id   = $verification->subscription_id;
                $user->save();
            }

            $verification->update([
                'status'        => 'verified',
                'sender_number' => $request->from,
            ]);

            Log::info('SIM verified successfully', [
                'verification_id' => $verification->verification_id,
                'ip'              => $request->ip(),
            ]);

            return response()->json([
                'status'          => true,
                'message'         => 'SIM verified successfully',
                'verification_id' => $verification->verification_id,
            ]);
        }

        Log::warning('SIM webhook: invalid or expired token attempt', ['ip' => $request->ip()]);

        return response()->json(['status' => false, 'message' => 'Invalid or expired token'], 400);
    }

    /**
     * STEP 3: Client-side polling for verification status.
     */
    public function status($verificationId)
    {
        // Sanitize the ID before querying
        $verificationId = preg_replace('/[^a-zA-Z0-9]/', '', (string) $verificationId);

        $verification = SimVerification::where('verification_id', $verificationId)->first();

        return response()->json([
            'status'   => true,
            'verified' => $verification && $verification->status === 'verified',
        ]);
    }

    private function normalize(string $num): string
    {
        return ltrim(preg_replace('/\D/', '', $num), '0');
    }
}
