<?php

namespace Modules\OperationTeam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\OperationTeam\Entities\OperationTeamRole;
use Modules\OperationTeam\Http\Requests\OperationTeamRoleRequest;

class OperationTeamRoleController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        $query = OperationTeamRole::query()->where('company_id', $company->id);

        if ($search = trim((string) $request->get('search'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if (($scope = $request->get('status_scope')) === 'active') {
            $query->where('status', true);
        } elseif ($scope === 'inactive') {
            $query->where('status', false);
        }

        $roles = $query->withCount('members')->orderBy('id')->paginate(12)->withQueryString();

        return view('operationteam::roles.index', [
            'roles' => $roles,
            'permissionModules' => operation_team_permission_modules(),
            'statusScope' => $scope ?: 'all',
            'search' => $search ?? '',
        ]);
    }

    public function create()
    {
        return view('operationteam::roles.form', [
            'role' => new OperationTeamRole(),
            'permissionModules' => operation_team_permission_modules(),
            'selectedPermissions' => [],
        ]);
    }

    public function store(OperationTeamRoleRequest $request)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        OperationTeamRole::query()->create([
            'company_id' => $company->id,
            'name' => $request->name,
            'permissions' => array_values(array_unique($request->permissions ?? [])),
            'status' => $request->boolean('status', true),
        ]);

        flashMessage('success', 'Operation team role created successfully');

        return redirect('company/operation-team/roles');
    }

    public function edit(OperationTeamRole $role)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $role->company_id === $company->id, 404);

        return view('operationteam::roles.form', [
            'role' => $role,
            'permissionModules' => operation_team_permission_modules(),
            'selectedPermissions' => $role->permissions ?? [],
        ]);
    }

    public function update(OperationTeamRoleRequest $request, OperationTeamRole $role)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $role->company_id === $company->id, 404);

        $role->update([
            'name' => $request->name,
            'permissions' => array_values(array_unique($request->permissions ?? [])),
            'status' => $request->boolean('status', true),
        ]);

        flashMessage('success', 'Operation team role updated successfully');

        return redirect('company/operation-team/roles');
    }

    public function destroy(OperationTeamRole $role)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $role->company_id === $company->id, 404);

        $role->delete();
        flashMessage('success', 'Operation team role deleted successfully');

        return redirect('company/operation-team/roles');
    }

    public function toggle(OperationTeamRole $role)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $role->company_id === $company->id, 404);

        $role->status = !$role->status;
        $role->save();

        flashMessage('success', 'Operation team role status updated');

        return back();
    }
}
