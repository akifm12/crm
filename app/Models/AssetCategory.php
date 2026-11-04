<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'depreciation_method', 'useful_life_years',
        'residual_value_percent', 'asset_account_id', 'accum_depr_account_id', 'depr_expense_account_id',
    ];

    public function assets()          { return $this->hasMany(FixedAsset::class, 'category_id'); }
    public function assetAccount()    { return $this->belongsTo(ChartOfAccount::class, 'asset_account_id'); }
    public function accumDeprAccount(){ return $this->belongsTo(ChartOfAccount::class, 'accum_depr_account_id'); }
    public function deprExpAccount()  { return $this->belongsTo(ChartOfAccount::class, 'depr_expense_account_id'); }
}
