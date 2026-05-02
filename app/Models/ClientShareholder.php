<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientShareholder extends Model
{
    protected $fillable = [
        'bullion_client_id',
        'shareholder_type',
        'name',
        'nationality',
        'dob',
        'ownership_percentage',
        'passport_number',
        'is_ubo',
    ];

    protected $casts = [
        'is_ubo'               => 'boolean',
        'ownership_percentage' => 'decimal:2',
        'dob'                  => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }
}
