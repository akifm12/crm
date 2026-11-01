<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'default_useful_life_months', 'depreciation_method',
        'asset_account_id', 'accum_depreciation_account_id', 'depreciation_expense_account_id',
    ];

    public function assets() { return $this->hasMany(FixedAsset::class, 'category_id'); }
}
