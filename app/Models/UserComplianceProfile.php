<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserComplianceProfile extends Model
{
    protected $fillable = [
        'user_id', 'name', 'regulator_id', 'license_activity_id', 'notify_days_before',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function regulator()
    {
        return $this->belongsTo(Regulator::class);
    }

    public function licenseActivity()
    {
        return $this->belongsTo(LicenseActivity::class);
    }
}
