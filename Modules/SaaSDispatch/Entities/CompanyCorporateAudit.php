<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyCorporateAudit extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['data' => 'array', 'created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \DomainException('Corporate audit history is immutable.'));
        static::deleting(fn () => throw new \DomainException('Corporate audit history cannot be deleted.'));
    }
}
