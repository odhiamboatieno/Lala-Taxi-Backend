<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverScoreSetting extends Model
{
    protected $table = 'driver_score_settings';

    protected $guarded = [];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function publicSettings()
    {
        return static::where('is_public', true)->orderBy('group')->orderBy('id')->get();
    }

    public static function castValue($value, string $type)
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => is_array($value) ? $value : json_decode((string) $value, true),
            default => $value,
        };
    }
}
