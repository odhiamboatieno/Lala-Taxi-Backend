@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Driver Scores</h5>
                    <a href="{{ url('admin/driver-score/settings') }}" class="btn btn-primary btn-sm">Scoring Settings</a>
                    <a href="{{ url('admin/driver-score/appeals') }}" class="btn btn-outline-primary btn-sm">Appeals</a>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search driver name or phone">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All score statuses</option>
                                @foreach(['Excellent','Good','Warning','Critical','Disabled Risk'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="marketplace_access_status" class="form-select">
                                <option value="">All marketplace statuses</option>
                                @foreach(['active','limited','review_required','suspended'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['marketplace_access_status'] ?? '') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Marketplace</th>
                                    <th>Completed Trips</th>
                                    <th>Rating</th>
                                    <th>Complaints</th>
                                    <th>Next Recalc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($scores as $score)
                                    <tr>
                                        <td>{{ trim(($score->driver->first_name ?? '').' '.($score->driver->last_name ?? '')) ?: ($score->driver->mobile_number ?? '-') }}</td>
                                        <td>{{ number_format($score->score_percentage, 2) }}%</td>
                                        <td>{{ $score->status }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $score->marketplace_access_status)) }}</td>
                                        <td>{{ $score->total_completed_trips }}</td>
                                        <td>{{ number_format($score->average_rating, 2) }}</td>
                                        <td>{{ number_format($score->complaint_rate, 2) }}%</td>
                                        <td>{{ optional($score->next_calculation_at)->format('d M Y H:i') ?: '-' }}</td>
                                        <td><a href="{{ url('admin/driver-score/'.$score->driver_id) }}" class="btn btn-sm btn-primary">Open</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">No driver scores found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $scores->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
