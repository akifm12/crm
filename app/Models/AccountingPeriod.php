<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingPeriod extends Model
{
    protected $fillable = ['tenant_id','fiscal_year_id','name','start_date','end_date','status'];

    public function tenant() { return $this->belongsTo(Tenant::class); }
}
