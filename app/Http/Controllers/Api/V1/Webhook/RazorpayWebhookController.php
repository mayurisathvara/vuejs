<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\SendSubscriptionPurchaseEmail;
use App\Models\OrganizationSubscription;
use App\Models\Plan;
use App\Services\InvoiceService;
use App\Services\RazorpayRecurringService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(private RazorpayRecurringService $recurringService)
    {
    }

    /**
     * Handle incoming Razorpay webhook events.
     * Route: POST /api/webhooks/razorpay  (public, no Sanctum auth)
     */
    public function handle(Request $request): \Illuminate\Http\JsonResponse
    {
        $rawBody  = $request->getContent();
        $sigHeader = $request->header('X-Razorpay-Signature', '');

        if (! $this->recurringService->verifyWebhookSignature($rawBody, $sigHeader)) {
            Log::warning('Razorpay webhook: invalid signature', [
                'ip'    => $request->ip(),
                'event' => $request->input('event'),
            ]);
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event   = $request->input('event', '');
        $payload = $request->input('payload', []);

        Log::info('Razorpay webhook received', ['event' => $event]);

        match ($event) {
            'subscription.charged'   => $this->handleCharged($payload),
            'subscription.halted'    => $this->handleHalted($payload),
            'subscription.cancelled' => $this->handleCancelled($payload),
            default                  => null,
        };

        return response()->json(['message' => 'OK']);
    }

    // -------------------------------------------------------------------------
    // subscription.charged — a recurring payment succeeded
    // -------------------------------------------------------------------------

    private function handleCharged(array $payload): void
    {
        $rzpSub   = $payload['subscription']['entity'] ?? [];
        $rzpPay   = $payload['payment']['entity']      ?? [];

        $rzpSubId  = $rzpSub['id']     ?? null;
        $paymentId = $rzpPay['id']     ?? null;
        $amount    = ($rzpPay['amount'] ?? 0) / 100; // paise → rupees
        $currency  = $rzpPay['currency'] ?? config('services.razorpay.currency', 'INR');
        $notes     = $rzpSub['notes']  ?? [];

        if (! $rzpSubId || ! $paymentId) {
            Log::error('Razorpay webhook charged: missing IDs', compact('rzpSubId', 'paymentId'));
            return;
        }

        $organizationId = (int) ($notes['organization_id'] ?? 0);
        $planId         = (int) ($notes['app_plan_id']     ?? 0);
        $simQuantity    = (int) ($notes['sim_quantity']    ?? 0);

        if ($organizationId < 1 || $planId < 1 || $simQuantity < 1) {
            Log::error('Razorpay webhook charged: incomplete notes', $notes);
            return;
        }

        $plan = Plan::find($planId);
        if (! $plan) {
            Log::error('Razorpay webhook charged: plan not found', ['plan_id' => $planId]);
            return;
        }

        DB::transaction(function () use (
            $organizationId, $planId, $plan, $simQuantity,
            $rzpSubId, $paymentId, $amount, $currency, $rzpSub
        ) {
            // Idempotency: skip if this payment was already recorded
            $alreadyRecorded = OrganizationSubscription::where('razorpay_subscription_id', $rzpSubId)
                ->where('razorpay_payment_id', $paymentId)
                ->exists();

            if ($alreadyRecorded) {
                return;
            }

            $today = Carbon::today();

            // Expire any currently active subscription for this org
            OrganizationSubscription::where('organization_id', $organizationId)
                ->where('status', 'active')
                ->whereDate('end_date', '<', $today)
                ->update(['status' => 'expired']);

            // Determine start/end dates from Razorpay data
            $startDate = empty($rzpSub['current_start'])
                ? $today->copy()
                : Carbon::createFromTimestamp($rzpSub['current_start']);

            $endDate = $this->recurringService->endDateFromSubscription($rzpSub, $plan);

            $subscription = OrganizationSubscription::create([
                'organization_id'        => $organizationId,
                'plan_id'                => $planId,
                'plan_name'              => $plan->display_name,
                'price_per_sim'          => $plan->price_per_sim,
                'billing_cycle'          => $plan->billing_type,
                'sim_limit'              => $simQuantity,
                'sim_quantity'           => $simQuantity,
                'features'               => $plan->features ?? [],
                'total_amount'           => $amount,
                'amount'                 => $amount,
                'currency'               => $currency,
                'razorpay_subscription_id' => $rzpSubId,
                'razorpay_payment_id'    => $paymentId,
                'payment_status'         => 'paid',
                'auto_renew'             => true,
                'start_date'             => $startDate->toDateString(),
                'end_date'               => $endDate->toDateString(),
                'status'                 => 'active',
                'transaction_payload'    => [
                    'razorpay_subscription' => $rzpSub,
                    'razorpay_payment_id'   => $paymentId,
                    'verified_at'           => now()->toISOString(),
                    'source'                => 'webhook',
                ],
            ]);

            SubscriptionService::enforceSimLimit($organizationId);

            InvoiceService::ensureSubscriptionInvoice($subscription);
            SendSubscriptionPurchaseEmail::dispatch($subscription, 'auto_renewal');

            Log::info('Razorpay subscription.charged processed', [
                'organization_id'          => $organizationId,
                'subscription_id'          => $subscription->id,
                'razorpay_subscription_id' => $rzpSubId,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // subscription.halted — recurring payment failed multiple times
    // -------------------------------------------------------------------------

    private function handleHalted(array $payload): void
    {
        $rzpSub   = $payload['subscription']['entity'] ?? [];
        $rzpSubId = $rzpSub['id'] ?? null;

        if (! $rzpSubId) {
            return;
        }

        OrganizationSubscription::where('razorpay_subscription_id', $rzpSubId)
            ->where('auto_renew', true)
            ->update([
                'auto_renew'              => false,
                'auto_renew_failed_at'    => now(),
                'auto_renew_failure_reason' => 'Subscription halted by Razorpay due to repeated payment failures.',
            ]);

        Log::warning('Razorpay subscription.halted', ['razorpay_subscription_id' => $rzpSubId]);
    }

    // -------------------------------------------------------------------------
    // subscription.cancelled — user or admin cancelled the recurring subscription
    // -------------------------------------------------------------------------

    private function handleCancelled(array $payload): void
    {
        $rzpSub   = $payload['subscription']['entity'] ?? [];
        $rzpSubId = $rzpSub['id'] ?? null;

        if (! $rzpSubId) {
            return;
        }

        OrganizationSubscription::where('razorpay_subscription_id', $rzpSubId)
            ->update([
                'auto_renew'              => false,
                'auto_renew_failure_reason' => 'Subscription cancelled.',
            ]);

        Log::info('Razorpay subscription.cancelled', ['razorpay_subscription_id' => $rzpSubId]);
    }
}
