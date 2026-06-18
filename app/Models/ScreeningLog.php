<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningLog extends Model
{
    protected $fillable = [
        'tenant_id', 'bullion_client_id', 'screened_by',
        'query', 'entity_type', 'status', 'total_hits',
        'source', 'reference', 'result',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo  { return $this->belongsTo(BullionClient::class, 'bullion_client_id'); }
    public function screener(): BelongsTo { return $this->belongsTo(User::class, 'screened_by'); }

    public function statusBadge(): string
    {
        return match($this->status) {
            'match' => 'bg-red-100 text-red-700',
            'clear' => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-500',
        };
    }
}
