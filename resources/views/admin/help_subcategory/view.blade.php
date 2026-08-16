@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_help.subcategory') }}</h4>
                    <div class="text-muted">Keep subtopics tidy with the same responsive custom table pattern.</div>
                </div>
                @if(Auth::guard('admin')->user()->can('manage_help'))
                    <a class="btn btn-primary" href="{{ url('admin/add_help_subcategory') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_help.add_subcategory') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/help_subcategory') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search subcategory name or description">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All</option>
                        @foreach($categoryOptions as $category)
                            <option value="{{ $category->id }}" {{ (string) ($filters['category_id'] ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
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
                    <a href="{{ url('admin/help_subcategory') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subcategories as $subcategory)
                            <tr>
                                <td>#{{ $subcategory->id }}</td>
                                <td>{{ $subcategory->category_name ?: '-' }}</td>
                                <td class="fw-semibold">{{ $subcategory->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($subcategory->description, 80) ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $subcategory->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $subcategory->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('admin/edit_help_subcategory/' . $subcategory->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <a href="{{ url('admin/delete_help_subcategory/' . $subcategory->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this subcategory?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No help subcategories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subcategories->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $subcategories->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
