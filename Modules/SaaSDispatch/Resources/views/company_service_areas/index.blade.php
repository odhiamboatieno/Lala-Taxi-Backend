@extends('admin.template')

@section('title-sub', 'Manage Zones')
@section('pagetitle', 'Manage Zones')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-1">Zone List</h5>
                <div class="text-muted">Total Zones: {{ $zoneCount }}</div>
            </div>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.service-areas.create', $company) : route('saasdispatch.self.service-areas.create') }}" class="btn btn-success">
                Add Zone
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Trip Coverage</th>
                            <th>Extra Fare</th>
                            <th>Extra Fare (%)</th>
                            <th>Status</th>
                            <th>Pickup</th>
                            <th>Dropoff</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceAreas as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->pickup_enabled || $item->dropoff_enabled ? 'Active' : 'Limited' }}</td>
                                <td>{{ $item->extra_fare_enabled ? 'Enabled' : 'Disabled' }}</td>
                                <td>{{ number_format((float) $item->extra_fare_percentage, 2) }}%</td>
                                <td>{{ $item->status }}</td>
                                <td>{{ $item->pickup_enabled ? 'Enabled' : 'Disabled' }}</td>
                                <td>{{ $item->dropoff_enabled ? 'Enabled' : 'Disabled' }}</td>
                                <td class="text-end">
                                    <a href="{{ $isAdmin ? route('saasdispatch.companies.service-areas.edit', [$company, $item]) : route('saasdispatch.self.service-areas.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form method="POST" action="{{ $isAdmin ? route('saasdispatch.companies.service-areas.destroy', [$company, $item]) : route('saasdispatch.self.service-areas.destroy', $item) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this location?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No company zones have been created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
