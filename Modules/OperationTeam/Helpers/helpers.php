<?php

use Illuminate\Support\Facades\Session;
use Modules\OperationTeam\Entities\OperationTeamMember;

if (!function_exists('current_operation_team_member')) {
    function current_operation_team_member(): ?OperationTeamMember
    {
        static $member;

        if ($member !== null) {
            return $member;
        }

        $memberId = Session::get('operation_team_member_id');
        if (!$memberId) {
            return $member = null;
        }

        return $member = OperationTeamMember::query()->with('role')->find($memberId);
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
        if (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE !== 'company') {
            return false;
        }

        $member = current_operation_team_member();
        if (!$member) {
            return auth('company')->check();
        }

        return $member->hasPermission($permission);
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
