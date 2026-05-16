<?php

namespace App\Services;

use App\Models\OrganizationSubscription;
use App\Models\Plan;
use App\Models\Sim;

class SubscriptionService
{
    private const DEFAULT_TRIAL_SIM_LIMIT = 10;

    // -------------------------------------------------------------------------
    // Subscription queries
    // -------------------------------------------------------------------------

    /**
     * Return the single active subscription for the given organization,
     * auto-expiring it if its end_date has passed.
     */
    public static function getActiveSubscription(int $organizationId): ?OrganizationSubscription
    {
        $subscription = OrganizationSubscription::with('plan')
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($subscription && $subscription->end_date && $subscription->end_date->isPast()) {
            $subscription->update(['status' => 'expired']);
            return null;
        }

        return $subscription;
    }

    /**
     * True when the org has an active, non-expired subscription.
     */
    public static function isSubscriptionActive(int $organizationId): bool
    {
        return self::getActiveSubscription($organizationId) !== null;
    }

    // -------------------------------------------------------------------------
    // SIM count helpers (ONLY active SIMs count toward the limit)
    // -------------------------------------------------------------------------

    public static function getActiveSimCount(int $organizationId): int
    {
        return Sim::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();
    }

    public static function getSimLimit(int $organizationId): int
    {
        $subscription = self::getActiveSubscription($organizationId);
        return $subscription?->sim_limit ?? 0;
    }

    /**
     * True when the org can still add or activate a SIM.
     * Requires an active (non-expired) subscription AND available slots.
     */
    public static function canAddOrActivateSim(int $organizationId): bool
    {
        if (! self::isSubscriptionActive($organizationId)) {
            return false;
        }

        return self::getActiveSimCount($organizationId) < self::getSimLimit($organizationId);
    }

    // -------------------------------------------------------------------------
    // Plan assignment
    // -------------------------------------------------------------------------

    /**
     * Snapshot plan pricing/features onto the subscription row.
     */
    private static function planSnapshot(Plan $plan, int $simQuantity): array
    {
        $pricePerSim = (float) $plan->price_per_sim;

        return [
            'plan_name'     => $plan->display_name,
            'price_per_sim' => $pricePerSim,
            'sim_quantity'  => $simQuantity,
            'features'      => $plan->features ?? [],
            'total_amount'  => $pricePerSim * $simQuantity,
        ];
    }

    /**
     * Create a Free Trial subscription for a freshly created organization.
     * Safe to call even if the plans table does not yet exist (during migrations).
     */
    public static function assignTrialPlan(int $organizationId): ?OrganizationSubscription
    {
        $trialPlan = Plan::where('name', 'free_trial')->first();

        if (! $trialPlan) {
            return null; // Plans not seeded yet (e.g. first migration run)
        }

        return OrganizationSubscription::create([
            'organization_id' => $organizationId,
            'plan_id'         => $trialPlan->id,
            'billing_cycle'   => 'trial',
            'sim_limit'       => self::DEFAULT_TRIAL_SIM_LIMIT,
            'start_date'      => now()->toDateString(),
            'end_date'        => now()->addDays($trialPlan->trial_days)->toDateString(),
            'status'          => 'active',
        ] + self::planSnapshot($trialPlan, self::DEFAULT_TRIAL_SIM_LIMIT));
    }

    /**
     * Assign (or replace) a plan for an organization.
     *
     * The previous active subscription is cancelled; a new one is created.
     * If the new sim_limit is lower than the current active SIM count,
     * enforceSimLimit() automatically deactivates excess SIMs (newest first).
     *
     * @param  int         $organizationId
     * @param  Plan        $plan
     * @param  string      $billingCycle  monthly | yearly | trial
     * @param  int         $simLimit  purchased SIM quantity for this subscription
     * @param  string|null $notes
     */
    public static function assignPlan(
        int $organizationId,
        Plan $plan,
        string $billingCycle = 'monthly',
        int $simLimit = self::DEFAULT_TRIAL_SIM_LIMIT,
        ?string $notes = null
    ): OrganizationSubscription {
        // Cancel current active subscription (if any)
        OrganizationSubscription::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $endDate = match ($billingCycle) {
            'monthly' => now()->addMonth()->toDateString(),
            'yearly'  => now()->addYear()->toDateString(),
            'trial'   => $plan->trial_days
                             ? now()->addDays($plan->trial_days)->toDateString()
                             : now()->addDays(14)->toDateString(),
            default   => null,
        };

        $subscription = OrganizationSubscription::create([
            'organization_id' => $organizationId,
            'plan_id'         => $plan->id,
            'billing_cycle'   => $billingCycle,
            'sim_limit'       => $simLimit,
            'start_date'      => now()->toDateString(),
            'end_date'        => $endDate,
            'status'          => 'active',
            'notes'           => $notes,
        ] + self::planSnapshot($plan, $simLimit));

        // Auto-deactivate excess SIMs when the new limit is lower
        self::enforceSimLimit($organizationId);

        return $subscription;
    }

