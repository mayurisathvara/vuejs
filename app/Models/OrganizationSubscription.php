<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationSubscription extends Model
{
    protected $fillable = [
        'organization_id',
        'plan_id',
        'billing_cycle',
        'sim_limit',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
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
