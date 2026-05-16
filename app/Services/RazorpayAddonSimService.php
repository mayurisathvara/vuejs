<?php

namespace App\Services;

use App\Models\AddonSimPurchase;
use App\Models\OrganizationSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RazorpayAddonSimService
{
    /**
     * Calculate proration and create a Razorpay order for add-on SIM purchase.
     */
    public function createAddonOrder(int $organizationId, int $simQuantity): array
    {
        $subscription = $this->getActiveSubscription($organizationId);
        $proration    = $this->calculateProration($subscription);

        if ($proration['remaining_days'] <= 0) {
            throw ValidationException::withMessages([
                'subscription' => 'Your subscription has no remaining days for add-on billing.',
            ]);
        }

        $pricePerSim     = (float) $subscription->price_per_sim;
        $prorationFactor = min(1.0, $proration['proration_factor']); // never charge more than one full cycle
        $proratedTotal   = round($pricePerSim * $simQuantity * $prorationFactor, 2);
        $amountInPaise = (int) round($proratedTotal * 100);

        // Razorpay requires a minimum of ₹1 (100 paise).
        if ($amountInPaise < 100) {
            $amountInPaise = 100;
            $proratedTotal = 1.00;
        }

        $currency = config('services.razorpay.currency', 'INR');

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount'          => $amountInPaise,
                'currency'        => $currency,
                'receipt'         => 'addon_'.$organizationId.'_'.Str::random(16),
                'payment_capture' => 1,
                'notes'           => [
                    'type'               => 'addon_sim',
                    'organization_id'    => (string) $organizationId,
                    'subscription_id'    => (string) $subscription->id,
                    'plan_id'            => (string) $subscription->plan_id,
                    'sim_quantity'       => (string) $simQuantity,
                    'remaining_days'     => (string) $proration['remaining_days'],
                    'billing_cycle_days' => (string) $proration['billing_cycle_days'],
                ],
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'payment' => $response->json('error.description') ?: 'Unable to create Razorpay order.',
            ]);
        }

        $order = $response->json();

        return [
            'key'   => $this->key(),
            'order' => $order,
            'quote' => [
                'type'               => 'addon_sim',
                'subscription_id'    => $subscription->id,
                'plan_name'          => $subscription->plan_name,
                'price_per_sim'      => $pricePerSim,
                'sim_quantity'       => $simQuantity,
                'billing_cycle_days' => $proration['billing_cycle_days'],
                'remaining_days'     => $proration['remaining_days'],
                'proration_factor'   => $proration['proration_factor'],
                'full_price'         => round($pricePerSim * $simQuantity, 2),
                'prorated_amount'    => $proratedTotal,
                'amount_in_paise'    => $amountInPaise,
                'currency'           => $currency,
            ],
        ];
    }

    /**
     * Verify signature, record the add-on purchase, and expand the subscription's SIM limit.
     */
    public function verifyAndActivateAddon(int $organizationId, array $payload): AddonSimPurchase
    {
        $this->assertValidSignature(
            $payload['razorpay_order_id'],
            $payload['razorpay_payment_id'],
            $payload['razorpay_signature']
        );

        $order = $this->fetchOrder($payload['razorpay_order_id']);
        $notes = $order['notes'] ?? [];

        if (
            ($notes['type'] ?? '') !== 'addon_sim' ||
            (int) ($notes['organization_id'] ?? 0) !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'razorpay_order_id' => 'This Razorpay order does not belong to an add-on SIM purchase.',
            ]);
        }

        $simQuantity      = (int) ($notes['sim_quantity'] ?? 0);
        $subscriptionId   = (int) ($notes['subscription_id'] ?? 0);
        $planId           = (int) ($notes['plan_id'] ?? 0);
        $remainingDays    = (int) ($notes['remaining_days'] ?? 0);
        $billingCycleDays = max(1, (int) ($notes['billing_cycle_days'] ?? 1));

        if ($simQuantity < 1 || $subscriptionId < 1) {
            throw ValidationException::withMessages([
                'razorpay_order_id' => 'Add-on order data is incomplete or invalid.',
            ]);
        }

        return DB::transaction(function () use (
            $organizationId, $payload, $order, $subscriptionId, $planId,
            $simQuantity, $remainingDays, $billingCycleDays
        ) {
            // Idempotency: return existing record if already processed.
            $existing = AddonSimPurchase::where('razorpay_order_id', $payload['razorpay_order_id'])
                ->orWhere('razorpay_payment_id', $payload['razorpay_payment_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $subscription = OrganizationSubscription::whereKey($subscriptionId)
                ->where('organization_id', $organizationId)
                ->lockForUpdate()
                ->firstOrFail();

            $pricePerSim     = (float) $subscription->price_per_sim;
            $prorationFactor = min(1.0, round($remainingDays / $billingCycleDays, 6));
            $proratedAmount  = round($pricePerSim * $simQuantity * $prorationFactor, 2);
            $actualAmount    = round(($order['amount'] ?? 0) / 100, 2);
            $currency        = $order['currency'] ?? config('services.razorpay.currency', 'INR');

            $addon = AddonSimPurchase::create([
                'organization_id'     => $organizationId,
                'subscription_id'     => $subscriptionId,
                'plan_id'             => $planId ?: $subscription->plan_id,
                'sim_quantity'        => $simQuantity,
                'price_per_sim'       => $pricePerSim,
                'billing_cycle_days'  => $billingCycleDays,
                'remaining_days'      => $remainingDays,
                'proration_factor'    => $prorationFactor,
                'prorated_amount'     => $proratedAmount,
                'amount'              => $actualAmount,
                'currency'            => $currency,
                'razorpay_order_id'   => $payload['razorpay_order_id'],
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature'  => $payload['razorpay_signature'],
                'payment_status'      => 'paid',
                'transaction_payload' => [
                    'razorpay_order'      => $order,
                    'razorpay_payment_id' => $payload['razorpay_payment_id'],
                    'verified_at'         => now()->toISOString(),
                ],
            ]);

            // Expand SIM capacity on the active subscription immediately.
            $subscription->increment('sim_limit', $simQuantity);

            return $addon->load(['subscription', 'plan']);
        });
    }

    /**
     * Calculate remaining and total billing days for proration.
     *
     * remaining_days = calendar days from today to end_date (exclusive of today's start).
     * billing_cycle_days = total days in the current subscription period.
     */
    private function calculateProration(OrganizationSubscription $subscription): array
    {
        $today     = Carbon::today();
        $startDate = Carbon::parse($subscription->start_date)->startOfDay();
        $endDate   = Carbon::parse($subscription->end_date)->startOfDay();

        $billingCycleDays = max(1, (int) abs($startDate->diffInDays($endDate)));

        $remainingDays = $today->gte($endDate)
            ? 0
            : (int) abs($today->diffInDays($endDate));

        $prorationFactor = min(1.0, round($remainingDays / $billingCycleDays, 6));

        return [
            'billing_cycle_days' => $billingCycleDays,
            'remaining_days'     => $remainingDays,
            'proration_factor'   => $prorationFactor,
            'start_date'         => $startDate->toDateString(),
            'end_date'           => $endDate->toDateString(),
        ];
    }

    private function getActiveSubscription(int $organizationId): OrganizationSubscription
    {
        $subscription = OrganizationSubscription::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereNotNull('price_per_sim')
            ->latest('id')
            ->first();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription' => 'No active subscription found. Please renew your plan before purchasing add-on SIMs.',
            ]);
        }

        return $subscription;
    }

    private function fetchOrder(string $orderId): array
    {
        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->get("https://api.razorpay.com/v1/orders/{$orderId}");

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'razorpay_order_id' => $response->json('error.description') ?: 'Unable to fetch Razorpay order.',
            ]);
        }

        return $response->json();
    }

    private function assertValidSignature(string $orderId, string $paymentId, string $signature): void
    {
        $expected = hash_hmac('sha256', "{$orderId}|{$paymentId}", $this->secret());

        if (! hash_equals($expected, $signature)) {
            throw ValidationException::withMessages([
                'razorpay_signature' => 'Invalid Razorpay payment signature.',
            ]);
        }
    }

    private function key(): string
    {
        $key = (string) config('services.razorpay.key');

        if ($key === '') {
            throw ValidationException::withMessages([
                'payment' => 'Razorpay key is not configured.',
            ]);
        }

        return $key;
    }

    private function secret(): string
    {
        $secret = (string) config('services.razorpay.secret');

        if ($secret === '') {
            throw ValidationException::withMessages([
                'payment' => 'Razorpay secret is not configured.',
            ]);
        }

        return $secret;
    }
}
