@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row g-3 mb-4">
        @foreach($typeCards as $typeCard)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="fs-15 fw-semibold text-primary mb-2">{{ $typeCard->car_name }}</div>
                            <div class="fs-2 fw-bold mb-1">{{ (int) $typeCard->vehicles_count }}</div>
                            <div class="text-muted small">Vehicles using this type</div>
                        </div>
                        <div class="text-end">
                            <img src="{{ $typeCard->vehicle_image }}" alt="{{ $typeCard->car_name }}" style="width:48px;height:48px;object-fit:contain;">
                            <div class="mt-3">
                                <span class="badge {{ $typeCard->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $typeCard->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">{{ __('messages.admin.manage_vehicle_type.title') }}</h5>
                        <div class="text-muted small">
                            Total types: {{ $summary['total_types'] }} |
                            Active: {{ $summary['active_types'] }} |
                            Inactive: {{ $summary['inactive_types'] }} |
                            Vehicles: {{ $summary['total_vehicles'] }}
                        </div>
                    </div>
                    @if(Auth::guard('admin')->user()->can('create_vehicle_type'))
                        <div class="ms-auto">
                            <a class="btn btn-success" href="{{ url('admin/add_vehicle_type') }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_vehicle_type.add_vehicle_type') }}</a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ url('admin/vehicle_type') }}" class="row g-3 align-items-end mb-4">
                        <div class="col-md-5">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Search by vehicle type name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status_scope" class="form-select">
                                <option value="all" {{ $filters['status_scope'] === 'all' ? 'selected' : '' }}>All</option>
                                <option value="active" {{ $filters['status_scope'] === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $filters['status_scope'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ url('admin/vehicle_type') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vehicle Type</th>
                                    <th>Description</th>
                                    <th>Total Vehicles</th>
                                    <th>Pooling</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicleTypes as $index => $vehicleType)
                                    <tr>
                                        <td>{{ $vehicleTypes->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $vehicleType->vehicle_image }}" alt="{{ $vehicleType->car_name }}" style="width:44px;height:44px;object-fit:contain;">
                                                <div>
                                                    <div class="fw-semibold">{{ $vehicleType->car_name }}</div>
                                                    <div class="text-muted small">ID #{{ $vehicleType->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $vehicleType->description ?: 'Not provided' }}</td>
                                        <td>{{ (int) $vehicleType->vehicles_count }}</td>
                                        <td>{{ (int) $vehicleType->is_pool === 1 ? 'Pool Enabled' : 'Standard' }}</td>
                                        <td>
                                            <span class="badge {{ $vehicleType->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                {{ $vehicleType->status }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ url('admin/edit_vehicle_type/'.$vehicleType->id) }}" class="btn btn-outline-primary">Edit</a>
                                                <a href="{{ url('admin/delete_vehicle_type/'.$vehicleType->id) }}" class="btn btn-outline-danger" onclick="return confirm('Delete this vehicle type?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">No vehicle types found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $vehicleTypes->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
