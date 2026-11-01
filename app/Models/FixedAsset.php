<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'tenant_id', 'asset_number', 'name', 'category_id', 'purchase_date',
        'purchase_cost', 'salvage_value', 'useful_life_months', 'depreciation_method',
        'accumulated_depreciation', 'net_book_value', 'status', 'last_depreciation_date', 'notes',
    ];

    protected $casts = [
        'purchase_date'            => 'date',
        'last_depreciation_date'   => 'date',
        'purchase_cost'            => 'decimal:4',
        'salvage_value'            => 'decimal:4',
        'accumulated_depreciation' => 'decimal:4',
        'net_book_value'           => 'decimal:4',
    ];

    public function category() { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function tenant()   { return $this->belongsTo(Tenant::class); }

    public function monthlyDepreciation(): float
    {
        if ($this->useful_life_months <= 0) return 0;
        return (float) (($this->purchase_cost - $this->salvage_value) / $this->useful_life_months);
    }
}
