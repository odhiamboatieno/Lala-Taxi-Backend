@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_cancel_reason.title') }}</h4>
                    <div class="text-muted">Manage rider, driver, and system cancellation reasons without DataTables.</div>
                </div>
                @if(Auth::guard('admin')->user()->can('create_manage_reason'))
                    <a class="btn btn-primary" href="{{ url('admin/add-cancel-reason') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_cancel_reason.add') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/cancel-reason') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search reason">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cancelled By</label>
                    <select name="cancelled_by" class="form-select">
                        <option value="">All</option>
                        <option value="Driver" {{ ($filters['cancelled_by'] ?? '') === 'Driver' ? 'selected' : '' }}>Driver</option>
                        <option value="Rider" {{ ($filters['cancelled_by'] ?? '') === 'Rider' ? 'selected' : '' }}>Rider</option>
                        <option value="Admin" {{ ($filters['cancelled_by'] ?? '') === 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Company" {{ ($filters['cancelled_by'] ?? '') === 'Company' ? 'selected' : '' }}>Company</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" {{ ($filters['status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/cancel-reason') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Reason</th>
                            <th>Cancelled By</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cancelReasons as $reason)
                            <tr>
                                <td>#{{ $reason->id }}</td>
                                <td class="fw-semibold">{{ $reason->reason }}</td>
                                <td>{{ $reason->cancelled_by ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $reason->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $reason->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if(Auth::guard('admin')->user()->can('update_manage_reason'))
                                            <a href="{{ url('admin/edit-cancel-reason/' . $reason->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif
                                        @if(Auth::guard('admin')->user()->can('delete_manage_reason'))
                                            <a href="{{ url('admin/delete-cancel-reason/' . $reason->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this cancel reason?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No cancel reasons found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($cancelReasons->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $cancelReasons->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
