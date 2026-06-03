<?php

namespace App\Services;

use App\Models\OrganizationSubscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RazorpaySubscriptionService
{
    public function createOrder(int $organizationId, int $planId, int $simQuantity): array
    {
        $plan = $this->getPaidPlan($planId);
        $amount = $this->calculateAmount($plan, $simQuantity);
        $amountInPaise = (int) round($amount * 100);
        $currency = config('services.razorpay.currency', 'INR');

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountInPaise,
                'currency' => $currency,
                'receipt' => 'sub_'.$organizationId.'_'.Str::random(18),
                'payment_capture' => 1,
                'notes' => [
                    'organization_id' => (string) $organizationId,
                    'subscription_plan_id' => (string) $plan->id,
                    'sim_quantity' => (string) $simQuantity,
                    'billing_cycle' => $plan->billing_type,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Razorpay create order failed', [
                'organization_id' => $organizationId,
                'status'          => $response->status(),
                'body'            => $response->json(),
            ]);

            throw ValidationException::withMessages([
                'payment' => 'Unable to initiate payment. Please try again.',
            ]);
        }

        $order = $response->json();

        return [
            'key' => $this->key(),
            'order' => $order,
            'quote' => [
                'subscription_plan_id' => $plan->id,
                'plan_name' => $plan->display_name,
                'billing_cycle' => $plan->billing_type,
                'sim_quantity' => $simQuantity,
                'price_per_sim' => (float) $plan->price_per_sim,
                'amount' => $amount,
                'amount_in_paise' => $amountInPaise,
                'currency' => $currency,
            ],
        ];
    }

    public function verifyAndCreateSubscription(int $organizationId, array $payload): OrganizationSubscription
    {
        $this->assertValidSignature(
            $payload['razorpay_order_id'],
            $payload['razorpay_payment_id'],
            $payload['razorpay_signature']
        );

        $order = $this->fetchOrder($payload['razorpay_order_id']);
        $notes = $order['notes'] ?? [];
        $planId = (int) ($notes['subscription_plan_id'] ?? 0);
        $simQuantity = (int) ($notes['sim_quantity'] ?? 0);

        if ((int) ($notes['organization_id'] ?? 0) !== $organizationId || $planId < 1 || $simQuantity < 1) {
            throw ValidationException::withMessages([
                'razorpay_order_id' => 'Razorpay order does not match this organization.',
            ]);
        }

        $plan = $this->getPaidPlan($planId);
        $amount = $this->calculateAmount($plan, $simQuantity);
        $amountInPaise = (int) round($amount * 100);

        if ((int) ($order['amount'] ?? 0) !== $amountInPaise) {
            throw ValidationException::withMessages([
                'razorpay_order_id' => 'Razorpay order amount does not match the selected plan.',
            ]);
        }

        return DB::transaction(function () use ($organizationId, $plan, $simQuantity, $amount, $payload, $order) {
            $existing = OrganizationSubscription::where('razorpay_order_id', $payload['razorpay_order_id'])
                ->orWhere('razorpay_payment_id', $payload['razorpay_payment_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $today = Carbon::today();

            $latest = OrganizationSubscription::where('organization_id', $organizationId)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $latestExpiry = $latest?->end_date ? Carbon::parse($latest->end_date) : null;
            $isActiveTrial = $latest && $latest->status === 'active' && $latest->billing_cycle === 'trial';

            if ($isActiveTrial) {
                // Cancel the free trial immediately so the paid plan activates now
                $latest->update(['status' => 'cancelled']);
                Cache::forget("sub_active:{$organizationId}");
                $startDate = $today->copy();
                $status = 'active';
            } elseif ($latestExpiry && $latestExpiry->greaterThan($today)) {
                $startDate = $latestExpiry->copy()->addDay();
                $status = 'upcoming';
            } else {
                $startDate = $today->copy();
                $status = 'active';
            }

            $endDate = match ($plan->billing_type) {
                'monthly' => $startDate->copy()->addMonth(),
                'yearly' => $startDate->copy()->addYear(),
                default => throw ValidationException::withMessages([
                    'subscription_plan_id' => 'Only monthly and yearly plans can be renewed.',
                ]),
            };

            if ($status === 'active' && $this->hasOverlappingActivePlan($organizationId, $startDate, $endDate)) {
                throw ValidationException::withMessages([
                    'subscription' => 'An active subscription already overlaps this renewal period.',
                ]);
            }

            $subscription = OrganizationSubscription::create([
                'organization_id' => $organizationId,
                'plan_id' => $plan->id,
                'plan_name' => $plan->display_name,
                'price_per_sim' => $plan->price_per_sim,
                'billing_cycle' => $plan->billing_type,
                'sim_limit' => $simQuantity,
                'sim_quantity' => $simQuantity,
                'features' => $plan->features ?? [],
                'total_amount' => $amount,
                'amount' => $amount,
                'currency' => $order['currency'] ?? config('services.razorpay.currency', 'INR'),
                'razorpay_order_id' => $payload['razorpay_order_id'],
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature' => $payload['razorpay_signature'],
                'payment_status' => 'paid',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => $status,
                'transaction_payload' => [
                    'razorpay_order' => $order,
                    'razorpay_payment_id' => $payload['razorpay_payment_id'],
                    'verified_at' => now()->toISOString(),
                ],
            ]);

            if ($subscription->status === 'active') {
                SubscriptionService::enforceSimLimit($organizationId);
            }

            return $subscription->load('plan');
        });
    }

    public function syncLifecycle(): array
    {
        $today = Carbon::today();

        $expired = OrganizationSubscription::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->update(['status' => 'expired']);

        $activated = 0;

        OrganizationSubscription::where('status', 'upcoming')
            ->whereDate('start_date', '<=', $today)
            ->orderBy('start_date')
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) use (&$activated, $today) {
                foreach ($subscriptions as $subscription) {
                    DB::transaction(function () use ($subscription, &$activated, $today) {
                        $fresh = OrganizationSubscription::whereKey($subscription->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $fresh || $fresh->status !== 'upcoming') {
                            return;
                        }

                        OrganizationSubscription::where('organization_id', $fresh->organization_id)
                            ->where('status', 'active')
                            ->whereDate('end_date', '<', $today)
                            ->update(['status' => 'expired']);

                        $hasActiveOverlap = OrganizationSubscription::where('organization_id', $fresh->organization_id)
                            ->where('status', 'active')
                            ->whereDate('start_date', '<=', $fresh->end_date)
                            ->whereDate('end_date', '>=', $fresh->start_date)
                            ->exists();

                        if ($hasActiveOverlap) {
                            return;
                        }

                        $fresh->update(['status' => 'active']);
                        SubscriptionService::enforceSimLimit($fresh->organization_id);
                        $activated++;
                    });
                }
            });

        return [
            'expired' => $expired,
            'activated' => $activated,
        ];
    }

    private function getPaidPlan(int $planId): Plan
    {
        $plan = Plan::whereKey($planId)
            ->where('is_active', true)
            ->whereIn('billing_type', ['monthly', 'yearly'])
            ->first();

        if (! $plan) {
            throw ValidationException::withMessages([
                'subscription_plan_id' => 'Please select an active monthly or yearly plan.',
            ]);
        }

        return $plan;
    }

    private function calculateAmount(Plan $plan, int $simQuantity): float
    {
        $months = $plan->billing_type === 'yearly' ? 12 : 1;

        return round(((float) $plan->price_per_sim) * $simQuantity * $months, 2);
    }

    private function fetchOrder(string $orderId): array
    {
        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->get("https://api.razorpay.com/v1/orders/{$orderId}");

        if ($response->failed()) {
            Log::error('Razorpay fetch order failed', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->json(),
            ]);

            throw ValidationException::withMessages([
                'razorpay_order_id' => 'Unable to verify payment. Please contact support.',
            ]);
        }

        return $response->json();
    }

    private function assertValidSignature(string $orderId, string $paymentId, string $signature): void
    {
        $expected = hash_hmac('sha256', "{$orderId}|{$paymentId}", $this->secret());

        if (! hash_equals($expected, $signature)) {
            Log::warning('Razorpay signature verification failed (subscription)', [
                'order_id'   => $orderId,
                'payment_id' => $paymentId,
            ]);

            throw ValidationException::withMessages([
                'razorpay_signature' => 'Payment verification failed. Please contact support.',
            ]);
        }
    }

    private function hasOverlappingActivePlan(int $organizationId, Carbon $startDate, Carbon $endDate): bool
    {
        return OrganizationSubscription::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists();
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
