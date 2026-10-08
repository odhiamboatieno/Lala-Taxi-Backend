<?php

use Illuminate\Support\Facades\Session;
use Modules\OperationTeam\Entities\OperationTeamMember;

if (!function_exists('current_operation_team_member')) {
    function current_operation_team_member(): ?OperationTeamMember
    {
        $memberId = Session::get('operation_team_member_id');
        if (!$memberId) {
            return null;
        }
        // Keep the lookup request-local; long-lived workers must not reuse another session's member.
        $key = 'operation_team_member:'.$memberId;
        if (!request()->attributes->has($key)) {
            request()->attributes->set($key, OperationTeamMember::query()->with('role')->find($memberId));
        }
        $member = request()->attributes->get($key);
        return $member && (int) $member->company_id === (int) auth('company')->id()
            && (!$member->role || (int) $member->role->company_id === (int) $member->company_id) ? $member : null;
    }
}

if (!function_exists('operation_team_permission_modules')) {
    function operation_team_permission_modules(): array
    {
        return config('operation_team.module_permissions', []);
    }
}

if (!function_exists('operation_team_available_permissions')) {
    function operation_team_available_permissions(): array
    {
        return collect(operation_team_permission_modules())->flatten()->unique()->values()->all();
    }
}

if (!function_exists('operation_team_can')) {
    function operation_team_can(string $permission): bool
    {
        if (request()->segment(1) !== 'company' && !request()->is('api/company/*')) {
            return false;
        }

        $member = current_operation_team_member();
        if (!$member) {
            return !Session::has('operation_team_member_id') && auth('company')->check();
        }
        return $member->status && $member->role && $member->role->status && $member->hasPermission($permission);
    }
}

if (!function_exists('operation_team_allows_any')) {
    function operation_team_allows_any(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (operation_team_can($permission)) {
                return true;
            }
        }

        return false;
    }
}
