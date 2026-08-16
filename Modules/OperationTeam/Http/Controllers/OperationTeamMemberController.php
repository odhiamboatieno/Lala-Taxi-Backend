<?php

namespace Modules\OperationTeam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\OperationTeam\Entities\OperationTeamMember;
use Modules\OperationTeam\Entities\OperationTeamRole;
use Modules\OperationTeam\Http\Requests\OperationTeamMemberRequest;

class OperationTeamMemberController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        $query = OperationTeamMember::query()
            ->with('role')
            ->where('company_id', $company->id);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('mobile_number', 'like', '%'.$search.'%');
            });
        }

        if (($scope = $request->get('status_scope')) === 'active') {
            $query->where('status', true);
        } elseif ($scope === 'inactive') {
            $query->where('status', false);
        }

        $members = $query->orderBy('id')->paginate(12)->withQueryString();

        return view('operationteam::members.index', [
            'members' => $members,
            'statusScope' => $scope ?: 'all',
            'search' => $search ?? '',
        ]);
    }

    public function create()
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        return view('operationteam::members.form', [
            'member' => new OperationTeamMember(),
            'roles' => OperationTeamRole::query()->where('company_id', $company->id)->where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(OperationTeamMemberRequest $request)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        $member = new OperationTeamMember();
        $member->company_id = $company->id;
        $this->fillAndSave($member, $request);

        flashMessage('success', 'Operation team member created successfully');

        return redirect('company/operation-team/members');
    }

    public function edit(OperationTeamMember $member)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $member->company_id === $company->id, 404);

        return view('operationteam::members.form', [
            'member' => $member,
            'roles' => OperationTeamRole::query()->where('company_id', $company->id)->where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(OperationTeamMemberRequest $request, OperationTeamMember $member)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $member->company_id === $company->id, 404);

        $this->fillAndSave($member, $request);

        flashMessage('success', 'Operation team member updated successfully');

        return redirect('company/operation-team/members');
    }

    public function destroy(OperationTeamMember $member)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $member->company_id === $company->id, 404);

        $member->delete();
        flashMessage('success', 'Operation team member deleted successfully');

        return redirect('company/operation-team/members');
    }

    public function toggle(OperationTeamMember $member)
    {
        $company = Auth::guard('company')->user();
        abort_unless($company && $member->company_id === $company->id, 404);

        $member->status = !$member->status;
        $member->save();

        flashMessage('success', 'Operation team member status updated');

        return back();
    }

    protected function fillAndSave(OperationTeamMember $member, OperationTeamMemberRequest $request): void
    {
        $member->first_name = $request->first_name;
        $member->last_name = $request->last_name;
        $member->email = $request->email;
        $member->country_code = $request->country_code;
        $member->mobile_number = $request->mobile_number;
        $member->address = $request->address;
        $member->identity_type = $request->identity_type;
        $member->identity_number = $request->identity_number;
        $member->role_id = $request->role_id;
        $member->status = $request->boolean('status', true);

        if ($request->filled('password')) {
            $member->password = $request->password;
        }

        if ($request->hasFile('image')) {
            $member->image = $request->file('image')->store('operation-team', 'public');
        }

        if ($request->hasFile('identity_image')) {
            $member->identity_image = $request->file('identity_image')->store('operation-team', 'public');
        }

        $member->save();
    }
}
