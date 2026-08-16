@extends('admin.template')

@section('title-sub', 'Parcel Pricing')
@section('pagetitle', 'Parcel Pricing')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Parcel Pricing</h4>
                <div class="text-muted">Review parcel fare rules by category, vehicle type, and zone.</div>
            </div>
            <a class="btn btn-primary" href="{{ url('admin/add_parcel_pricing') }}">Add Pricing Rule</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/parcel_pricing') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search category, vehicle type, or zone">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/parcel_pricing') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Vehicle Type</th>
                            <th>Zone</th>
                            <th>Base Fare</th>
                            <th>Per KM</th>
                            <th>Min Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fares as $fare)
                            <tr>
                                <td>{{ optional($fare->parcelCategory)->title ?: '-' }}</td>
                                <td>{{ optional($fare->carType)->car_name ?: '-' }}</td>
                                <td>{{ optional($fare->location)->name ?: 'Default' }}</td>
                                <td>{{ currency_symbol().number_format((float) $fare->base_fare, 2) }}</td>
                                <td>{{ currency_symbol().number_format((float) $fare->per_km_rate, 2) }}</td>
                                <td>{{ currency_symbol().number_format((float) $fare->min_fare, 2) }}</td>
                                <td><span class="badge {{ $fare->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $fare->is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No parcel pricing rules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($fares->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $fares->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
