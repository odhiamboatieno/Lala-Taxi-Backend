@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ __('messages.admin.manage_trips.manage_ride_requests') }}</h4>
                <div class="text-muted">Monitor rider requests, dispatch state, and payment progress.</div>
            </div>
            <div class="text-muted">Total requests : <strong>{{ $requests->total() }}</strong></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/request') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Request ID, rider, payment mode">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url(LOGIN_USER_TYPE.'/request') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Request ID</th>
                            <th>Rider</th>
                            <th>Updated</th>
                            <th>Status</th>
                            @if(LOGIN_USER_TYPE !== 'company')
                                <th>Amount</th>
                            @endif
                            <th>Payment Mode</th>
                            <th>Payment Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $row)
                            <tr>
                                <td>#{{ $row->id }}</td>
                                <td>{{ trim($row->first_name.' '.$row->last_name) }}</td>
                                <td>{{ $row->date_time_label }}</td>
                                <td>{{ $row->request_status }}</td>
                                @if(LOGIN_USER_TYPE !== 'company')
                                    <td>{{ $row->total_amount_label }}</td>
                                @endif
                                <td>{{ $row->payment_mode }}</td>
                                <td>{{ $row->payment_status_label }}</td>
                                <td class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE.'/detail_request/'.$row->id) }}" class="btn btn-sm btn-outline-primary"><i class="ri-eye-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ LOGIN_USER_TYPE !== 'company' ? 8 : 7 }}" class="text-center py-5 text-muted">No ride requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $requests->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
