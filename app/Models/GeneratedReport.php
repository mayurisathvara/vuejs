<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedReport extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'report_type',
        'format',
        'status',
        'file_path',
        'file_name',
        'file_size',
        'filters',
        'generated_at',
        'expires_at',
        'error_message',
    ];

    protected $casts = [
        'filters'      => 'array',
        'generated_at' => 'datetime',
        'expires_at'   => 'datetime',
        'file_size'    => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReportLabelAttribute(): string
    {
        return match ($this->report_type) {
            'call_report'    => 'Call Report',
            'summary_report' => 'Summary Report',
            default          => 'Report',
        };
    }

    public function getFormatLabelAttribute(): string
    {
        return match ($this->format) {
            'excel' => 'Excel (.xls)',
            default => 'CSV (.csv)',
        };
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
