<?php
// app/Models/ClientFillToken.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClientFillToken extends Model
{
    protected $fillable = [
        'tenant_id', 'token', 'client_name', 'client_email',
        'client_type', 'expires_at', 'used_at', 'created_by', 'bullion_client_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    public static function generate(int $tenantId, ?string $name = null, ?string $email = null, string $type = 'individual'): self
    {
        return self::create([
            'tenant_id'   => $tenantId,
            'token'       => Str::random(48),
            'client_name' => $name,
            'client_email'=> $email,
            'client_type' => $type,
            'expires_at'  => now()->addDays(7),
            'created_by'  => auth()->id(),
        ]);
    }
}
