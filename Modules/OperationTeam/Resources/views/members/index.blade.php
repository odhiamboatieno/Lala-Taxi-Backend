@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Employee List</h1>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Operation Team Members</h4>
                    <a href="{{ url('company/operation-team/members/create') }}" class="btn btn-primary">Add Employee</a>
                </div>
                <form method="get" class="row g-3 mb-3">
                    <div class="col-md-4"><input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search employee"></div>
                    <div class="col-md-3">
                        <select name="status_scope" class="form-control">
                            <option value="all" {{ $statusScope == 'all' ? 'selected' : '' }}>All</option>
                            <option value="active" {{ $statusScope == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $statusScope == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-success w-100">Search</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Module Access</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($members as $index => $member)
                            <tr>
                                <td>{{ $members->firstItem() + $index }}</td>
                                <td>{{ trim($member->first_name.' '.$member->last_name) }}</td>
                                <td>{{ $member->role->name ?? '-' }}</td>
                                <td>{{ implode(', ', $member->role->permissions ?? []) }}</td>
                                <td>{{ $member->status ? 'Active' : 'Inactive' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ url('company/operation-team/members/'.$member->id.'/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ url('company/operation-team/members/'.$member->id.'/toggle') }}" method="post" style="display:inline">@csrf<button class="btn btn-sm btn-warning">Toggle</button></form>
                                    <form action="{{ url('company/operation-team/members/'.$member->id) }}" method="post" style="display:inline">@csrf @method('DELETE')<button onclick="return confirm('Delete this employee?')" class="btn btn-sm btn-danger">Delete</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No operation team members found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $members->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
