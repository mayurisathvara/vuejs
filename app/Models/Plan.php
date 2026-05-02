<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'billing_type',
        'price_per_sim',
        'trial_days',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features'      => 'array',
            'is_active'     => 'boolean',
            'price_per_sim' => 'decimal:2',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class);
    }
}
