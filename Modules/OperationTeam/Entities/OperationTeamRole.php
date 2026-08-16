<?php

namespace Modules\OperationTeam\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationTeamRole extends Model
{
    protected $table = 'operation_team_roles';

    protected $guarded = [];

    protected $casts = [
        'permissions' => 'array',
        'status' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OperationTeamMember::class, 'role_id');
    }
}
