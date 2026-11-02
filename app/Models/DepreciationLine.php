<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepreciationLine extends Model
{
    protected $fillable = [
        'tenant_id', 'asset_id', 'period_start', 'period_end',
        'depreciation_amount', 'accumulated_depreciation', 'net_book_value', 'journal_entry_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function asset()        { return $this->belongsTo(FixedAsset::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
}
