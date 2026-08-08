<?php
// app/Models/UserDeadline.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeadline extends Model
{
    protected $fillable = ['public_user_id', 'type', 'label', 'due_date', 'notes'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public static function typeLabels(): array
    {
        return [
            'trade_license'       => 'Trade License',
            'ejari'                => 'Ejari Contract',
            'passport'             => 'Passport',
            'eid'                  => 'Emirates ID',
            'dnfbp_registration'   => 'DNFBP / goAML Registration',
            'pi_insurance'         => 'Professional Indemnity Insurance',
            'regulatory_license'   => 'Regulatory License',
            'other'                => 'Other',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function displayLabel(): string
    {
        return $this->label ?: (static::typeLabels()[$this->type] ?? ucfirst($this->type));
    }

    /**
     * Badge color tier ported from tenant/expiry.blade.php's convention:
     * red = expired, amber = due within 30 days, blue = due 31-90+ days out.
     */
    public function badge(): array
    {
        $days = now()->startOfDay()->diffInDays($this->due_date, false);

        if ($days < 0) {
            return ['bg-red-100 text-red-700', 'Expired · ' . $this->due_date->format('d M Y')];
        }

        if ($days <= 30) {
            return ['bg-amber-100 text-amber-700', $this->due_date->format('d M Y') . ' · ' . $days . 'd'];
        }

        return ['bg-blue-100 text-blue-700', $this->due_date->format('d M Y')];
    }
}
