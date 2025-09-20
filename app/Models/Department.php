<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization_id',
    ];

    /**
     * Get the organization that the department belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Get the users that belong to this department.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}
