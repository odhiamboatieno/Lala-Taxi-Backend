<?php

use Illuminate\Support\Facades\Route;
use Modules\OperationTeam\Http\Controllers\OperationTeamMemberController;
use Modules\OperationTeam\Http\Controllers\OperationTeamRoleController;

Route::group([
    'prefix' => 'company',
    'middleware' => ['web', 'admin_guest'],
], function () {
    Route::get('operation-team/roles', [OperationTeamRoleController::class, 'index'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.index');
    Route::get('operation-team/roles/create', [OperationTeamRoleController::class, 'create'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.create');
    Route::post('operation-team/roles', [OperationTeamRoleController::class, 'store'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.store');
    Route::get('operation-team/roles/{role}/edit', [OperationTeamRoleController::class, 'edit'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.edit');
    Route::put('operation-team/roles/{role}', [OperationTeamRoleController::class, 'update'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.update');
    Route::delete('operation-team/roles/{role}', [OperationTeamRoleController::class, 'destroy'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.destroy');
    Route::post('operation-team/roles/{role}/toggle', [OperationTeamRoleController::class, 'toggle'])
        ->middleware('admin_can:manage_operation_team_roles')
        ->name('operationteam.roles.toggle');

    Route::get('operation-team/members', [OperationTeamMemberController::class, 'index'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.index');
    Route::get('operation-team/members/create', [OperationTeamMemberController::class, 'create'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.create');
    Route::post('operation-team/members', [OperationTeamMemberController::class, 'store'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.store');
    Route::get('operation-team/members/{member}/edit', [OperationTeamMemberController::class, 'edit'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.edit');
    Route::put('operation-team/members/{member}', [OperationTeamMemberController::class, 'update'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.update');
    Route::delete('operation-team/members/{member}', [OperationTeamMemberController::class, 'destroy'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.destroy');
    Route::post('operation-team/members/{member}/toggle', [OperationTeamMemberController::class, 'toggle'])
        ->middleware('admin_can:manage_operation_team_members')
        ->name('operationteam.members.toggle');
});
