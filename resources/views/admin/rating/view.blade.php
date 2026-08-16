@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ __('messages.admin.manage_trips.ratings') }}</h4>
                <div class="text-muted">Review rider and driver feedback with company-aware filtering.</div>
            </div>
            <div class="text-muted">Total ratings : <strong>{{ $ratings->total() }}</strong></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/rating') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Trip ID, driver, rider, company, car">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url(LOGIN_USER_TYPE.'/rating') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Trip</th>
                            <th>Date</th>
                            <th>Driver</th>
                            <th>Rider</th>
                            @if(LOGIN_USER_TYPE === 'admin')
                                <th>Company</th>
                            @endif
                            <th>Vehicle</th>
                            <th>Driver Rating</th>
                            <th>Rider Rating</th>
                            <th>Rider Comments</th>
                            <th>Driver Comments</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ratings as $rating)
                            <tr>
                                <td>#{{ $rating->trip_id }}</td>
                                <td>{{ \Carbon\Carbon::parse($rating->date)->format('d M Y H:i') }}</td>
                                <td>{{ trim($rating->driver_name) ?: 'Unassigned' }}</td>
                                <td>{{ trim($rating->rider_name) }}</td>
                                @if(LOGIN_USER_TYPE === 'admin')
                                    <td>{{ $rating->driver_company_name ?: 'Platform' }}</td>
                                @endif
                                <td>{{ $rating->car_name }}</td>
                                <td>{{ $rating->driver_rating ?: '0' }}</td>
                                <td>{{ $rating->rider_rating ?: '0' }}</td>
                                <td>{{ $rating->rider_comments ?: '-' }}</td>
                                <td>{{ $rating->driver_comments ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE.'/delete_rating/'.$rating->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this rating?')"><i class="ri-delete-bin-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ LOGIN_USER_TYPE === 'admin' ? 11 : 10 }}" class="text-center py-5 text-muted">No ratings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ratings->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $ratings->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
