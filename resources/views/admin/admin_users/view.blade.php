@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ __('messages.blade_static.admin_users_management_715217a5') }}</h4>
                <div class="text-muted">Manage internal admin users with a standard paginated table.</div>
            </div>
            <a class="btn btn-success" href="{{ url('admin/add_admin_user') }}"><i class="fas fa-plus me-1"></i>{{ __('messages.blade_static.add_admin_user_2cc62dbd') }}</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/admin_user') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ID, username, email, status">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/admin_user') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>#{{ $admin->id }}</td>
                                <td>{{ $admin->username }}</td>
                                <td>{{ $admin->email }}</td>
                                <td><span class="badge {{ $admin->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $admin->status }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('admin/edit_admin_users/'.$admin->id) }}" class="btn btn-sm btn-outline-success"><i class="ri-pencil-line"></i></a>
                                        <a href="{{ url('admin/delete_admin_user/'.$admin->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this admin user?')"><i class="ri-delete-bin-line"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No admin users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $admins->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
