<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'tenant_id', 'run_number', 'pay_period_start', 'pay_period_end', 'pay_date', 'status',
        'total_basic_salary', 'total_allowances', 'total_deductions', 'total_net_pay',
        'employee_count', 'journal_entry_id', 'wps_file_generated', 'notes', 'is_deleted',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end'   => 'date',
        'pay_date'         => 'date',
        'is_deleted'       => 'boolean',
    ];

    public function lines()       { return $this->hasMany(PayrollLine::class); }
    public function journalEntry(){ return $this->belongsTo(JournalEntry::class); }
}
