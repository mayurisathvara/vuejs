<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'industry',
		'app_login_code',
        'description',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the corresponding user record for this organization
     */
    public function user()
    {
        return $this->hasOne(User::class, 'organization_id');
    }

    /**
     * Get the teams that belong to this organization.
     */
    public function teams()
    {
        return $this->hasMany(Team::class, 'organization_id');
    }

    /**
     * Get the SIMs that belong to this organization.
     */
    public function sims()
    {
        return $this->hasMany(Sim::class, 'organization_id');
    }

    public function settings()
    {
        return $this->hasOne(OrganizationSetting::class, 'organization_id');
    }

    /**
     * Active subscription (the one with status=active and a valid date range).
     */
    public function subscription()
    {
        return $this->hasOne(OrganizationSubscription::class, 'organization_id')
            ->where('status', 'active')
            ->latest();
    }

    /**
     * All historical subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class, 'organization_id');
    }

    /**
     * Create or update corresponding user record
     */
    public function syncUser()
    {
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'mobile' => $this->mobile,
            'description' => $this->description,
            'status' => $this->status,
            'role' => 'organization',
        ];

        if ($this->user) {
            // Update existing user
            $this->user->update($userData);
        } else {
            // Create new user
            User::create($userData + ['organization_id' => $this->id]);
        }
    }

    /**
     * Boot method to handle model events - Optimized for performance
     */
    protected static function boot()
    {
        parent::boot();

        // Only sync user on create, not on every update for better performance
        static::created(function ($organization) {
            $organization->syncUser();

            // Assign Free Trial plan automatically — guarded so early migrations
            // (before the plans table exists) do not throw an exception.
            if (Schema::hasTable('plans')) {
                \App\Services\SubscriptionService::assignTrialPlan($organization->id);
            }
        });

        // Only sync user if critical fields changed
        static::updated(function ($organization) {
            if ($organization->isDirty(['name', 'email', 'password', 'mobile', 'status'])) {
                $organization->syncUser();
            }
        });

        static::deleted(function ($organization) {
            if ($organization->user) {
                $organization->user->delete();
            }
        });
    }
}
