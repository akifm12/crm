<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'basic_salary', 'allowances', 'deductions', 'net_pay', 'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:4',
        'allowances'   => 'decimal:4',
        'deductions'   => 'decimal:4',
        'net_pay'      => 'decimal:4',
    ];

    public function payrollRun() { return $this->belongsTo(PayrollRun::class); }
    public function employee()   { return $this->belongsTo(Employee::class); }
}
