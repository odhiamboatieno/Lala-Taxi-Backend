@extends('admin.template')

@section('title-sub', __('messages.admin.manage_trips.manage_canceled_trips'))
@section('pagetitle', __('messages.admin.manage_trips.cancelled_trips'))

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-1">{{ __('messages.admin.manage_trips.manage_canceled_trips') }}</h5>
                        <p class="text-muted mb-0">{{ __('messages.blade_static.track_cancelled_bookings_reasons_and_who_initiated_each_f9fe3992') }}</p>
                    </div>
                </div>
                <div class="card-body">
                    {!! $dataTable->table(['class' => 'table table-nowrap table-striped table-bordered w-100', 'id' => 'cancel-trips-table']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="{{ url('css/buttons.dataTables.css') }}">
    <script src="{{ url('js/dataTables.buttons.js') }}"></script>
    <script src="{{ url('js/buttons.server-side.js') }}"></script>
    {!! $dataTable->scripts() !!}
@endpush
