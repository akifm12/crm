<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps  = false;
    public $incrementing = false;
    protected $keyType  = 'string';
    protected $primaryKey = 'country_code';

    protected $fillable = ['country_code', 'country_name'];

    public static function dropdown(): array
    {
        return static::orderBy('country_name')->pluck('country_name', 'country_code')->toArray();
    }
}
