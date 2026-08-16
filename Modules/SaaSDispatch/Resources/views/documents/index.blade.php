@extends('admin.template')

@section('title-sub', 'Documents Management')
@section('pagetitle', 'Documents Management')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Driver Documents</h4></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Driver</th><th>Documents</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            @forelse($drivers as $driver)
                                @php $count = (int) ($driverDocumentCounts[$driver->id] ?? 0); @endphp
                                <tr>
                                    <td>{{ $driver->first_name }} {{ $driver->last_name }}</td>
                                    <td>{{ $count }}</td>
                                    <td><span class="badge {{ $count > 0 ? 'bg-success' : 'bg-warning' }}">{{ $count > 0 ? 'Configured' : 'Pending' }}</span></td>
                                    <td class="text-end"><a href="{{ url($routePrefix.'/edit_driver/'.$driver->id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">No company drivers found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Vehicle Documents</h4></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Vehicle</th><th>Driver</th><th>Documents</th><th></th></tr></thead>
                            <tbody>
                            @forelse($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->vehicle_name }}<br><small class="text-muted">{{ $vehicle->vehicle_number }}</small></td>
                                    <td>{{ optional($vehicle->driver)->first_name }} {{ optional($vehicle->driver)->last_name }}</td>
                                    <td>{{ (int) ($vehicleDocumentCounts[$vehicle->id] ?? 0) }}</td>
                                    <td class="text-end"><a href="{{ url($routePrefix.'/edit_vehicle/'.$vehicle->id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">No company vehicles found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
