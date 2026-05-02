<?php
// app/Models/GoamlStaticConfig.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoamlStaticConfig extends Model
{
    protected $fillable = [
        'tenant_id', 'rentity_id', 'entity_name', 'entity_address',
        'entity_city', 'entity_country_code', 'entity_state',
        'mlro_gender', 'mlro_first_name', 'mlro_last_name', 'mlro_ssn',
        'mlro_id_number', 'mlro_nationality', 'mlro_email', 'mlro_occupation',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function isComplete(): bool
    {
        return !empty($this->rentity_id)
            && !empty($this->mlro_first_name)
            && !empty($this->mlro_last_name)
            && !empty($this->mlro_email)
            && !empty($this->entity_address);
    }
}
