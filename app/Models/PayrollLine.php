<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLine extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'tenant_id', 'basic_salary', 'housing_allowance',
        'transport_allowance', 'other_allowances', 'overtime_amount', 'gross_pay',
        'loan_deduction', 'absence_deduction', 'other_deductions', 'total_deductions',
        'net_pay', 'days_worked', 'days_absent', 'overtime_hours', 'eosb_accrual', 'remarks',
    ];

    public function payrollRun() { return $this->belongsTo(PayrollRun::class); }
    public function employee()   { return $this->belongsTo(Employee::class); }
}
