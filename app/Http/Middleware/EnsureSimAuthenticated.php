<?php

namespace App\Http\Middleware;

use App\Models\Sim;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSimAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof Sim) {
            return response()->json([
                'message' => 'Unauthorized. SIM authentication is required.'
            ], 403);
        }

        // If the organization's plan has expired, revoke the token and force logout
        if (! SubscriptionService::isSubscriptionActive($user->organization_id)) {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Your organization\'s plan has expired. Please contact your administrator to renew.',
                'error'   => 'plan_expired',
            ], 401);
        }

        return $next($request);
    }
}
