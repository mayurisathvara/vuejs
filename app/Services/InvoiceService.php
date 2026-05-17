<?php

namespace App\Services;

use App\Models\AddonSimPurchase;
use App\Models\OrganizationSubscription;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate and store a unique invoice number for a subscription.
     * Idempotent — returns the existing number if already set.
     */
    public static function ensureSubscriptionInvoice(OrganizationSubscription $subscription): string
    {
        if ($subscription->invoice_number) {
            return $subscription->invoice_number;
        }

        $number = 'INV-' . date('Y') . '-' . str_pad((string) $subscription->id, 6, '0', STR_PAD_LEFT);
        $subscription->updateQuietly(['invoice_number' => $number]);

        return $number;
    }

    /**
     * Generate and store a unique invoice number for an add-on purchase.
     * Idempotent — returns the existing number if already set.
     */
    public static function ensureAddonInvoice(AddonSimPurchase $addon): string
    {
        if ($addon->invoice_number) {
            return $addon->invoice_number;
        }

        $number = 'ADD-' . date('Y') . '-' . str_pad((string) $addon->id, 6, '0', STR_PAD_LEFT);
        $addon->updateQuietly(['invoice_number' => $number]);

        return $number;
    }

    /**
     * Render a subscription invoice as a PDF and return the raw bytes.
     */
    public static function generateSubscriptionPdf(OrganizationSubscription $subscription, string $invoiceNumber): string
    {
        $subscription->loadMissing('organization', 'plan');

        $data = [
            'invoiceNumber' => $invoiceNumber,
            'subscription'  => $subscription,
            'organization'  => $subscription->organization,
            'plan'          => $subscription->plan,
            'currency'      => $subscription->currency ?: 'INR',
            'amount'        => (float) $subscription->amount,
            'pricePerSim'   => (float) $subscription->price_per_sim,
            'simQuantity'   => (int)   $subscription->sim_quantity,
            'paidAt'        => $subscription->created_at?->format('d M Y, h:i A') ?? '-',
            'period'        => ($subscription->start_date?->format('d M Y') ?? '-')
                               . ' to '
                               . ($subscription->end_date?->format('d M Y') ?? '-'),
            'planName'      => $subscription->plan_name
                               ?? $subscription->plan?->display_name
                               ?? 'Subscription Plan',
            'billingCycle'  => ucfirst((string) $subscription->billing_cycle),
            'paymentId'     => $subscription->razorpay_payment_id ?? '-',
            'orderId'       => $subscription->razorpay_order_id ?? '-',
            'type'          => 'subscription',
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isRemoteEnabled', false);

        return $pdf->output();
    }

    /**
     * Render an add-on SIM invoice as a PDF and return the raw bytes.
     */
    public static function generateAddonPdf(AddonSimPurchase $addon, string $invoiceNumber): string
    {
        $addon->loadMissing('organization', 'subscription', 'plan');

        $subscription = $addon->subscription;
        $currency     = $addon->currency ?: 'INR';

        $data = [
            'invoiceNumber' => $invoiceNumber,
            'addon'         => $addon,
            'organization'  => $addon->organization,
            'subscription'  => $subscription,
            'plan'          => $addon->plan,
            'currency'      => $currency,
            'amount'        => (float) $addon->amount,
            'pricePerSim'   => (float) $addon->price_per_sim,
            'simQuantity'   => (int)   $addon->sim_quantity,
            'paidAt'        => $addon->created_at?->format('d M Y, h:i A') ?? '-',
            'period'        => ($subscription?->start_date?->format('d M Y') ?? '-')
                               . ' to '
                               . ($subscription?->end_date?->format('d M Y') ?? '-'),
            'planName'      => $addon->plan?->display_name ?? 'Add-on SIMs',
            'billingCycle'  => ucfirst((string) ($subscription?->billing_cycle ?? '-')),
            'paymentId'     => $addon->razorpay_payment_id ?? '-',
            'orderId'       => $addon->razorpay_order_id ?? '-',
            'remainingDays' => (int) $addon->remaining_days,
            'proratedAmount' => (float) $addon->prorated_amount,
            'type'          => 'addon',
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isRemoteEnabled', false);

        return $pdf->output();
    }
}
