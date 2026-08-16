<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'name',
        'alias',
        'version',
        'description',
        'author',
        'path',
        'providers',
        'requires',
        'enabled',
        'autoload',
        'has_migrations',
        'migrated',
        'checksum',
        'installed_at',
        'enabled_at',
    ];

    protected $casts = [
        'providers' => 'array',
        'requires' => 'array',
        'enabled' => 'boolean',
        'autoload' => 'boolean',
        'has_migrations' => 'boolean',
        'migrated' => 'boolean',
        'installed_at' => 'datetime',
        'enabled_at' => 'datetime',
    ];

    public function isEnabled(): bool
    {
        return (bool) $this->enabled;
    }

    public function getProviderClasses(): array
    {
        return is_array($this->providers) ? $this->providers : [];
    }
}
