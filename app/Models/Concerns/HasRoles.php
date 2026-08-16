<?php

namespace App\Models\Concerns;

use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function attachRole($role)
    {
        $roleId = $role instanceof Role ? $role->getKey() : $role;

        return $this->roles()->syncWithoutDetaching([$roleId]);
    }

    public function detachRole($role)
    {
        $roleId = $role instanceof Role ? $role->getKey() : $role;

        return $this->roles()->detach($roleId);
    }

    public function hasPermission($permissions)
    {
        $permissions = (array) $permissions;

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    public function can($ability, $arguments = [])
    {
        return $this->hasPermission($ability);
    }
}
