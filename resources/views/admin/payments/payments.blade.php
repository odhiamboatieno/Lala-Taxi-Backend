@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ __('messages.admin.manage_trips.manage_payments') }}</h4>
                <div class="text-muted">Review trip fare, payout, commission, and payment status without DataTables.</div>
            </div>
            <div class="text-muted">Total payments : <strong>{{ $payments->total() }}</strong></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/payments') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Trip ID, driver, rider, company, status">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url(LOGIN_USER_TYPE.'/payments') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Trip Date</th>
                            @if(LOGIN_USER_TYPE !== 'company')
                                <th>Company</th>
                            @endif
                            <th>Driver</th>
                            <th>Rider</th>
                            <th>Base</th>
                            <th>Distance</th>
                            <th>Time</th>
                            <th>Access</th>
                            <th>Total</th>
                            <th>Commission</th>
                            <th>Driver Payout</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>#{{ $payment->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->trip_date)->format('d M Y H:i') }}</td>
                                @if(LOGIN_USER_TYPE !== 'company')
                                    <td>{{ $payment->company_name ?: 'Platform' }}</td>
                                @endif
                                <td>{{ trim($payment->driver_name) ?: 'Unassigned' }}</td>
                                <td>{{ trim($payment->rider_name) ?: 'Unknown' }}</td>
                                <td>{{ $payment->base_fare_amount_display }}</td>
                                <td>{{ $payment->distance_fare_amount_display }}</td>
                                <td>{{ $payment->time_fare_amount_display }}</td>
                                <td>{{ $payment->access_fee_amount_display }}</td>
                                <td>{{ $payment->total_fare_amount_display }}</td>
                                <td>{{ $payment->commission_amount_display }}</td>
                                <td>{{ $payment->driver_payout_amount_display }}</td>
                                <td>{{ $payment->payment_mode ?: 'N/A' }}</td>
                                <td><span class="badge {{ ($payment->payment_status ?: 'Pending') === 'Completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $payment->payment_status ?: 'Pending' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ LOGIN_USER_TYPE !== 'company' ? 14 : 13 }}" class="text-center py-5 text-muted">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
