<?php

namespace App\Http\Controllers;

use App\Jobs\SendAddonSimPurchaseEmail;
use App\Jobs\SendSubscriptionPurchaseEmail;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Plan;
use App\Services\InvoiceService;
use App\Services\RazorpayAddonSimService;
use App\Services\RazorpayRecurringService;
use App\Services\RazorpaySubscriptionService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly RazorpaySubscriptionService $razorpaySubscriptions,
        private readonly RazorpayAddonSimService $razorpayAddons,
        private readonly RazorpayRecurringService $razorpayRecurring,
    ) {}

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
            'data' => SubscriptionService::getStats($organizationId),
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
                'plan_name',
                'price_per_sim',
                'billing_cycle',
                'sim_limit',
                'sim_quantity',
                'start_date',
                'end_date',
                'status',
                'auto_renew',
                'razorpay_subscription_id',
                'auto_renew_failed_at',
                'auto_renew_failure_reason',
                'features',
                'total_amount',
                'created_at',
                'updated_at',
            ])
            ->where('organization_id', $organizationId)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => SubscriptionService::getStats($organizationId),
                'subscription' => $subscription,
                'plan' => $subscription?->plan,
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
                'plan_name',
                'price_per_sim',
                'billing_cycle',
                'sim_limit',
                'sim_quantity',
                'start_date',
                'end_date',
                'status',
                'features',
                'total_amount',
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
            'data' => [
                'plans' => $plans,
                'stats' => SubscriptionService::getStats($organizationId),
                'subscription' => $subscription,
                'plan' => $subscription?->plan,
            ],
        ]);
    }

    /**
     * POST /subscription/renew/order
     *
     * Create a Razorpay TEST order for the selected paid plan and SIM quantity.
     */
    public function createRenewalOrder(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json([
                'message' => 'No organization linked to this account.',
            ], 404);
        }

        $validated = $request->validate([
            'subscription_plan_id' => 'required|integer|exists:plans,id',
            'sim_quantity' => 'required|integer|min:5|max:10000',
        ]);

        $data = $this->razorpaySubscriptions->createOrder(
            organizationId: $organizationId,
            planId: (int) $validated['subscription_plan_id'],
            simQuantity: (int) $validated['sim_quantity']
        );

        return response()->json([
            'success' => true,
            'message' => 'Razorpay order created.',
            'data' => $data,
        ]);
    }

    /**
     * POST /subscription/renew/verify
     *
     * Verify Razorpay signature and insert a brand-new subscription row.
     */
    public function verifyRenewalPayment(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json([
                'message' => 'No organization linked to this account.',
            ], 404);
        }

        $validated = $request->validate([
            'razorpay_order_id' => 'required|string|max:255',
            'razorpay_payment_id' => 'required|string|max:255',
            'razorpay_signature' => 'required|string|max:255',
        ]);

        $subscription = $this->razorpaySubscriptions->verifyAndCreateSubscription(
            organizationId: $organizationId,
            payload: $validated
        );

        if ($subscription->wasRecentlyCreated) {
            InvoiceService::ensureSubscriptionInvoice($subscription);
            SendSubscriptionPurchaseEmail::dispatch($subscription, 'renewal');
        }

        Log::info('Renewal payment verified', [
            'organization_id'     => $organizationId,
            'subscription_id'     => $subscription->id,
            'razorpay_order_id'   => $validated['razorpay_order_id'],
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'newly_created'       => $subscription->wasRecentlyCreated,
            'ip'                  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $subscription->wasRecentlyCreated
                ? 'Payment verified and subscription renewed.'
                : 'Payment already verified. Existing subscription returned.',
            'data' => [
                'subscription' => $subscription,
                'stats' => SubscriptionService::getStats($organizationId),
            ],
        ]);
    }

    /**
     * GET /subscription/payments
     *
     * Paid renewal rows for the authenticated organization.
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json([
                'message' => 'No organization linked to this account.',
            ], 404);
        }

        $payments = OrganizationSubscription::with('plan:id,name,display_name')
            ->where('organization_id', $organizationId)
            ->where('payment_status', 'paid')
            ->whereNotNull('razorpay_payment_id')
            ->select([
                'id',
                'organization_id',
                'plan_id',
                'plan_name',
                'billing_cycle',
                'sim_quantity',
                'amount',
                'currency',
                'razorpay_order_id',
                'razorpay_payment_id',
                'payment_status',
                'start_date',
                'end_date',
                'status',
                'created_at',
            ])
            ->latest('id')
            ->get()
            ->map(fn (OrganizationSubscription $payment) => [
                'id' => $payment->id,
                'invoice_number' => $this->invoiceNumber($payment),
                'plan_name' => $payment->plan_name ?? $payment->plan?->display_name,
                'billing_cycle' => $payment->billing_cycle,
                'sim_quantity' => $payment->sim_quantity,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'razorpay_order_id' => $payment->razorpay_order_id,
                'razorpay_payment_id' => $payment->razorpay_payment_id,
                'payment_status' => $payment->payment_status,
                'subscription_status' => $payment->status,
                'start_date' => $payment->start_date?->toDateString(),
                'end_date' => $payment->end_date?->toDateString(),
                'paid_at' => $payment->created_at?->toDateTimeString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * GET /subscription/invoices/{subscription}
     */
    public function invoiceView(Request $request, OrganizationSubscription $subscription)
    {
        $invoice = $this->invoiceForRequest($request, $subscription);

        return response($invoice['html'])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * GET /subscription/invoices/{subscription}/download
     */
    public function invoiceDownload(Request $request, OrganizationSubscription $subscription)
    {
        $invoice = $this->invoiceForRequest($request, $subscription);

        return response($invoice['html'])
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$invoice['filename'].'"');
    }

    private function invoiceForRequest(Request $request, OrganizationSubscription $subscription): array
    {
        $organizationId = (int) $request->user()->organization_id;

        if (
            ! $organizationId ||
            (int) $subscription->organization_id !== $organizationId ||
            $subscription->payment_status !== 'paid' ||
            ! $subscription->razorpay_payment_id
        ) {
            abort(404, 'Invoice not found.');
        }

        $subscription->loadMissing('organization', 'plan');

        $invoiceNumber = $this->invoiceNumber($subscription);

        return [
            'filename' => strtolower($invoiceNumber).'.html',
            'html' => $this->renderInvoiceHtml($subscription, $invoiceNumber),
        ];
    }

    private function invoiceNumber(OrganizationSubscription $subscription): string
    {
        return $subscription->invoice_number
            ?? InvoiceService::ensureSubscriptionInvoice($subscription);
    }

    private function renderInvoiceHtml(OrganizationSubscription $subscription, string $invoiceNumber): string
    {
        $organization = $subscription->organization;
        $currency = $subscription->currency ?: 'INR';
        $amount = number_format((float) $subscription->amount, 2);
        $pricePerSim = number_format((float) $subscription->price_per_sim, 2);
        $paidAt = $subscription->created_at?->format('d M Y, h:i A') ?? '-';
        $period = ($subscription->start_date?->format('d M Y') ?? '-').' to '.($subscription->end_date?->format('d M Y') ?? '-');
        $logoUrl = asset('logo/logo_black.png');

        return '<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>'.e($invoiceNumber).' - Callytics Invoice</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #f3f6fb; color: #102033; font-family: Arial, Helvetica, sans-serif; }
    .page { max-width: 920px; margin: 32px auto; padding: 36px; background: #fff; border: 1px solid #e5ebf3; border-radius: 18px; }
    .top { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #10233f; padding-bottom: 24px; }
    .brand img { display: block; width: 150px; height: auto; }
    .muted { color: #667085; }
    h1 { margin: 8px 0 0; font-size: 30px; }
    h3 { margin: 0 0 10px; font-size: 15px; text-transform: uppercase; letter-spacing: .08em; color: #667085; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 28px 0; }
    .box { padding: 18px; border: 1px solid #e5ebf3; border-radius: 12px; background: #fbfdff; }
    .box p { margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 18px; }
    th { text-align: left; background: #10233f; color: #fff; padding: 13px; font-size: 13px; }
    td { padding: 13px; border-bottom: 1px solid #e5ebf3; vertical-align: top; }
    .right { text-align: right; }
    .total { margin-left: auto; width: 320px; margin-top: 20px; }
    .total-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e5ebf3; }
    .grand { font-size: 22px; font-weight: 900; color: #10233f; }
    .ids { margin-top: 28px; padding-top: 18px; border-top: 1px solid #e5ebf3; font-size: 13px; word-break: break-all; }
    .actions { display: flex; justify-content: flex-end; margin-bottom: 16px; }
    button { border: 0; border-radius: 8px; padding: 10px 14px; color: #fff; background: #f97316; font-weight: 800; cursor: pointer; }
    @media print { body { background: #fff; } .page { margin: 0; max-width: none; border: 0; border-radius: 0; } .actions { display: none; } }
  </style>
</head>
<body>
  <div class="page">
    <div class="actions"><button onclick="window.print()">Print / Save PDF</button></div>
    <div class="top">
      <div>
        <div class="brand"><img src="'.e($logoUrl).'" alt="Callytics"></div>
        <p class="muted">Subscription payment invoice</p>
      </div>
      <div class="right">
        <h1>Invoice</h1>
        <p><strong>'.e($invoiceNumber).'</strong></p>
        <p class="muted">Paid on '.e($paidAt).'</p>
      </div>
    </div>

    <div class="grid">
      <div class="box">
        <h3>Billed To</h3>
        <p><strong>'.e($organization?->name ?? 'Organization').'</strong></p>
        <p>'.e($organization?->email ?? '-').'</p>
        <p>'.e($organization?->mobile ?? '-').'</p>
      </div>
      <div class="box">
        <h3>Payment</h3>
        <p>Status: <strong>'.e(ucfirst((string) $subscription->payment_status)).'</strong></p>
        <p>Currency: '.e($currency).'</p>
        <p>Subscription: '.e(ucfirst((string) $subscription->status)).'</p>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th>Billing Cycle</th>
          <th class="right">SIMs</th>
          <th class="right">Rate</th>
          <th class="right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>'.e($subscription->plan_name ?? $subscription->plan?->display_name ?? 'Subscription Plan').'</strong><br>
            <span class="muted">Service period: '.e($period).'</span>
          </td>
          <td>'.e(ucfirst((string) $subscription->billing_cycle)).'</td>
          <td class="right">'.e((string) $subscription->sim_quantity).'</td>
          <td class="right">'.e($currency).' '.e($pricePerSim).'</td>
          <td class="right">'.e($currency).' '.e($amount).'</td>
        </tr>
      </tbody>
    </table>

    <div class="total">
      <div class="total-row"><span>Subtotal</span><strong>'.e($currency).' '.e($amount).'</strong></div>
      <div class="total-row"><span>Tax</span><strong>'.e($currency).' 0.00</strong></div>
      <div class="total-row grand"><span>Total Paid</span><span>'.e($currency).' '.e($amount).'</span></div>
    </div>

    <div class="ids">
      <p><strong>Razorpay Order ID:</strong> '.e($subscription->razorpay_order_id ?? '-').'</p>
      <p><strong>Razorpay Payment ID:</strong> '.e($subscription->razorpay_payment_id ?? '-').'</p>
    </div>
  </div>
</body>
</html>';
    }

    /**
     * POST /subscription/addon-sim/order
     *
     * Calculate prorated amount for add-on SIMs and create a Razorpay order.
     * Requires an active subscription with a future end date.
     */
    public function createAddonOrder(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json(['message' => 'No organization linked to this account.'], 404);
        }

        $validated = $request->validate([
            'sim_quantity' => 'required|integer|min:1|max:1000',
        ]);

        $data = $this->razorpayAddons->createAddonOrder(
            organizationId: $organizationId,
            simQuantity: (int) $validated['sim_quantity']
        );

        return response()->json([
            'success' => true,
            'message' => 'Add-on SIM order created.',
            'data'    => $data,
        ]);
    }

    /**
     * POST /subscription/addon-sim/verify
     *
     * Verify Razorpay add-on payment signature and expand the subscription SIM limit.
     */
    public function verifyAddonPayment(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json(['message' => 'No organization linked to this account.'], 404);
        }

        $validated = $request->validate([
            'razorpay_order_id'   => 'required|string|max:255',
            'razorpay_payment_id' => 'required|string|max:255',
            'razorpay_signature'  => 'required|string|max:255',
        ]);

        $addon = $this->razorpayAddons->verifyAndActivateAddon(
            organizationId: $organizationId,
            payload: $validated
        );

        $simCount = $addon->sim_quantity;

        if ($addon->wasRecentlyCreated) {
            InvoiceService::ensureAddonInvoice($addon);
            SendAddonSimPurchaseEmail::dispatch($addon);
        }

        Log::info('Addon SIM payment verified', [
            'organization_id'     => $organizationId,
            'addon_id'            => $addon->id,
            'sim_quantity'        => $simCount,
            'razorpay_order_id'   => $validated['razorpay_order_id'],
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'newly_created'       => $addon->wasRecentlyCreated,
            'ip'                  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $addon->wasRecentlyCreated
                ? "{$simCount} add-on SIM(s) activated and added to your subscription."
                : 'Payment already verified. Add-on SIMs are already active.',
            'data'    => [
                'addon' => $addon,
                'stats' => SubscriptionService::getStats($organizationId),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Auto-renewal endpoints
    // -------------------------------------------------------------------------

    /**
     * POST /subscription/auto-renew/toggle
     *
     * Enable or disable auto-renewal for the current active subscription.
     * When disabling, cancel the associated Razorpay subscription (at cycle end).
     */
    public function toggleAutoRenew(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json(['message' => 'No organization linked to this account.'], 404);
        }

        $validated = $request->validate([
            'auto_renew' => 'required|boolean',
        ]);

        $subscription = OrganizationSubscription::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'upcoming'])
            ->latest()
            ->first();

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription found.'], 404);
        }

        $enable = (bool) $validated['auto_renew'];

        // If disabling and a Razorpay subscription exists, cancel it at cycle end
        if (! $enable && $subscription->razorpay_subscription_id) {
            $this->razorpayRecurring->cancelSubscription($subscription->razorpay_subscription_id);
        }

        $subscription->update([
            'auto_renew'              => $enable,
            'auto_renew_failure_reason' => $enable ? null : $subscription->auto_renew_failure_reason,
            'auto_renew_failed_at'    => $enable ? null : $subscription->auto_renew_failed_at,
        ]);

        Log::info('Auto-renew toggled', [
            'organization_id'  => $organizationId,
            'subscription_id'  => $subscription->id,
            'auto_renew'       => $enable,
            'ip'               => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $enable ? 'Auto-renewal enabled.' : 'Auto-renewal disabled.',
            'data'    => ['auto_renew' => $enable],
        ]);
    }

    /**
     * POST /subscription/recurring/order
     *
     * Create a Razorpay Subscription (recurring) for the selected plan + SIM count.
     * Returns the Razorpay key + subscription object so the frontend can open checkout.
     */
    public function createRecurringOrder(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json(['message' => 'No organization linked to this account.'], 404);
        }

        $validated = $request->validate([
            'subscription_plan_id' => 'required|integer|exists:plans,id',
            'sim_quantity'         => 'required|integer|min:5|max:10000',
        ]);

        $organization = Organization::findOrFail($organizationId);
        $plan         = Plan::where('id', $validated['subscription_plan_id'])
            ->where('is_active', true)
            ->whereIn('billing_type', ['monthly', 'yearly'])
            ->firstOrFail();

        $data = $this->razorpayRecurring->createSubscription(
            organization: $organization,
            plan: $plan,
            simQuantity: (int) $validated['sim_quantity']
        );

        return response()->json([
            'success' => true,
            'message' => 'Razorpay recurring subscription created.',
            'data'    => $data,
        ]);
    }

    /**
     * POST /subscription/recurring/verify
     *
     * Verify the recurring subscription payment signature and activate auto-renewal.
     * The actual subscription row will be created/updated when the webhook fires
     * (subscription.charged). This endpoint just records the razorpay_subscription_id
     * and marks the current subscription as auto_renew=true.
     */
    public function verifyRecurringPayment(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = (int) $user->organization_id;

        if (! $organizationId) {
            return response()->json(['message' => 'No organization linked to this account.'], 404);
        }

        $validated = $request->validate([
            'razorpay_subscription_id' => 'required|string|max:255',
            'razorpay_payment_id'      => 'required|string|max:255',
            'razorpay_signature'       => 'required|string|max:255',
        ]);

        $this->razorpayRecurring->verifySignature(
            subscriptionId: $validated['razorpay_subscription_id'],
            paymentId:      $validated['razorpay_payment_id'],
            signature:      $validated['razorpay_signature']
        );

        // Link the razorpay_subscription_id to the active subscription so the webhook
        // can match future charge events to this organization.
        $subscription = OrganizationSubscription::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'upcoming'])
            ->latest()
            ->first();

        if ($subscription) {
            $subscription->update([
                'razorpay_subscription_id' => $validated['razorpay_subscription_id'],
                'auto_renew'              => true,
                'auto_renew_failed_at'    => null,
                'auto_renew_failure_reason' => null,
            ]);
        }

        Log::info('Recurring subscription payment verified', [
            'organization_id'          => $organizationId,
            'subscription_id'          => $subscription?->id,
            'razorpay_subscription_id' => $validated['razorpay_subscription_id'],
            'razorpay_payment_id'      => $validated['razorpay_payment_id'],
            'ip'                       => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recurring subscription verified. Auto-renewal is now active.',
            'data'    => [
                'razorpay_subscription_id' => $validated['razorpay_subscription_id'],
                'auto_renew'               => true,
                'stats'                    => SubscriptionService::getStats($organizationId),
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
            'data' => $plans,
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
            'data' => $subscriptions,
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
            'success' => true,
            'subscription' => $subscription,
            'stats' => SubscriptionService::getStats($organization->id),
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
            'plan_id' => 'required|integer|exists:plans,id',
            'billing_cycle' => 'required|string|in:monthly,yearly,trial',
            'sim_limit' => 'required|integer|min:1|max:10000',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $plan = Plan::findOrFail($request->plan_id);

        $subscription = SubscriptionService::assignPlan(
            organizationId: $organization->id,
            plan: $plan,
            billingCycle: $request->billing_cycle,
            simLimit: $request->sim_limit,
            notes: $request->notes
        );

        return response()->json([
            'success' => true,
            'message' => "Plan \"{$plan->display_name}\" assigned to {$organization->name}.",
            'subscription' => $subscription->load('plan'),
            'stats' => SubscriptionService::getStats($organization->id),
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
                'errors' => $validator->errors(),
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
            'success' => true,
            'message' => 'SIM limit updated.',
            'subscription' => $subscription->fresh()->load('plan'),
            'stats' => SubscriptionService::getStats($organization->id),
        ]);
    }
}
