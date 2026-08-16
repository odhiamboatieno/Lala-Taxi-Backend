@extends('admin.template')

@section('title-sub', 'Parcel Categories')
@section('pagetitle', 'Parcel Categories')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Parcel Categories</h4>
                <div class="text-muted">Manage parcel category labels, pricing multipliers, and active status.</div>
            </div>
            <a class="btn btn-primary" href="{{ url('admin/add_parcel_category') }}">Add Category</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/parcel_categories') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search category key or title">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/parcel_categories') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Key</th>
                            <th>Title</th>
                            <th>Base Fare</th>
                            <th>Multiplier</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->key }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $category->title }}</div>
                                    <div class="text-muted small">{{ $category->description ?: '-' }}</div>
                                </td>
                                <td>{{ currency_symbol().number_format((float) $category->base_fare, 2) }}</td>
                                <td>{{ number_format((float) $category->multiplier, 2) }}</td>
                                <td><span class="badge {{ $category->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-end d-flex justify-content-end gap-2">
                                    <a href="{{ url('admin/edit_parcel_category/'.$category->id) }}" class="btn btn-sm btn-outline-success">Edit</a>
                                    <a href="{{ url('admin/toggle_parcel_category_status/'.$category->id) }}" class="btn btn-sm btn-outline-primary">Toggle</a>
                                    <a href="{{ url('admin/delete_parcel_category/'.$category->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this parcel category?')">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No parcel categories found.</td></tr>
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
