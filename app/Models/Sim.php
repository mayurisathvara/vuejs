<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sim extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'mobile',
        'name',
        'organization_id',
        'department_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the organization that owns the SIM.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the department that owns the SIM.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user SIM assignments.
     */
    public function userSims()
    {
        return $this->hasMany(UserSim::class);
    }

    /**
     * Get the users this SIM is assigned to.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sims')
            ->withTimestamps();
    }
}
