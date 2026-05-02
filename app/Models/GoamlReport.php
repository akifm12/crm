<?php
// app/Models/GoamlReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoamlReport extends Model
{
    protected $fillable = [
        'tenant_id', 'bullion_client_id', 'report_type', 'entity_reference',
        'client_name', 'estimated_value', 'disposed_value', 'currency_code',
        'size', 'size_uom', 'registration_date', 'reason', 'comments',
        'xml_file_path', 'xml_file_name', 'generated_by',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'estimated_value'   => 'decimal:2',
        'disposed_value'    => 'decimal:2',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo  { return $this->belongsTo(BullionClient::class, 'bullion_client_id'); }
    public function author(): BelongsTo  { return $this->belongsTo(User::class, 'generated_by'); }

    public function reportTypeBadgeColor(): string
    {
        return match($this->report_type) {
            'STR'   => 'bg-red-100 text-red-700',
            'SAR'   => 'bg-orange-100 text-orange-700',
            'DPMSR' => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-500',
        };
    }
}
