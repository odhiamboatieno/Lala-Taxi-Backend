<?php

namespace Modules\UserLevels\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\User;

class UserLevelAssignment extends Model
{
    protected $table = 'user_level_assignments';

    protected $guarded = [];

    protected $casts = [
        'metrics_snapshot' => 'array',
        'assigned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id', 'id');
    }
}
