<?php
// app/Models/OtcJournalEntry.php — B-Book's own journal, separate from JournalEntry.

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtcJournalEntry extends Model
{
    protected $fillable = [
        'tenant_id', 'entry_date', 'reference', 'source_type', 'source_id', 'bullion_client_id',
        'currency_code', 'exchange_rate', 'status', 'voided_journal_entry_id',
        'created_by', 'memo',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'exchange_rate' => 'decimal:6',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(BullionClient::class, 'bullion_client_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OtcJournalEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'voided_journal_entry_id');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }
}
