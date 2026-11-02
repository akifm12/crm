<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'name_arabic', 'category', 'unit',
        'gold_purity', 'weight_per_unit_grams', 'current_price', 'cost_price',
        'stock_qty', 'vat_rate', 'sales_account_id', 'inventory_account_id',
        'cogs_account_id', 'is_active', 'notes', 'created_by',
    ];

    protected $casts = [
        'current_price'          => 'float',
        'cost_price'             => 'float',
        'stock_qty'              => 'float',
        'weight_per_unit_grams'  => 'float',
        'gold_purity'            => 'float',
        'vat_rate'               => 'float',
        'is_active'              => 'boolean',
    ];

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function salesAccount() { return $this->belongsTo(ChartOfAccount::class, 'sales_account_id'); }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'gold_bar'   => 'Gold Bar',
            'gold_coin'  => 'Gold Coin',
            'jewelry'    => 'Jewelry',
            'silver_bar' => 'Silver Bar',
            'silver_coin'=> 'Silver Coin',
            default      => ucfirst($this->category ?? 'Other'),
        };
    }

    public function vatLabel(): string
    {
        return $this->vat_rate > 0 ? "{$this->vat_rate}% VAT" : 'Zero-rated';
    }
}
