<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'type',
        'title',
        'last_updated',
        'effective_date',
        'intro_text',
        'sections',
    ];

    protected $casts = [
        'sections' => 'array',
    ];
}
