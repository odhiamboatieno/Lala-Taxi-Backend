@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_mobile_app_version.title') }}</h4>
                    <div class="text-muted">Track rider and driver app releases without the old DataTables dependency.</div>
                </div>
                @if(Auth::guard('admin')->user()->can('manage_mobile_app_version'))
                    <a class="btn btn-primary" href="{{ url('admin/add_app_version') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_mobile_app_version.add') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/mobile_app_version') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Version</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search version">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Device</label>
                    <select name="device_type" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ ($filters['device_type'] ?? '') === '1' ? 'selected' : '' }}>Apple</option>
                        <option value="2" {{ ($filters['device_type'] ?? '') === '2' ? 'selected' : '' }}>Android</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">User</label>
                    <select name="user_type" class="form-select">
                        <option value="">All</option>
                        <option value="0" {{ ($filters['user_type'] ?? '') === '0' ? 'selected' : '' }}>Rider</option>
                        <option value="1" {{ ($filters['user_type'] ?? '') === '1' ? 'selected' : '' }}>Driver</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Force Update</label>
                    <select name="force_update" class="form-select">
                        <option value="">All</option>
                        <option value="0" {{ ($filters['force_update'] ?? '') === '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ ($filters['force_update'] ?? '') === '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/mobile_app_version') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Version</th>
                            <th>Device</th>
                            <th>User</th>
                            <th>Force Update</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versions as $version)
                            <tr>
                                <td>#{{ $version->id }}</td>
                                <td class="fw-semibold">{{ $version->version }}</td>
                                <td>{{ $version->add_device_type }}</td>
                                <td>{{ $version->add_user_type }}</td>
                                <td>{{ $version->add_force_update }}</td>
                                <td>{{ $version->created_at }}</td>
                                <td>{{ $version->updated_at }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('admin/edit_app_version/' . $version->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <a href="{{ url('admin/delete_app_version/' . $version->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this app version?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted">No app versions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($versions->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $versions->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
