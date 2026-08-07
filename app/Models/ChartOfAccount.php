<?php
// app/Models/ChartOfAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ChartOfAccount extends Model
{
    public const TYPES = ['asset', 'liability', 'equity', 'income', 'expense'];

    protected $fillable = [
        'tenant_id', 'parent_id', 'code', 'name', 'type', 'subtype',
        'normal_balance', 'is_system', 'is_active', 'description',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Net balance as of a given date, signed according to this account's normal balance
     * (i.e. a debit-normal account with more debits than credits returns a positive number).
     * Only counts posted journal entries.
     */
    public function balance(?Carbon $asOf = null): float
    {
        $query = $this->lines()
            ->whereHas('journalEntry', function ($q) use ($asOf) {
                $q->where('status', 'posted');
                if ($asOf) {
                    $q->where('entry_date', '<=', $asOf->toDateString());
                }
            });

        $totalDebit = (float) $query->sum('base_debit');
        $totalCredit = (float) $query->sum('base_credit');

        return $this->normal_balance === 'debit'
            ? $totalDebit - $totalCredit
            : $totalCredit - $totalDebit;
    }
}
