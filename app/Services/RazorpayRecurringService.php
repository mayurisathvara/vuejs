<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RazorpayRecurringService
{
    // ---------------------------------------------------------------------------
    // Customer management
    // ---------------------------------------------------------------------------

    /**
     * Return the existing Razorpay customer ID or create a new customer and
     * persist it on the organization.
     */
    public function ensureCustomer(Organization $organization): string
    {
        if ($organization->razorpay_customer_id) {
            return $organization->razorpay_customer_id;
        }

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/customers', [
                'name'    => $organization->name,
                'email'   => $organization->email,
                'contact' => $organization->mobile ?? '',
                'fail_existing' => '0', // return existing customer if e-mail is duplicate
            ]);

        if ($response->failed()) {
            Log::error('Razorpay create customer failed', [
                'organization_id' => $organization->id,
                'status'          => $response->status(),
                'body'            => $response->json(),
            ]);
            throw ValidationException::withMessages([
                'payment' => 'Unable to create Razorpay customer. Please try again.',
            ]);
        }

        $customerId = $response->json('id');
        $organization->update(['razorpay_customer_id' => $customerId]);

        return $customerId;
    }

    // ---------------------------------------------------------------------------
    // Razorpay Plan management
    // ---------------------------------------------------------------------------

    /**
     * Fetch or create a Razorpay Plan for the given app Plan + SIM quantity.
     * Razorpay Plans are immutable, so we create one per unique interval/amount
     * combo and store its ID in the plan notes so we can look it up next time.
     */
    public function getOrCreateRazorpayPlan(Plan $plan, int $simQuantity): string
    {
        $amount     = (int) round((float) $plan->price_per_sim * $simQuantity * 100); // paise
        $interval   = $plan->billing_type === 'yearly' ? 12 : 1;
        $periodUnit = 'monthly'; // Razorpay only supports monthly period; we use interval=12 for yearly

        // Check for cached Razorpay plan ID stored in plan notes
        $cacheKey   = "rzp_plan_{$plan->id}_{$simQuantity}";
        $cachedId   = cache()->get($cacheKey);
        if ($cachedId) {
            return $cachedId;
        }

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/plans', [
                'period'   => $periodUnit,
                'interval' => $interval,
                'item'     => [
                    'name'     => "{$plan->display_name} – {$simQuantity} SIM(s)",
                    'amount'   => $amount,
                    'currency' => config('services.razorpay.currency', 'INR'),
                ],
                'notes' => [
                    'app_plan_id'  => (string) $plan->id,
                    'sim_quantity' => (string) $simQuantity,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Razorpay create plan failed', [
                'plan_id'      => $plan->id,
                'sim_quantity' => $simQuantity,
                'status'       => $response->status(),
                'body'         => $response->json(),
            ]);
            throw ValidationException::withMessages([
                'payment' => 'Unable to create recurring plan. Please try again.',
            ]);
        }

        $razorpayPlanId = $response->json('id');
        cache()->put($cacheKey, $razorpayPlanId, now()->addDays(30));

        return $razorpayPlanId;
    }

    // ---------------------------------------------------------------------------
    // Razorpay Subscription management
    // ---------------------------------------------------------------------------

    /**
     * Create a Razorpay Subscription for the given plan/SIM combination and
     * return the raw Razorpay subscription object + the Razorpay key so the
     * frontend can open the Razorpay checkout.
     */
    public function createSubscription(
        Organization $organization,
        Plan $plan,
        int $simQuantity
    ): array {
        $customerId     = $this->ensureCustomer($organization);
        $razorpayPlanId = $this->getOrCreateRazorpayPlan($plan, $simQuantity);

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/subscriptions', [
                'plan_id'         => $razorpayPlanId,
                'total_count'     => 120, // allow up to 120 billing cycles (~10 years)
                'quantity'        => 1,
                'customer_notify' => 1,
                'notes'           => [
                    'organization_id' => (string) $organization->id,
                    'app_plan_id'     => (string) $plan->id,
                    'sim_quantity'    => (string) $simQuantity,
                ],
                'customer_id' => $customerId,
            ]);

        if ($response->failed()) {
            Log::error('Razorpay create subscription failed', [
                'organization_id' => $organization->id,
                'plan_id'         => $plan->id,
                'sim_quantity'    => $simQuantity,
                'status'          => $response->status(),
                'body'            => $response->json(),
            ]);
            throw ValidationException::withMessages([
                'payment' => 'Unable to create recurring subscription. Please try again.',
            ]);
        }

        return [
            'key'          => $this->key(),
            'subscription' => $response->json(),
            'quote' => [
                'type'            => 'recurring',
                'app_plan_id'     => $plan->id,
                'plan_name'       => $plan->display_name,
                'billing_cycle'   => $plan->billing_type,
                'sim_quantity'    => $simQuantity,
                'price_per_sim'   => (float) $plan->price_per_sim,
                'amount'          => round((float) $plan->price_per_sim * $simQuantity, 2),
                'currency'        => config('services.razorpay.currency', 'INR'),
            ],
        ];
    }

    /**
     * Cancel an active Razorpay subscription (cancel at end of billing cycle).
     */
    public function cancelSubscription(string $razorpaySubscriptionId, bool $immediately = false): void
    {
        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->post("https://api.razorpay.com/v1/subscriptions/{$razorpaySubscriptionId}/cancel", [
                'cancel_at_cycle_end' => $immediately ? 0 : 1,
            ]);

        if ($response->failed()) {
            Log::error('Razorpay cancel subscription failed', [
                'razorpay_subscription_id' => $razorpaySubscriptionId,
                'status'                   => $response->status(),
                'body'                     => $response->json(),
            ]);
            // Non-fatal — log and continue; the subscription may already be cancelled
        }
    }

    // ---------------------------------------------------------------------------
    // Signature verification (subscription checkout)
    // ---------------------------------------------------------------------------

    /**
     * Verify the Razorpay signature returned after subscription payment.
     * Format: HMAC-SHA256("{subscription_id}|{payment_id}", secret)
     */
    public function verifySignature(string $subscriptionId, string $paymentId, string $signature): void
    {
        $expected = hash_hmac('sha256', "{$subscriptionId}|{$paymentId}", $this->secret());

        if (! hash_equals($expected, $signature)) {
            Log::warning('Razorpay recurring signature verification failed', [
                'subscription_id' => $subscriptionId,
                'payment_id'      => $paymentId,
            ]);
            throw ValidationException::withMessages([
                'razorpay_signature' => 'Payment verification failed. Please contact support.',
            ]);
        }
    }

    // ---------------------------------------------------------------------------
    // Webhook signature verification
    // ---------------------------------------------------------------------------

    /**
     * Verify the Razorpay webhook signature.
     * Header: X-Razorpay-Signature
     * Secret: RAZORPAY_WEBHOOK_SECRET env var
     */
    public function verifyWebhookSignature(string $rawBody, string $headerSignature): bool
    {
        $secret   = (string) config('services.razorpay.webhook_secret');
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $headerSignature);
    }

    // ---------------------------------------------------------------------------
    // Fetch Razorpay subscription details
    // ---------------------------------------------------------------------------

    public function fetchSubscription(string $subscriptionId): array
    {
        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->get("https://api.razorpay.com/v1/subscriptions/{$subscriptionId}");

        if ($response->failed()) {
            Log::error('Razorpay fetch subscription failed', [
                'subscription_id' => $subscriptionId,
                'status'          => $response->status(),
                'body'            => $response->json(),
            ]);
            throw ValidationException::withMessages([
                'razorpay_subscription_id' => 'Unable to fetch subscription details.',
            ]);
        }

        return $response->json();
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /**
     * Determine the next billing end date from a Razorpay subscription event.
     * Razorpay provides current_end as a Unix timestamp.
     */
    public function endDateFromSubscription(array $rzpSubscription, Plan $plan): Carbon
    {
        if (! empty($rzpSubscription['current_end'])) {
            return Carbon::createFromTimestamp($rzpSubscription['current_end']);
        }

        // Fallback: compute from today
        return $plan->billing_type === 'yearly'
            ? Carbon::today()->addYear()
            : Carbon::today()->addMonth();
    }

    private function key(): string
    {
        $key = (string) config('services.razorpay.key');
        if ($key === '') {
            throw ValidationException::withMessages(['payment' => 'Razorpay key is not configured.']);
        }
        return $key;
    }

    private function secret(): string
    {
        $secret = (string) config('services.razorpay.secret');
        if ($secret === '') {
            throw ValidationException::withMessages(['payment' => 'Razorpay secret is not configured.']);
        }
        return $secret;
    }
}
