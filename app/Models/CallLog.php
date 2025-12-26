<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_id',
        'organization_id',
        'caller_id',
        'date',
        'time',
        'date_time',
        'call_type',
        'call_status',
        'caller_number',
        'caller_duration',
        'conversation_duration',
        'ring_duration',
        'contact_status',
        'contact_name',
        'hangup_by',
        'name',
        'department_name',
        'call_back',
        'callback_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'time' => 'datetime:H:i:s',
        'date_time' => 'datetime:Y-m-d H:i:s',
        'caller_duration' => 'string',
        'conversation_duration' => 'string',
        'ring_duration' => 'string',
        'deleted_at' => 'datetime',
    ];
	

    /**
     * Get the organization that owns the call log.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user that owns the call log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
