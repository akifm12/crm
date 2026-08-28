<?php
// app/Models/AppSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'app_settings_map';

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::map()[$key] ?? null;

        return $value !== null ? $value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /** All settings as a flat [key => value] map, cached for a few minutes. */
    public static function map(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}
