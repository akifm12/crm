<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'department_id', 'first_name', 'last_name', 'full_name',
        'nationality', 'date_of_birth', 'gender', 'marital_status', 'emirates_id', 'passport_no',
        'visa_no', 'visa_expiry', 'labour_card_no', 'designation', 'join_date', 'end_date',
        'status', 'basic_salary', 'housing_allowance', 'transport_allowance', 'other_allowances',
        'gross_salary', 'payment_frequency', 'bank_name', 'bank_account_no', 'iban',
        'wps_routing_code', 'salary_account_id', 'annual_leave_days', 'sick_leave_days', 'notes',
        'is_deleted', 'deleted_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date'     => 'date',
        'end_date'      => 'date',
        'visa_expiry'   => 'date',
        'is_deleted'    => 'boolean',
    ];

    public function department()    { return $this->belongsTo(Department::class); }
    public function salaryAccount() { return $this->belongsTo(ChartOfAccount::class, 'salary_account_id'); }
    public function payrollLines()  { return $this->hasMany(PayrollLine::class); }
}
