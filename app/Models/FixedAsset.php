<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'tenant_id', 'category_id', 'asset_number', 'name', 'description', 'serial_number',
        'location', 'assigned_to', 'purchase_date', 'in_service_date', 'disposal_date',
        'purchase_cost', 'residual_value', 'depreciable_amount', 'depreciation_method',
        'useful_life_years', 'depreciation_rate', 'accumulated_depreciation', 'net_book_value',
        'asset_account_id', 'accum_depr_account_id', 'depr_expense_account_id', 'status',
        'supplier_id', 'purchase_invoice_ref', 'insurance_policy_no', 'insurance_expiry', 'notes',
        'is_deleted', 'deleted_at',
    ];

    protected $casts = [
        'purchase_date'    => 'date',
        'in_service_date'  => 'date',
        'disposal_date'    => 'date',
        'insurance_expiry' => 'date',
        'is_deleted'       => 'boolean',
    ];

    public function category()        { return $this->belongsTo(AssetCategory::class); }
    public function supplier()        { return $this->belongsTo(Supplier::class); }
    public function assetAccount()    { return $this->belongsTo(ChartOfAccount::class, 'asset_account_id'); }
    public function accumDeprAccount(){ return $this->belongsTo(ChartOfAccount::class, 'accum_depr_account_id'); }
    public function deprExpAccount()  { return $this->belongsTo(ChartOfAccount::class, 'depr_expense_account_id'); }
    public function depreciationLines(){ return $this->hasMany(DepreciationLine::class, 'asset_id'); }
}
