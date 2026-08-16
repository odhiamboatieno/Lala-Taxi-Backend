@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Employee Attributes</h1>
    </section>
    <section class="content">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Employee Role List</h4>
                    <a href="{{ url('company/operation-team/roles/create') }}" class="btn btn-primary">Add Role</a>
                </div>
                <form method="get" class="row g-3 mb-3">
                    <div class="col-md-4"><input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search role"></div>
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
                                <th>Role Name</th>
                                <th>Module Access</th>
                                <th>Employees</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($roles as $index => $role)
                            <tr>
                                <td>{{ $roles->firstItem() + $index }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ implode(', ', $role->permissions ?? []) }}</td>
                                <td>{{ $role->members_count }}</td>
                                <td>{{ $role->status ? 'Active' : 'Inactive' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ url('company/operation-team/roles/'.$role->id.'/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ url('company/operation-team/roles/'.$role->id.'/toggle') }}" method="post" style="display:inline">@csrf<button class="btn btn-sm btn-warning">Toggle</button></form>
                                    <form action="{{ url('company/operation-team/roles/'.$role->id) }}" method="post" style="display:inline">@csrf @method('DELETE')<button onclick="return confirm('Delete this role?')" class="btn btn-sm btn-danger">Delete</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No employee roles found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $roles->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
