<?php

namespace Modules\OperationTeam\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class OperationTeamMember extends Model
{
    protected $table = 'operation_team_members';

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(OperationTeamRole::class, 'role_id');
    }

    public function setPasswordAttribute($input): void
    {
        if ($input === null || $input === '') {
            return;
        }

        $this->attributes['password'] = Hash::make($input);
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->status || !$this->role || !$this->role->status) {
            return false;
        }

        return in_array($permission, $this->role->permissions ?? [], true);
    }
}
