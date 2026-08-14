<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningLog extends Model
{
    protected $fillable = [
        'tenant_id', 'bullion_client_id', 'screened_by',
        'query', 'entity_type', 'status', 'total_hits',
        'source', 'reference', 'result', 'reviews', 'reviewed_at',
    ];

    protected $casts = [
        'result'      => 'array',
        'reviews'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo  { return $this->belongsTo(BullionClient::class, 'bullion_client_id'); }
    public function screener(): BelongsTo { return $this->belongsTo(User::class, 'screened_by'); }

    public function statusBadge(): string
    {
        return match($this->status) {
            'match' => 'bg-red-100 text-red-700',
            'clear' => 'bg-green-100 text-green-700',
            'error' => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-500',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'match' => 'Match',
            'clear' => 'Clear',
            'error' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function eddStatus(): string
    {
        if ($this->status !== 'match' || !$this->reviewed_at) return $this->status;
        $tp = collect($this->reviews ?? [])->where('verdict', 'true_positive')->count();
        return $tp > 0 ? 'confirmed_match' : 'edd_clear';
    }

    public function eddStatusBadge(): string
    {
        return match($this->eddStatus()) {
            'edd_clear'       => 'bg-green-100 text-green-700',
            'confirmed_match' => 'bg-red-100 text-red-800',
            default           => $this->statusBadge(),
        };
    }

    public function eddStatusLabel(): string
    {
        return match($this->eddStatus()) {
            'edd_clear'       => 'Clear (EDD)',
            'confirmed_match' => 'Confirmed Match',
            default           => $this->statusLabel(),
        };
    }

    public function hitsNeedingReview(): array
    {
        return $this->result['hits'] ?? [];
    }

    public function isFullyReviewed(): bool
    {
        $hits = $this->hitsNeedingReview();
        if (empty($hits)) return true;
        $reviewed = collect($this->reviews ?? [])->pluck('hit_id')->all();
        foreach ($hits as $hit) {
            if (!in_array($hit['id'], $reviewed)) return false;
        }
        return true;
    }

    public function reviewFor(int|string $hitId): ?array
    {
        return collect($this->reviews ?? [])
            ->firstWhere('hit_id', $hitId);
    }
}
