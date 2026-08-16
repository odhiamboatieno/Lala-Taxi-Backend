@extends('admin.template')

@section('main')
@php
    $rider = $result->users;
    $driver = $result->driver;
    $company = optional($driver)->company;
    $tripDate = $result->travel_date_time
        ?? ($result->begin_trip && $result->begin_trip !== '0000-00-00 00:00:00' ? \Carbon\Carbon::parse($result->begin_trip) : null)
        ?? ($result->created_at instanceof \Carbon\Carbon ? $result->created_at : ($result->created_at ? \Carbon\Carbon::parse($result->created_at) : null));
    $rideTypeLabel = $result->ride_type ?: ($result->pool_id ? 'Pool Ride' : 'Trip');
    $typeLabel = $result->type ? ucfirst($result->type) : ($result->payment_mode ?: 'Standard');
    $priceLabel = optional($result->currency)->symbol
        ? optional($result->currency)->symbol.(is_numeric($result->total_fare ?? null) ? number_format((float) $result->total_fare, 2) : ($result->price ?? 0))
        : ($result->price ?? $result->total_fare ?? 'N/A');
    $returnDate = $result->return_date ? (\Illuminate\Support\Carbon::parse($result->return_date)) : null;
@endphp
<div class="content-wrapper">
    <section class="content-header">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('messages.blade_static.manage_ride_details_449a718e') }}</h1>
        <ol class="breadcrumb flex items-center space-x-2 text-sm text-gray-600">
            <li>
                <a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}" class="hover:text-blue-600">
                    <i class="fa fa-dashboard"></i> {{ __('messages.admin.home') }}
                </a>
            </li>
            <li>
                <a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}" class="hover:text-blue-600">{{ __('messages.blade_static.rides_ae849605') }}</a>
            </li>
            <li class="active">{{ __('messages.blade_static.details_dc3decbb') }}</li>
        </ol>
    </section>
    <section class="content bg-white">
        <div class="max-w-5xl mx-auto my-8">
            <div class="bg-white shadow-2xl rounded-xl p-8 transition-all duration-300 hover:shadow-3xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">{{ __('messages.blade_static.ride_details_8ee65360') }}</h3>
                {!! Form::open(['url' => '', 'class' => 'space-y-8']) !!}

                <!-- Ride Information Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.ride_information_08297dd4') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.vehicle_name_92dc53a1') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ optional($result->car_type)->car_name ?: ($result->vehicle_name ?? 'N/A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.user_name_a951840b') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ trim((optional($rider)->first_name ?? '').' '.(optional($rider)->last_name ?? '')) ?: ($result->rider_name ?? 'N/A') }}</div>
                            </div>
                        </div>
                        @if(LOGIN_USER_TYPE != 'company')
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.company_name_8599f5cc') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ optional($company)->name ?: 'Platform' }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.trip_date_d4c6eae4') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ optional($tripDate)->format('d M Y, H:i') ?: 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.ride_type_71606829') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $rideTypeLabel }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.type_3deb7456') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $typeLabel }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seats Information Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.seats_information_4c08d78c') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.total_seats_dbad010e') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->seats }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.booked_seats_d8008505') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->booked_seats }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.available_seats_9a2fba65') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->available_seats }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location and Price Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.location_and_price_8c2f0c0f') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.pickup_location_8dea766c') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->pickup_location ?: ($result->pick_location ?? 'N/A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.drop_location_81051dbb') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->drop_location }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.price_3e8248e3') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $priceLabel }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.status_bae7d5be') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">
                                    @php
                                        $resultStatusClass = $result->status == 'completed'
                                            ? 'bg-green-100 text-green-800'
                                            : ($result->status == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-sm {{ $resultStatusClass }}">
                                        {{ ucfirst($result->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if($result->additional_info)
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.additional_info_bdcc9d9b') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->additional_info }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Round Trip Details Section -->
                @if($result->is_round)
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.round_trip_details_cb0c7c9b') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.return_pickup_location_ef5f9698') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->return_pick_location ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.return_date_2b1a1ab6') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ optional($returnDate)->format('d M Y, H:i') ?: 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.return_time_f782634d') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $result->return_time ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Booked Seats Details -->
                @if($result->trips->count() > 0)
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.booked_seats_d8008505') }}</h4>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-900">{{ __('messages.blade_static.user_name_a951840b') }}</th>
                                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-900">{{ __('messages.blade_static.status_bae7d5be') }}</th>
                                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-900">{{ __('messages.blade_static.payment_b41a92be') }}</th>
                                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-900">{{ __('messages.blade_static.refund_fee_9f57bcb4') }}</th>
                                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-900">{{ __('messages.blade_static.payout_9bb39694') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($result->trips as $trip)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-gray-900">{{ trim((optional($trip->users)->first_name ?? '').' '.(optional($trip->users)->last_name ?? '')) ?: 'N/A' }}</td>
                                    <td class="py-4 px-6">
                                        @php
                                            $tripStatusClass = $trip->status == 'completed'
                                                ? 'bg-green-100 text-green-800'
                                                : ($trip->status == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                        @endphp
                                        <span class="inline-block px-3 py-1 rounded-full text-sm {{ $tripStatusClass }}">
                                            {{ ucfirst($trip->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-900">{{ $trip->payment_status ?? 'N/A' }}</td>
                                    <td class="py-4 px-6 text-gray-900">
                                        {{ $trip->refund_amount ?? 0 }}
                                        <div class="text-xs text-gray-500">Fee: {{ $trip->cancel_fee_amount ?? $trip->cancellation_fee_amount ?? 0 }}</div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-900">{{ $trip->driver_payout ?? 0 }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Invoice Data -->
                @if($invoice_data)
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.invoice_details_397a6f30') }}</h4>
                    <div class="row">
                        @foreach($invoice_data as $invoice)
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ $invoice['key'] }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ $invoice['value'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Cancellation Details -->
                @if($result->status == 'cancelled')
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.cancellation_details_5f69f1dc') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.cancelled_reason_4c69afa1') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ @$result->cancel->cancel_reason->reason ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.cancelled_message_f181b185') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ @$result->cancel->cancel_comments ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.cancelled_by_adfc64da') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ @$result->cancel->cancelled_by ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.cancelled_date_1a1d87a9') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ @$result->cancel->created_at ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payout and Transaction Details -->
                @if(LOGIN_USER_TYPE != 'company')
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.payout_and_transaction_details_cf410917') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.transaction_id_06d8ffe6') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ @$result->paykey ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @if((int) optional($company)->id === 1 && blank(optional($rider)->default_payout_credentials))
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.payout_status_acd05ee4') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ __('messages.blade_static.yet_user_doesn_t_enter_their_payout_details_be7d126b') }}</div>
                            </div>
                        </div>
                        @elseif($result->status == 'completed' && $result->payout_status == 'Paid')
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.payout_status_acd05ee4') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ __('messages.blade_static.payout_successfully_sent_17469311') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if((int) ($result->company_id ?? 0) !== 1)
                @if(blank(optional($company)->default_payout_credentials))
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">{{ __('messages.blade_static.company_payout_details_81957b56') }}</h4>
                    <div class="row">
                        <div class="col-md-4 form-group m-0 p-1">
                            <div class="detail-card">
                                <label class="block text-sm font-medium text-gray-700">{{ __('messages.blade_static.payout_status_acd05ee4') }}</label>
                                <div class="mt-2 text-gray-900 font-medium">{{ __('messages.blade_static.yet_company_doesn_t_enter_their_payout_details_dcbebfd5') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif

                <div class="flex justify-center mt-8">
                    <a class="inline-block bg-gradient-to-r from-gray-200 to-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium hover:from-gray-300 hover:to-gray-400 transition-all duration-300 shadow-md" href="{{ $back_url }}">{{ __('messages.blade_static.back_b52b36b7') }}</a>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </section>
</div>

<style>
    .detail-card {
        background: linear-gradient(145deg, var(--color-as-f9fafb), var(--color-white));
        padding: 1rem;
        border-radius: 0.5rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-1);
    }

    .shadow-3xl {
        box-shadow: 0 20px 40px var(--color-as-rgba-0-0-0-0-15);
    }

    table {
        border-collapse: separate;
        border-spacing: 0;
    }

    table th, table td {
        transition: background-color 0.2s ease;
    }

    .content-wrapper {
        background-color: var(--color-as-f3f4f6);
        min-height: 100vh;
    }

    .content-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin-top: 0.5rem;
    }

    .content-header .breadcrumb a {
        transition: color 0.2s ease;
    }

    .grid > .detail-card {
        min-height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>
@endsection
