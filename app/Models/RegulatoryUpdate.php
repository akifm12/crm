<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulatoryUpdate extends Model
{
    protected $fillable = [
        'source_key', 'fingerprint', 'title', 'url', 'change_type', 'summary', 'details',
        'affected_sections', 'status', 'reviewer_note', 'detected_at', 'reviewed_at',
    ];

    protected $casts = [
        'details'     => 'array',
        'detected_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
}
