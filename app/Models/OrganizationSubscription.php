<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationSubscription extends Model
{
    protected $fillable = [
        'organization_id',
        'plan_id',
        'plan_name',
        'invoice_number',
        'price_per_sim',
        'billing_cycle',
        'sim_limit',
        'sim_quantity',
        'start_date',
        'end_date',
        'status',
        'notes',
        'features',
        'total_amount',
        'amount',
        'currency',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'payment_status',
        'transaction_payload',
    ];

    protected function casts(): array
    {
        return [
            'start_date'           => 'date',
            'end_date'             => 'date',
            'features'             => 'array',
            'price_per_sim'        => 'decimal:2',
            'total_amount'         => 'decimal:2',
            'amount'               => 'decimal:2',
            'transaction_payload'  => 'array',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Whether this subscription is currently within its valid date range and marked active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->end_date === null || $this->end_date->isFuture() || $this->end_date->isToday());
    }
}
