<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulatoryWatchRun extends Model
{
    protected $fillable = ['ran_at', 'ok', 'sources'];

    protected $casts = [
        'ran_at'  => 'datetime',
        'ok'      => 'boolean',
        'sources' => 'array',
    ];
}
