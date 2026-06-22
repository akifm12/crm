<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentScanLog extends Model
{
    protected $fillable = [
        'tenant_id', 'bullion_client_id', 'client_document_id',
        'document_type_detected', 'raw_response', 'changes',
        'status', 'failure_reason', 'created_by',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'changes'      => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ClientDocument::class, 'client_document_id');
    }

    public function revert(): bool
    {
        if ($this->status !== 'applied') return false;

        foreach ($this->changes as $change) {
            $model = match ($change['model']) {
                'client'      => BullionClient::find($change['model_id']),
                'shareholder' => ClientShareholder::find($change['model_id']),
                default       => null,
            };

            if ($model) {
                $model->update([$change['field'] => $change['old_value']]);
            }
        }

        $this->update(['status' => 'reverted']);
        return true;
    }
}
