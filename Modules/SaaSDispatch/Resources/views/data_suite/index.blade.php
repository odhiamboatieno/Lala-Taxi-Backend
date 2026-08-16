@extends('admin.template')

@section('title-sub', 'Data Suite')
@section('pagetitle', 'Data Suite')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4 mb-4">
        @foreach([
            'Total Trips' => $stats['totalTrips'],
            'Completed Trips' => $stats['completedTrips'],
            'Cancelled Trips' => $stats['cancelledTrips'],
            'Active Drivers' => $stats['activeDrivers'],
            'Riders' => $stats['riders'],
            'Requests' => $stats['requests'],
        ] as $label => $value)
            <div class="col-md-4 col-xl-2">
                <div class="card"><div class="card-body"><div class="text-muted">{{ $label }}</div><h4 class="mb-0 mt-2">{{ $value }}</h4></div></div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h4 class="mb-0">Recent Revenue Trend</h4></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Date</th><th>Trips</th><th>Gross Revenue</th></tr></thead>
                    <tbody>
                        @forelse($dailyRevenue as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->trip_date)->format('d M Y') }}</td>
                                <td>{{ $row->trips_count }}</td>
                                <td>{{ number_format((float) $row->gross_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-5">No analytics data available yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
