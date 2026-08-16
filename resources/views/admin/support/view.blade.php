@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    @php
        $canManageSupport = LOGIN_USER_TYPE === 'company' || Auth::guard('admin')->user()?->can('manage_support');
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_support.title') }}</h4>
                    <div class="text-muted">Manage support channels with the same responsive table layout used across the dashboard.</div>
                </div>
                @if($canManageSupport)
                    <a class="btn btn-primary" href="{{ url(LOGIN_USER_TYPE . '/add_support') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_support.add') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url(LOGIN_USER_TYPE . '/support') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name, link, owner, or status">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" {{ ($filters['status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Owner</label>
                    <select name="owner" class="form-select">
                        <option value="">All</option>
                        <option value="All" {{ ($filters['owner'] ?? '') === 'All' ? 'selected' : '' }}>All</option>
                        <option value="Rider" {{ ($filters['owner'] ?? '') === 'Rider' ? 'selected' : '' }}>Rider</option>
                        <option value="Driver" {{ ($filters['owner'] ?? '') === 'Driver' ? 'selected' : '' }}>Driver</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url(LOGIN_USER_TYPE . '/support') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Support</th>
                            <th>Link</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supports as $support)
                            <tr>
                                <td>#{{ $support->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($support->image)
                                            <img src="{{ $support->image_src }}" alt="{{ $support->name }}" class="rounded" style="width:42px;height:42px;object-fit:cover;">
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $support->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 260px;">{{ $support->link }}</div>
                                </td>
                                <td>{{ $support->Owner ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $support->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $support->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url(LOGIN_USER_TYPE . '/edit_support/' . $support->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        @if(!in_array((string) $support->id, ['1', '2'], true))
                                            <a href="{{ url(LOGIN_USER_TYPE . '/delete_support/' . $support->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this support item?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No support records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($supports->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $supports->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
