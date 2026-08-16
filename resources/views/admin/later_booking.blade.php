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
        @include('admin.partials.datatable-card', [
            'title' => __('messages.admin.manage_manual_booking_page.manage_bookings'),
            'table' => $dataTable->table(['class' => 'table table-nowrap table-striped table-bordered w-100', 'id' => 'later-booking-table']),
        ])
        <div class="modal fade" id="cancel_popup" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"></button>
                        <h4 class="modal-title">{{ __('messages.admin.manage_manual_booking_page.booking_cancel')}}</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open([
                            'method' => 'POST',
                            'url' => 'admin/manual_booking/cancel',
                            'class' => 'form-horizontal manual_booking_cancel',
                            'id' => 'manual_booking_cancel',
                        ]) !!}
                        {!! Form::hidden('manual_booking_id', '', ['ng-model' => 'manual_booking_cancel_id']) !!}
                        <div class="row">
                            <div class="col-md-3">
                                {{ __('messages.admin.manage_manual_booking_page.cancel_reason') }}
                            </div>
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
                            <div class="col-md-3">
                              {{ __('messages.admin.manage_manual_booking_page.reason')}}
                            </div>
                            <div class="col-md-7 col-sm-offset-1">
                                {!! Form::textarea('cancel_reason', '', [
                                    'class' => 'form-control',
                                    'id' => 'input_cancel_reason',
                                    'placeholder' => 'Cancel Reason',
                                ]) !!}
                            </div>
                        </div>
                        <div class="row" align="center">
                            <input type="submit" name="submit" class="btn btn-primary">
                        </div>
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
                        <p> {{ __('messages.admin.manage_manual_booking_page.cancel_reason') }} : <span
                                class="cancel_reason">@{{ cancel_reason }}</span></p>
                        <p>{{ __('messages.admin.manage_manual_booking_page.reason')}} : <span class="reason">@{{ cancel_reason }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ url('js/dataTables.buttons.js') }}"></script>
    <script src="{{ url('js/buttons.server-side.js') }}"></script>
    <script type="text/javascript">
        var REQUEST_URL = "{{ url('/' . LOGIN_USER_TYPE) }}";
    </script>
    {!! $dataTable->scripts() !!}
@endpush
<style type="text/css">
    .fa-eye {
        font-size: 20px !important;
    }
</style>
