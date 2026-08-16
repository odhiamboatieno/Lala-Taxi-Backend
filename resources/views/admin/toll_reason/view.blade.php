@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.additional_reasons') }}</h4>
                    <div class="text-muted">Manage additional charge reasons with a clean paginated table.</div>
                </div>
                @if(Auth::guard('admin')->user()->can('create_additional_reason'))
                    <a class="btn btn-primary" href="{{ url('admin/add-additional-reason') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.add_additional_reason') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/additional-reasons') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search reason">
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
                    <a href="{{ url('admin/additional-reasons') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tollReasons as $reason)
                            <tr>
                                <td>#{{ $reason->id }}</td>
                                <td class="fw-semibold">{{ $reason->reason }}</td>
                                <td>
                                    <span class="badge {{ $reason->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $reason->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($reason->id != 1)
                                            <a href="{{ url('admin/edit-additional-reason/' . $reason->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                            <a href="{{ url('admin/delete-additional-reason/' . $reason->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this additional reason?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">No additional reasons found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tollReasons->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $tollReasons->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
