@extends('admin.template')

@section('title-sub', 'Parcel Package Sizes')
@section('pagetitle', 'Parcel Package Sizes')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Package Sizes</h4>
                <div class="text-muted">Manage package-size options and pricing multipliers for parcel jobs.</div>
            </div>
            <a class="btn btn-primary" href="{{ url('admin/add_parcel_package_size') }}">Add Package Size</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/parcel_package_sizes') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search size key or title">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/parcel_package_sizes') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Key</th>
                            <th>Title</th>
                            <th>Multiplier</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sizes as $size)
                            <tr>
                                <td>{{ $size->key }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $size->title }}</div>
                                    <div class="text-muted small">{{ $size->subtitle ?: $size->description ?: '-' }}</div>
                                </td>
                                <td>{{ number_format((float) $size->price_multiplier, 2) }}</td>
                                <td><span class="badge {{ $size->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $size->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-end d-flex justify-content-end gap-2">
                                    <a href="{{ url('admin/edit_parcel_package_size/'.$size->id) }}" class="btn btn-sm btn-outline-success">Edit</a>
                                    <a href="{{ url('admin/toggle_parcel_package_size_status/'.$size->id) }}" class="btn btn-sm btn-outline-primary">Toggle</a>
                                    <a href="{{ url('admin/delete_parcel_package_size/'.$size->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this package size?')">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No package sizes found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sizes->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $sizes->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
