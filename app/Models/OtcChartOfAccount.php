<?php
// app/Models/OtcChartOfAccount.php — B-Book's own chart of accounts, deliberately
// separate from ChartOfAccount (A-Book). See migration for why.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class OtcChartOfAccount extends Model
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
        return $this->hasMany(OtcJournalEntryLine::class);
    }

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
