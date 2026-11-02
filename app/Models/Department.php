<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['tenant_id', 'code', 'name', 'manager_id', 'is_active'];

    public function employees() { return $this->hasMany(Employee::class); }
    public function manager()   { return $this->belongsTo(Employee::class, 'manager_id'); }
}
