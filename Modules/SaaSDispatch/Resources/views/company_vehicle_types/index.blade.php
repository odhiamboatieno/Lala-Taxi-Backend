@extends('admin.template')

@section('title-sub', 'Company Vehicle Types')
@section('pagetitle', 'Company Vehicle Types')

@section('main')
<div id="layout-wrapper">
    @include('saasdispatch::partials.company_tabs')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Managed Vehicle Types</h5>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.vehicle-types.create', $company) : route('saasdispatch.self.vehicle-types.create') }}" class="btn btn-success">Add Vehicle Type</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Pool</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicleTypes as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ optional($item->globalVehicleCategory)->name ?: '-' }}</td>
                                <td>{{ $item->is_pool ?: 'No' }}</td>
                                <td>{{ $item->status }}</td>
                                <td class="text-end">
                                    <a href="{{ $isAdmin ? route('saasdispatch.companies.vehicle-types.edit', [$company, $item]) : route('saasdispatch.self.vehicle-types.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form method="POST" action="{{ $isAdmin ? route('saasdispatch.companies.vehicle-types.destroy', [$company, $item]) : route('saasdispatch.self.vehicle-types.destroy', $item) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this vehicle type?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No company vehicle types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
