<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TfsSubmission extends Model
{
    protected $fillable = [
        'tenant_id', 'created_by', 'title', 'alert_url',
        'alerted_names', 'screening_results', 'tfs_urls',
        'recommended_response', 'selected_response',
        'status', 'submitted_at',
    ];

    protected $casts = [
        'alerted_names'     => 'array',
        'screening_results' => 'array',
        'tfs_urls'          => 'array',
        'submitted_at'      => 'datetime',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function pendingUrls(): array
    {
        return collect($this->tfs_urls ?? [])
            ->where('status', 'pending')
            ->values()->toArray();
    }

    public function submittedCount(): int
    {
        return collect($this->tfs_urls ?? [])->where('status', 'submitted')->count();
    }

    public function totalUrls(): int
    {
        return count($this->tfs_urls ?? []);
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'submitted' => 'bg-green-100 text-green-700',
            'partial'   => 'bg-amber-100 text-amber-700',
            'screened'  => 'bg-blue-100 text-blue-700',
            default     => 'bg-gray-100 text-gray-500',
        };
    }

    public function responseBadge(): string
    {
        return match($this->selected_response ?? $this->recommended_response) {
            'no_match'        => 'bg-green-100 text-green-700',
            'confirmed_match' => 'bg-red-100 text-red-700',
            'partial_match'   => 'bg-amber-100 text-amber-700',
            default           => 'bg-gray-100 text-gray-500',
        };
    }

    public function responseLabel(): string
    {
        return match($this->selected_response ?? $this->recommended_response) {
            'no_match'        => 'No match identified',
            'confirmed_match' => 'Confirmed match',
            'partial_match'   => 'Partial match',
            default           => '—',
        };
    }
}
