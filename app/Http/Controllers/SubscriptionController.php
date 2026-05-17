<?php

namespace App\Http\Controllers;

use App\Jobs\SendAddonSimPurchaseEmail;
use App\Jobs\SendSubscriptionPurchaseEmail;
use App\Models\AddonSimPurchase;
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
        ['pdf' => $pdf] = $this->subscriptionInvoiceData($request, $subscription);

        return response($pdf)->header('Content-Type', 'application/pdf');
    }

    /**
     * GET /subscription/invoices/{subscription}/download
     */
    public function invoiceDownload(Request $request, OrganizationSubscription $subscription)
    {
        ['pdf' => $pdf, 'filename' => $filename] = $this->subscriptionInvoiceData($request, $subscription);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * GET /subscription/addon-payments
     */
    public function addonPaymentHistory(Request $request): JsonResponse
    {
        $organizationId = (int) $request->user()->organization_id;

        if (! $organizationId) {
            return response()->json(['message' => 'No organization linked to this account.'], 404);
        }

        $addons = AddonSimPurchase::with([
            'plan:id,name,display_name',
            'subscription:id,billing_cycle,start_date,end_date',
        ])
            ->where('organization_id', $organizationId)
            ->where('payment_status', 'paid')
            ->whereNotNull('razorpay_payment_id')
            ->select([
                'id', 'organization_id', 'subscription_id', 'plan_id',
                'invoice_number', 'sim_quantity', 'price_per_sim', 'amount',
                'currency', 'razorpay_order_id', 'razorpay_payment_id',
                'payment_status', 'remaining_days', 'created_at',
            ])
            ->latest('id')
            ->get()
            ->map(fn (AddonSimPurchase $addon) => [
                'id'                  => $addon->id,
                'invoice_number'      => $addon->invoice_number ?? InvoiceService::ensureAddonInvoice($addon),
                'plan_name'           => $addon->plan?->display_name ?? 'Add-on SIMs',
                'billing_cycle'       => $addon->subscription?->billing_cycle,
                'sim_quantity'        => $addon->sim_quantity,
                'amount'              => $addon->amount,
                'currency'            => $addon->currency,
                'razorpay_order_id'   => $addon->razorpay_order_id,
                'razorpay_payment_id' => $addon->razorpay_payment_id,
                'payment_status'      => $addon->payment_status,
                'remaining_days'      => $addon->remaining_days,
                'start_date'          => $addon->subscription?->start_date?->toDateString(),
                'end_date'            => $addon->subscription?->end_date?->toDateString(),
                'paid_at'             => $addon->created_at?->toDateTimeString(),
            ]);

        return response()->json(['success' => true, 'data' => $addons]);
    }

    /**
     * GET /subscription/addon-invoices/{addon}
     */
    public function addonInvoiceView(Request $request, AddonSimPurchase $addon)
    {
        ['pdf' => $pdf] = $this->addonInvoiceData($request, $addon);

        return response($pdf)->header('Content-Type', 'application/pdf');
    }

    /**
     * GET /subscription/addon-invoices/{addon}/download
     */
    public function addonInvoiceDownload(Request $request, AddonSimPurchase $addon)
    {
        ['pdf' => $pdf, 'filename' => $filename] = $this->addonInvoiceData($request, $addon);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    private function subscriptionInvoiceData(Request $request, OrganizationSubscription $subscription): array
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

        $invoiceNumber = $subscription->invoice_number
            ?? InvoiceService::ensureSubscriptionInvoice($subscription);

        return [
            'filename' => strtolower($invoiceNumber).'.pdf',
            'pdf'      => InvoiceService::generateSubscriptionPdf($subscription, $invoiceNumber),
        ];
    }

    private function addonInvoiceData(Request $request, AddonSimPurchase $addon): array
    {
        $organizationId = (int) $request->user()->organization_id;

        if (
            ! $organizationId ||
            (int) $addon->organization_id !== $organizationId ||
            $addon->payment_status !== 'paid' ||
            ! $addon->razorpay_payment_id
        ) {
            abort(404, 'Invoice not found.');
        }

        $invoiceNumber = $addon->invoice_number
            ?? InvoiceService::ensureAddonInvoice($addon);

        return [
            'filename' => strtolower($invoiceNumber).'.pdf',
            'pdf'      => InvoiceService::generateAddonPdf($addon, $invoiceNumber),
        ];
    }

    private function invoiceNumber(OrganizationSubscription $subscription): string
    {
        return $subscription->invoice_number
            ?? InvoiceService::ensureSubscriptionInvoice($subscription);
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
