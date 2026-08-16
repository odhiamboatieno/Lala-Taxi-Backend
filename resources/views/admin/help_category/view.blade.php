@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_help.category') }}</h4>
                    <div class="text-muted">Organize help topics with a lightweight searchable listing.</div>
                </div>
                @if(Auth::guard('admin')->user()->can('manage_help'))
                    <a class="btn btn-primary" href="{{ url('admin/add_help_category') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_help.add_category') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/help_category') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search category name or description">
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
                    <a href="{{ url('admin/help_category') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>#{{ $category->id }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($category->description, 80) ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $category->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $category->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('admin/edit_help_category/' . $category->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <a href="{{ url('admin/delete_help_category/' . $category->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No help categories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
