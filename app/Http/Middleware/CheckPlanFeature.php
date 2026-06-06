<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        $user = $request->user();

        if (! $user?->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'No organization associated with this account.',
            ], 403);
        }

        $stats    = SubscriptionService::getStats((int) $user->organization_id);
        $features = $stats['features'] ?? [];

        if (! in_array($feature, $features, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Your current plan does not include access to this feature.',
            ], 403);
        }

        return $next($request);
    }
}
