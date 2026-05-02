<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    // -------------------------------------------------------------------------
    // Organization-facing endpoints (any authenticated org user)
    // -------------------------------------------------------------------------

    /**
     * GET /subscription/stats
     *
     * Returns the current plan and SIM usage stats for the authenticated user's
     * organization. Used by the dashboard to display plan info.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json([
                'message' => 'No organization linked to this account.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => SubscriptionService::getStats($organizationId),
        ]);
    }

    /**
     * GET /subscription/overview
     *
     * Organization-only subscription page payload. It returns the latest
     * subscription row so organizations can still see an expired plan's dates.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json([
                'message' => 'No organization linked to this account.',
            ], 404);
        }

        // Auto-expire stale active rows before presenting subscription data.
        SubscriptionService::getActiveSubscription($organizationId);

        $subscription = OrganizationSubscription::with([
                'plan:id,name,display_name,billing_type,price_per_sim,trial_days,features,is_active',
            ])
            ->select([
                'id',
                'organization_id',
                'plan_id',
                'billing_cycle',
                'sim_limit',
                'start_date',
                'end_date',
                'status',
                'created_at',
                'updated_at',
            ])
            ->where('organization_id', $organizationId)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'stats'        => SubscriptionService::getStats($organizationId),
                'subscription' => $subscription,
                'plan'         => $subscription?->plan,
            ],
        ]);
    }

    /**
     * GET /subscription/renewal-data
     *
     * Organization-only payload for the renewal quote page. This intentionally
     * stops at quote calculation; payment/submit flow will be added later.
     */
    public function renewalData(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json([
                'message' => 'No organization linked to this account.',
            ], 404);
        }

        // Keep subscription status fresh before using the latest subscription.
        SubscriptionService::getActiveSubscription($organizationId);

        $subscription = OrganizationSubscription::with([
                'plan:id,name,display_name,billing_type,price_per_sim,trial_days,features,is_active',
            ])
            ->select([
                'id',
                'organization_id',
                'plan_id',
                'billing_cycle',
                'sim_limit',
                'start_date',
                'end_date',
                'status',
                'created_at',
                'updated_at',
            ])
            ->where('organization_id', $organizationId)
            ->latest()
            ->first();

        $plans = Plan::where('is_active', true)
            ->select('id', 'name', 'display_name', 'billing_type', 'price_per_sim',
                     'trial_days', 'features')
            ->orderBy('price_per_sim')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'plans'        => $plans,
                'stats'        => SubscriptionService::getStats($organizationId),
                'subscription' => $subscription,
                'plan'         => $subscription?->plan,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Admin-facing endpoints
    // -------------------------------------------------------------------------

    /**
     * GET /admin/plans
     *
     * List all available plans (admin reference).
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::where('is_active', true)
            ->select('id', 'name', 'display_name', 'billing_type', 'price_per_sim',
                     'trial_days', 'features')
            ->orderBy('price_per_sim')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $plans,
        ]);
    }

    /**
     * GET /admin/subscriptions
     *
     * Paginated list of all organization subscriptions (admin view).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);

        $subscriptions = OrganizationSubscription::with(['organization:id,name,email', 'plan:id,name,display_name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('organization_id'), fn ($q) => $q->where('organization_id', $request->organization_id))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $subscriptions,
        ]);
    }

    /**
     * GET /admin/organizations/{organization}/subscription
     *
     * View subscription details (and SIM stats) for a specific organization.
     */
    public function show(Organization $organization): JsonResponse
    {
        $subscription = OrganizationSubscription::with('plan')
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        return response()->json([
            'success'      => true,
            'subscription' => $subscription,
            'stats'        => SubscriptionService::getStats($organization->id),
        ]);
    }

    /**
     * PUT /admin/organizations/{organization}/subscription
     *
     * Assign or replace a plan for an organization.
     *
     * Body:
     *   plan_id        int     (required)
     *   billing_cycle  string  monthly | yearly | trial  (required)
     *   sim_limit      int     required - purchased SIM quantity
     *   notes          string  optional — admin notes
     */
    public function assign(Request $request, Organization $organization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id'       => 'required|integer|exists:plans,id',
            'billing_cycle' => 'required|string|in:monthly,yearly,trial',
            'sim_limit'     => 'required|integer|min:1|max:10000',
            'notes'         => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $plan = Plan::findOrFail($request->plan_id);

        $subscription = SubscriptionService::assignPlan(
            organizationId:   $organization->id,
            plan:             $plan,
            billingCycle:     $request->billing_cycle,
            simLimit:         $request->sim_limit,
            notes:            $request->notes
        );

        return response()->json([
            'success'      => true,
            'message'      => "Plan \"{$plan->display_name}\" assigned to {$organization->name}.",
            'subscription' => $subscription->load('plan'),
            'stats'        => SubscriptionService::getStats($organization->id),
        ]);
    }

    /**
     * PATCH /admin/organizations/{organization}/subscription/sim-limit
     *
     * Adjust only the sim_limit of the active subscription without changing the plan.
     * Runs enforceSimLimit() if the new limit is lower than current active SIM count.
     *
     * Body:
     *   sim_limit  int  (required)
     */
    public function adjustSimLimit(Request $request, Organization $organization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sim_limit' => 'required|integer|min:0|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $subscription = OrganizationSubscription::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription found for this organization.',
            ], 404);
        }

        $subscription->update(['sim_limit' => $request->sim_limit]);

        // May deactivate excess SIMs if the new limit is lower.
        SubscriptionService::enforceSimLimit($organization->id);

        return response()->json([
            'success'      => true,
            'message'      => 'SIM limit updated.',
            'subscription' => $subscription->fresh()->load('plan'),
            'stats'        => SubscriptionService::getStats($organization->id),
        ]);
    }
}
