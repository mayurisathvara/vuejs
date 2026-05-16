<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddonSimPurchase extends Model
{
    protected $table = 'organization_addon_sim_purchases';

    protected $fillable = [
        'organization_id',
        'subscription_id',
        'plan_id',
        'sim_quantity',
        'price_per_sim',
        'billing_cycle_days',
        'remaining_days',
        'proration_factor',
        'prorated_amount',
        'amount',
        'currency',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'payment_status',
        'merged_at_renewal',
        'transaction_payload',
    ];

    protected $casts = [
        'price_per_sim'    => 'decimal:2',
        'proration_factor' => 'decimal:6',
        'prorated_amount'  => 'decimal:2',
        'amount'           => 'decimal:2',
        'merged_at_renewal' => 'boolean',
        'transaction_payload' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(OrganizationSubscription::class, 'subscription_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
