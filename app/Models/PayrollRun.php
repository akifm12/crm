<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'tenant_id', 'run_number', 'period_start', 'period_end', 'pay_date',
        'total_gross', 'total_deductions', 'total_net', 'status', 'journal_entry_id',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'pay_date'         => 'date',
        'total_gross'      => 'decimal:4',
        'total_deductions' => 'decimal:4',
        'total_net'        => 'decimal:4',
    ];

    public function items() { return $this->hasMany(PayrollItem::class); }
    public function tenant(){ return $this->belongsTo(Tenant::class); }
}
