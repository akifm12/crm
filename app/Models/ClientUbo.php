<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientUbo extends Model
{
    protected $fillable = [
        'bullion_client_id',
        'full_name',
        'nationality',
        'dob',
        'passport_number',
        'ownership_percentage',
        'country_of_residence',
        'pep_status',
    ];

    protected $casts = [
        'dob'                  => 'date',
        'pep_status'           => 'boolean',
        'ownership_percentage' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }
}
