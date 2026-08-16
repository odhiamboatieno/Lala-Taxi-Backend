@extends('admin.template')

@section('title-sub', 'Vehicle Model')
@section('pagetitle', 'Vehicle Model')

@section('main')
@php
    $canCreate = Auth::guard('admin')->user()->can('create_vehicle_model');
    $canUpdate = Auth::guard('admin')->user()->can('update_vehicle_model');
    $canDelete = Auth::guard('admin')->user()->can('delete_vehicle_make');
@endphp
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_vehicle_model.title') }}</h4>
                    <div class="text-muted">Keep make-to-model mappings tidy for driver onboarding and vehicle assignment.</div>
                </div>
                @if($canCreate)
                    <a class="btn btn-primary" href="{{ url('admin/add-vehicle_model') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_vehicle_model.add_vehicle_model') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/vehicle_model') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by model, make, or status">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Make</label>
                    <select name="make_id" class="form-select">
                        <option value="0">All Makes</option>
                        @foreach($makeOptions as $makeId => $makeName)
                            <option value="{{ $makeId }}" {{ (int) ($filters['make_id'] ?? 0) === (int) $makeId ? 'selected' : '' }}>{{ $makeName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" {{ ($filters['status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/vehicle_model') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>SL</th>
                            <th>Model</th>
                            <th>Make</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($models as $index => $model)
                            <tr>
                                <td>{{ $models->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $model->model_name }}</div>
                                    <div class="text-muted small">ID #{{ $model->id }}</div>
                                </td>
                                <td>{{ optional($model->vehicle_make)->make_vehicle_name ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $model->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $model->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($canUpdate)
                                            <a href="{{ url('admin/edit-vehicle_model/'.$model->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif
                                        @if($canDelete)
                                            <a href="{{ url('admin/delete_vehicle_model/'.$model->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this vehicle model?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2">No vehicle models found</div>
                                    @if($canCreate)
                                        <a class="btn btn-primary btn-sm" href="{{ url('admin/add-vehicle_model') }}">Add Vehicle Model</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($models->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $models->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
