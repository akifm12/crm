<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSignatory extends Model
{
    protected $fillable = [
        'bullion_client_id',
        'full_name',
        'position',
        'nationality',
        'dob',
        'passport_number',
        'passport_expiry',
        'eid_number',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'dob'             => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }
}