    // -------------------------------------------------------------------------
    // Downgrade / limit enforcement
    // -------------------------------------------------------------------------

    /**
     * Deactivate excess active SIMs so the count matches the current plan limit.
     * Newest SIMs (highest ID) are deactivated first to preserve older SIM data.
     * Status is set to 'inactive_plan_limit' so the system can distinguish these
     * from manually deactivated SIMs.
     */
    public static function enforceSimLimit(int $organizationId): void
    {
        $limit       = self::getSimLimit($organizationId);
        $activeCount = self::getActiveSimCount($organizationId);

        if ($activeCount <= $limit) {
            return;
        }

        $excess = $activeCount - $limit;

        Sim::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('id', 'desc') // newest first
            ->limit($excess)
            ->get()
            ->each(fn (Sim $sim) => $sim->update(['status' => 'inactive_plan_limit']));
    }

    // -------------------------------------------------------------------------
    // Statistics (used by dashboard and admin views)
    // -------------------------------------------------------------------------

    /**
     * Return a fully resolved stats array for the given organization.
     * All modules should call this instead of duplicating the logic.
     */
    public static function getStats(int $organizationId): array
    {
        $subscription = self::getActiveSubscription($organizationId);
        $simLimit     = $subscription?->sim_limit ?? 0;
        $activeCount  = self::getActiveSimCount($organizationId);

        $inactiveCount = Sim::where('organization_id', $organizationId)
            ->whereIn('status', ['inactive', 'inactive_plan_limit'])
            ->count();

        $daysUntilExpiry = null;
        if ($subscription?->end_date) {
            $daysUntilExpiry = (int) now()->startOfDay()->diffInDays(
                $subscription->end_date->copy()->startOfDay(),
                false
            );
        }

        return [
            'plan_name'           => $subscription?->plan_name ?? $subscription?->plan?->display_name ?? 'No Plan',
            'plan_slug'           => $subscription?->plan?->name ?? null,
            'billing_cycle'       => $subscription?->billing_cycle,
            'sim_limit'           => $simLimit,
            'sim_quantity'        => $subscription?->sim_quantity ?? $simLimit,
            'price_per_sim'       => $subscription?->price_per_sim,
            'total_amount'        => $subscription?->total_amount,
            'active_sims'         => $activeCount,
            'inactive_sims'       => $inactiveCount,
            'remaining_slots'     => max(0, $simLimit - $activeCount),
            'subscription_status' => $subscription?->status ?? 'none',
            'start_date'          => $subscription?->start_date?->toDateString(),
            'end_date'            => $subscription?->end_date?->toDateString(),
            'auto_renew'          => $subscription?->auto_renew ?? true,
            'days_until_expiry'   => $daysUntilExpiry,
            'features'            => $subscription?->features ?? $subscription?->plan?->features ?? [],
        ];
    }

    // -------------------------------------------------------------------------
    // Active-SIM list (used for the manual swap UI)
    // -------------------------------------------------------------------------

    /**
     * Return the active SIMs for the given organization (id, name, mobile).
     * Used when the frontend needs to display "which SIM do you want to deactivate?"
     */
    public static function getActiveSims(int $organizationId): \Illuminate\Support\Collection
    {
        return Sim::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->select('id', 'name', 'mobile')
            ->orderBy('created_at')
            ->get();
    }
}
