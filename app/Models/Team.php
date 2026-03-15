<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization_id',
    ];

    /**
     * Get the organization that the team belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Get the users that belong to this team.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'team_id');
    }
}
