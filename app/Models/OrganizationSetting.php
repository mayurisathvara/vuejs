<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganizationSetting extends Model
{
    use HasFactory;

    protected $table = 'organization_settings';

    protected $fillable = [
        'organization_id',
        'callback_window_hours',
        'date_formate',
        'enable_manager_role',
        'enable_working_hours',
        'working_hours',
        'exclude_numbers_enabled',
    ];

    protected $casts = [
        'enable_manager_role'       => 'boolean',
        'enable_working_hours'      => 'boolean',
        'working_hours'             => 'integer',
        'callback_window_hours'     => 'integer',
        'exclude_numbers_enabled'   => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
