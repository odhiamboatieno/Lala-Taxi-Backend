<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelSetting extends Model
{
    protected $table = 'parcel_settings';

    protected $fillable = [
        'key', 'value', 'type', 'description',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default ?? config("parcel.{$key}");
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => (string) $value,
        };
    }
}
