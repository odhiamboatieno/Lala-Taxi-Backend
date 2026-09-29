@extends('admin.template')

@section('title', 'Trip / Request Details | FabKin Admin & Dashboards Template')
@section('title-sub', 'Trip Details')
@section('pagetitle', 'Request / Trip Details')

@section('css')
    <!-- If you use Google Maps or Leaflet, include here if not in template -->
    <!-- <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" /> -->
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <!-- Left: Trip Information -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.admin.manage_trips.request_details') }}</h5>

                        <!-- Hidden inputs for map initialization -->
                        <input type="hidden" id="pickup_latitude" value="{{ @$request_details->pickup_latitude }}">
                        <input type="hidden" id="pickup_longitude" value="{{ @$request_details->pickup_longitude }}">
                        <input type="hidden" id="drop_latitude" value="{{ @$request_details->formatted_drop_latitude }}">
                        <input type="hidden" id="drop_longitude" value="{{ @$request_details->formatted_drop_longitude }}">
                        <input type="hidden" id="trip_path" value="{{ $trip_path }}">

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.vehicle_type') }}</label>
                                <p class="mb-0">{{ @$request_details->car_type->car_name ?? '—' }}</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.rider_name') }}</label>
                                <p class="mb-0">
                                    {{ @$request_details->users->first_name . ' ' . @$request_details->users->last_name ?? '—' }}
                                </p>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.driver_name') }}</label>
                                <p class="mb-0">
                                    @if ($request_status == 'No one accepted')
                                        {{ __('messages.admin.manage_trip.request_details.request_status') }}
                                    @else
                                        {{ @$driver_name ?? '—' }}
                                    @endif
                                </p>
                            </div>

                            @if (LOGIN_USER_TYPE != 'company' && isset($company_name))
                                <div class="col-12">
                                    <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.company_name') }}</label>
                                    <p class="mb-0">{{ @$company_name }}</p>
                                </div>
                            @endif

                            <!-- Pickup & Dropoff -->
                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.pickup_address') }}</label>
                                <p class="mb-0">
                                    @if (isset($is_tripped) && in_array($trip_data->status, ['Rating', 'Payment', 'Completed']))
                                        {{ @$trip_data->pickup_location ?? '—' }}
                                    @else
                                        {{ @$request_details->pickup_location ?? '—' }}
                                    @endif
                                </p>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.drop_address') }}</label>
                                <p class="mb-0">
                                    @if (isset($is_tripped) && in_array($trip_data->status, ['Rating', 'Payment', 'Completed']))
                                        {{ @$trip_data->drop_location ?? '—' }}
                                    @else
                                        {{ @$request_details->drop_location ?? '—' }}
                                    @endif
                                </p>
                            </div>

                            <!-- Trip Timestamps -->
                            @isset($is_tripped)
                                @if (in_array($trip_data->status, ['End trip', 'Rating', 'Payment', 'Completed']))
                                    <div class="col-12">
                                        <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.ride_start_time') }}</label>
                                        <p class="mb-0">
                                            {!! $trip_data->begin_trip ? date('l jS F Y h:i:s A', strtotime($trip_data->begin_trip)) : '—' !!}
                                        </p>
                                    </div>

                                    @if ($trip_data->status != 'End trip')
                                        <div class="col-12">
                                            <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.ride_end_time') }}</label>
                                            <p class="mb-0">
                                                {!! $trip_data->end_trip ? date('l jS F Y h:i:s A', strtotime($trip_data->end_trip)) : '—' !!}
                                            </p>
                                        </div>
                                    @endif
                                @endif

                                <!-- Invoice / Fare Breakdown -->
                                @foreach ($invoice_data as $invoice)
                                    <div class="col-12">
                                        <label class="form-label fw-bold">{{ $invoice['key'] }}</label>
                                        <p class="mb-0">{{ $invoice['value'] }}</p>
                                    </div>
                                @endforeach
                            @endisset

                            <!-- Final Status -->
                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('messages.admin.manage_trip.request_details.ride_status') }}</label>
                                @php
                                    $statusBadgeClass = str_contains($request_status, 'Completed')
                                        ? 'bg-success'
                                        : (str_contains($request_status, 'Cancelled') ? 'bg-danger' : 'bg-warning');
                                @endphp
                                <p class="mb-0">
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ $request_status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right: Map & Trip Image -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.admin.manage_trip.request_details.map_view') }}</h5>

                        <div id="map" style="height: 450px; width: 100%; border-radius: 0;"></div>

                        @if (isset($is_tripped) && $trip_data->map_image)
                            <div class="mt-4">
                                <h6 class="mb-3">{{ __('messages.admin.manage_trip.request_details.trip_map_snapshot') }}</h6>
                                <img src="{{ $trip_data->map_image }}"
                                     data-original_src="{{ $trip_data->getOriginal('map_image') }}"
                                     class="img-fluid rounded shadow-sm trip_image" 
                                     id="trip_image"
                                     alt="{{ __('messages.blade_static.trip_route_snapshot_4e792a53') }}">
                            </div>
                        @endif
                    </div>
                </div>
            </div><!-- End right col -->
        </div><!-- End row -->

    </div><!-- End layout-wrapper -->
</main>    
@endsection

@push('scripts')
    <style>
        .trip_image {
            max-width: 100%;
            height: auto;
            border-radius: 0;
            box-shadow: 0 2px 10px var(--color-as-rgba-0-0-0-0-1);
        }

        #map {
            border: 1px solid var(--color-as-e0e0e0);
            border-radius: 0;
        }
    </style>

    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endpush
