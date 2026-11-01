<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_number', 'full_name', 'position', 'department_id',
        'basic_salary', 'join_date', 'status', 'notes',
    ];

    protected $casts = [
        'join_date'     => 'date',
        'basic_salary'  => 'decimal:4',
    ];

    public function department()  { return $this->belongsTo(Department::class); }
    public function payrollItems(){ return $this->hasMany(PayrollItem::class); }
}
