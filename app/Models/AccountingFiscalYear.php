<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingFiscalYear extends Model
{
    protected $fillable = ['tenant_id', 'name', 'start_date', 'end_date', 'status', 'closed_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'closed_at'  => 'datetime',
    ];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function isOpen()  { return $this->status === 'open'; }
}
