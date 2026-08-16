@extends('admin.template')

@section('main')
    <div ng-controller='later_booking'>
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h4 class="mb-1">{{ __('messages.admin.manage_manual_booking_page.manage_bookings') }}</h4>
                        <p class="text-muted mb-0">Track scheduled and manual bookings by queue and assignment state.</p>
                    </div>
                    <a href="{{ url(LOGIN_USER_TYPE.'/manual_booking') }}" class="btn btn-primary">New Manual Booking</a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @php
                        $queueLabels = [
                            'all' => 'All',
                            'asap' => 'ASAP',
                            'scheduled' => 'Scheduled',
                            'assigned' => 'Assigned',
                            'dispatch_due' => 'Dispatch Due',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'no_show' => 'No Show',
                        ];
                    @endphp
                    @foreach($queueLabels as $queueKey => $queueLabel)
                        <a href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue='.$queueKey) }}" class="btn {{ $queue === $queueKey ? 'btn-primary' : 'btn-default' }}">
                            {{ $queueLabel }}
                            <span class="badge {{ $queue === $queueKey ? 'bg-light text-primary' : 'bg-secondary text-white' }} ms-1">{{ $queue_counts[$queueKey] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_manual_booking_page.manage_bookings') }}</h4>
                    <div class="text-muted">Queue-aware later bookings list without DataTables.</div>
                </div>
                <div class="text-muted">Total bookings : <strong>{{ $scheduleRides->total() }}</strong></div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/later_booking') }}" class="row g-3 align-items-end mb-4">
                    <input type="hidden" name="queue" value="{{ $queue }}">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ID, rider, driver, pickup or drop">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1">Search</button>
                        <a href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue='.$queue) }}" class="btn btn-light">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Booked By</th>
                                <th>Date</th>
                                <th>Driver</th>
                                @if(LOGIN_USER_TYPE === 'admin')
                                    <th>Company</th>
                                @endif
                                <th>Rider</th>
                                <th>Pickup</th>
                                <th>Dropoff</th>
                                <th>Trip</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scheduleRides as $ride)
                                <tr>
                                    <td>#{{ $ride->id }}</td>
                                    <td>{{ $ride->booking_type }}</td>
                                    <td>{{ $ride->schedule_date }} {{ $ride->schedule_time }}</td>
                                    <td>{{ $ride->driver_name ?: ($ride->trip_status === 'Completed' && $ride->trip_driver_name ? $ride->trip_driver_name : 'Auto Assign') }}</td>
                                    @if(LOGIN_USER_TYPE === 'admin')
                                        <td>{{ $ride->company_name ?: '-' }}</td>
                                    @endif
                                    <td>{{ $ride->rider_name }}</td>
                                    <td>{{ $ride->pickup_location }}</td>
                                    <td>{{ $ride->drop_location }}</td>
                                    <td>
                                        @if($ride->trip_id)
                                            <a href="{{ url(LOGIN_USER_TYPE.'/view_trips/'.$ride->trip_id) }}" class="btn btn-sm btn-outline-primary"><i class="ri-eye-line"></i></a>
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td>{{ $ride->trip_id && $ride->status === 'Completed' && $ride->trip_status ? $ride->trip_status : $ride->status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ LOGIN_USER_TYPE === 'admin' ? 10 : 9 }}" class="text-center py-5 text-muted">No later bookings found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($scheduleRides->hasPages())
                    <div class="d-flex justify-content-end mt-3">{{ $scheduleRides->links() }}</div>
                @endif
            </div>
        </div>

        <div class="modal fade" id="cancel_popup" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"></button>
                        <h4 class="modal-title">{{ __('messages.admin.manage_manual_booking_page.booking_cancel')}}</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open(['method' => 'POST', 'url' => LOGIN_USER_TYPE.'/manual_booking/cancel', 'class' => 'form-horizontal manual_booking_cancel', 'id' => 'manual_booking_cancel']) !!}
                        {!! Form::hidden('manual_booking_id', '', ['ng-model' => 'manual_booking_cancel_id']) !!}
                        <div class="row">
                            <div class="col-md-3">{{ __('messages.admin.manage_manual_booking_page.cancel_reason') }}</div>
                            <div class="col-md-7 col-sm-offset-1">
                                <select class="form-control cancel_reason_id" name="cancel_reason_id">
                                    <option value="">{{ __('messages.blade_static.select_85982229') }}</option>
                                    @foreach ($cancel_reasons as $cancel_reason)
                                        <option value="{{ $cancel_reason->id }}">{{ $cancel_reason->reason }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">{{ __('messages.admin.manage_manual_booking_page.reason')}}</div>
                            <div class="col-md-7 col-sm-offset-1">
                                {!! Form::textarea('cancel_reason', '', ['class' => 'form-control', 'id' => 'input_cancel_reason', 'placeholder' => 'Cancel Reason']) !!}
                            </div>
                        </div>
                        <div class="row" align="center"><input type="submit" name="submit" class="btn btn-primary"></div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="cancel_reason_popup" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"></button>
                        <h4 class="modal-title">{{ __('messages.admin.manage_manual_booking_page.booking_cancel_reason')}}</h4>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('messages.admin.manage_manual_booking_page.cancel_by')}} : <span class="cancel_by">@{{ cancel_by }}</span></p>
                        <p>{{ __('messages.admin.manage_manual_booking_page.cancel_reason') }} : <span class="cancel_reason">@{{ cancel_reason }}</span></p>
                        <p>{{ __('messages.admin.manage_manual_booking_page.reason')}} : <span class="reason">@{{ cancel_reason }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        var REQUEST_URL = "{{ url('/' . LOGIN_USER_TYPE) }}";
    </script>
@endpush
