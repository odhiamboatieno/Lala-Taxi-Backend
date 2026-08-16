@extends('admin.template')

@section('title-sub', 'Parcel Trips')
@section('pagetitle', 'Parcel Trips')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Parcel Trips</h4>
                <div class="text-muted">Track parcel deliveries, recipients, categories, and trip state.</div>
            </div>
            <a class="btn btn-light" href="{{ url('admin/export_parcel_trips') }}">Export CSV</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/parcel_trips') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Trip ID, rider, driver, or recipient">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/parcel_trips') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Trip</th>
                            <th>Rider</th>
                            <th>Driver</th>
                            <th>Category</th>
                            <th>Recipient</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $trip)
                            <tr>
                                <td>#{{ $trip->id }}</td>
                                <td>{{ optional($trip->users)->first_name }} {{ optional($trip->users)->last_name }}</td>
                                <td>{{ optional($trip->driver)->first_name }} {{ optional($trip->driver)->last_name }}</td>
                                <td>{{ optional($trip->parcelCategory)->title ?: '-' }}</td>
                                <td>{{ optional($trip->parcelRequest)->recipient_name ?: '-' }}</td>
                                <td><span class="badge bg-light text-dark">{{ $trip->status }}</span></td>
                                <td class="text-end d-flex justify-content-end gap-2">
                                    <a href="{{ url('admin/view_parcel_trip/'.$trip->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ url('admin/cancel_parcel_trip/'.$trip->id) }}" class="btn btn-sm btn-outline-danger">Cancel</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No parcel trips found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($trips->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $trips->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
