@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    @php
        $canManageBanner = Auth::guard('admin')->user()->can('manage_banner');
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.blade_static.manage_banners_ec8d2e20') }}</h4>
                    <div class="text-muted">Review promotional banners with a simpler responsive listing and quick actions.</div>
                </div>
                @if($canManageBanner)
                    <a class="btn btn-primary" href="{{ url('admin/add_banner') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.blade_static.add_banner_b5b47e59') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/banner') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name, title, subtitle, or link">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="Ride" {{ ($filters['type'] ?? '') === 'Ride' ? 'selected' : '' }}>Ride</option>
                        <option value="Parcel" {{ ($filters['type'] ?? '') === 'Parcel' ? 'selected' : '' }}>Parcel</option>
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
                    <a href="{{ url('admin/banner') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Banner</th>
                            <th>Type</th>
                            <th>Headline</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $banner)
                            <tr>
                                <td>#{{ $banner->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($banner->image)
                                            <img src="{{ $banner->image_src }}" alt="{{ $banner->name }}" class="rounded" style="width:56px;height:42px;object-fit:cover;">
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $banner->name }}</div>
                                            <div class="text-muted small">{{ $banner->subtitle }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $banner->type }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $banner->title }}</div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 220px;">{{ $banner->link ?: '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $banner->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $banner->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('admin/edit_banner/' . $banner->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        @if(!in_array((string) $banner->id, ['1', '2'], true))
                                            <a href="{{ url('admin/delete_banner/' . $banner->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this banner?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No banners found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($banners->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $banners->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
