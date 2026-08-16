@extends('admin.template')

@section('title-sub', 'Vehicle Make')
@section('pagetitle', 'Vehicle Make')

@section('main')
@php
    $canCreate = Auth::guard('admin')->user()->can('create_vehicle_make');
    $canUpdate = Auth::guard('admin')->user()->can('update_vehicle_make');
    $canDelete = Auth::guard('admin')->user()->can('delete_vehicle_make');
@endphp
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_vehicle_make.title') }}</h4>
                    <div class="text-muted">Manage available vehicle brands and keep them consistent across vehicle setup.</div>
                </div>
                @if($canCreate)
                    <a class="btn btn-primary" href="{{ url('admin/add-vehicle-make') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_vehicle_make.add_vehicle_make') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/vehicle_make') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by make name or status">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/vehicle_make') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>SL</th>
                            <th>Make</th>
                            <th>Models</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($makes as $index => $make)
                            <tr>
                                <td>{{ $makes->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $make->make_vehicle_name }}</div>
                                    <div class="text-muted small">ID #{{ $make->id }}</div>
                                </td>
                                <td>{{ $make->vehicle_model_count }}</td>
                                <td>
                                    <span class="badge {{ $make->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $make->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($canUpdate)
                                            <a href="{{ url('admin/edit-vehicle-make/'.$make->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif
                                        @if($canDelete)
                                            <a href="{{ url('admin/delete-vehicle_make/'.$make->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this vehicle make?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2">No vehicle makes found</div>
                                    @if($canCreate)
                                        <a class="btn btn-primary btn-sm" href="{{ url('admin/add-vehicle-make') }}">Add Vehicle Make</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($makes->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $makes->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
