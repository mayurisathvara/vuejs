<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
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
     * Get the departments that belong to this organization.
     */
    public function departments()
    {
        return $this->hasMany(Department::class, 'organization_id');
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
