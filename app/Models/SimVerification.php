<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimVerification extends Model
{
    use HasFactory;

    /**
     * Table name (optional if using plural by default)
     */
    protected $table = 'sim_verifications';

    /**
     * Fields that can be mass-assigned.
     */
    protected $fillable = [
        'verification_id',
        'mobile',
        'token_hash',
        'status',           // pending | verified
        'sender_number',
        'subscription_id',  // SIM subscription_id selected by user
        'expires_at',
    ];

    /**
     * Casts for attributes.
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
