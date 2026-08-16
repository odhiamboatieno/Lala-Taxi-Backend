@extends('admin.template')

@section('title-sub', __('messages.admin.manage_drivers_page.edit_driver'))
@section('pagetitle', __('messages.admin.manage_drivers_page.edit_driver'))

@section('main')
<div id="layout-wrapper"
     ng-controller="driver_management"
     ng-init='login_user_type="{{ LOGIN_USER_TYPE }}"; driver_doc=""; errors=@json($errors->getMessages());'>

    {!! Form::open(['url' => LOGIN_USER_TYPE.'/edit_driver/'.$result->id, 'files' => true, 'class' => 'needs-validation']) !!}
    {{ Form::hidden('user_id', $result->id, ['id' => 'user_id']) }}
    {!! Form::hidden('user_type', __('messages.admin.user_type_driver')) !!}

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">{{ __('messages.admin.manage_drivers_page.driver') }} #{{ $result->uuid ?? $result->id }}</h3>
                    <div class="text-muted">{{ __('messages.admin.manage_drivers_page.control_center_desc') }}</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if((LOGIN_USER_TYPE == 'admin' && Auth::guard('admin')->user()->can('delete_driver')) || LOGIN_USER_TYPE == 'company')
                            <a href="{{ url(LOGIN_USER_TYPE.'/delete_driver/'.$result->id) }}" class="btn btn-outline-danger" onclick="return confirm('{{ __('messages.admin.manage_drivers_page.delete_driver_confirm') }}')">
                                <i class="ri-delete-bin-line me-1"></i>{{ __('messages.admin.delete') }}
                            </a>
                    @endif
                    <button type="submit" name="submit" value="toggle_status" class="btn btn-outline-secondary" formnovalidate>
                        <i class="ri-toggle-line me-1"></i>{{ __('messages.admin.manage_drivers_page.status_label') }} {{ $result->status === 'Active' ? __('messages.admin.manage_drivers_page.on') : __('messages.admin.manage_drivers_page.off') }}
                    </button>
                    <button type="submit" name="submit" value="suspend_driver" class="btn btn-danger" formnovalidate onclick="return confirm('{{ __('messages.admin.manage_drivers_page.suspend_driver_confirm') }}')">
                        <i class="ri-forbid-2-line me-1"></i>{{ __('messages.admin.manage_drivers_page.suspend_driver') }}
                    </button>
                    <a href="{{ url(LOGIN_USER_TYPE.'/driver') }}" class="btn btn-light">{{ __('messages.admin.back') }}</a>
                    <button type="submit" name="submit" value="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>{{ __('messages.admin.submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-4">{{ __('messages.admin.manage_drivers_page.driver_info') }}</h4>
                    <div class="d-flex gap-4 flex-wrap align-items-start">
                        <img src="{{ optional($result->profile_picture)->src ?: url('images/user.jpeg') }}" alt="{{ $result->first_name }}" class="rounded-3 border" style="width:132px;height:132px;object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <h3 class="mb-0">{{ $result->first_name }} {{ $result->last_name }}</h3>
                                <span class="badge bg-info-subtle text-info">{{ optional(optional($driver_level_assignment)->level)->name ?: __('messages.admin.manage_drivers_page.unassigned') }}</span>
                                <span class="badge {{ $result->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $result->status }}</span>
                            </div>
                            <div class="mb-2"><strong>{{ __('messages.admin.manage_drivers_page.phone_label') }}</strong> +{{ $result->country_code }}{{ $result->mobile_number }}</div>
                            <div class="mb-2"><strong>{{ __('messages.admin.manage_drivers_page.email_label') }}</strong> {{ protectedString($result->email) }}</div>
                            <div class="mb-2"><strong>{{ __('messages.admin.manage_drivers_page.service_label') }}</strong> {{ optional(optional($result->vehicle)->car_type)->car_name ?: __('messages.admin.manage_drivers_page.no_default_vehicle') }}</div>
                            <div><strong>{{ __('messages.admin.manage_drivers_page.company_label') }}</strong> {{ optional($result->company)->name ?: __('messages.admin.manage_drivers_page.admin_global') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.rate_info') }}</h4>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ __('messages.admin.manage_drivers_page.avg_active_rate_per_day') }}</span>
                        <strong>{{ number_format((float) ($driver_rate_info['average_earning_per_day'] ?? 0), 2) }}</strong>
                    </div>
                    <div class="progress mb-4" style="height:10px;">
                        <div class="progress-bar" style="width: {{ min(100, (float) ($driver_rate_info['success_rate'] ?? 0)) }}%"></div>
                    </div>
                    <div class="row g-3">
                        @foreach([
                            ['label' => __('messages.admin.manage_drivers_page.avg_earning_value'), 'value' => number_format((float) ($driver_rate_info['average_earning_per_day'] ?? 0), 2)],
                            ['label' => __('messages.admin.manage_drivers_page.positive_review_rate'), 'value' => number_format((float) ($driver_rate_info['positive_review_rate'] ?? 0), 2).' %'],
                            ['label' => __('messages.admin.manage_drivers_page.success_rate'), 'value' => number_format((float) ($driver_rate_info['success_rate'] ?? 0), 2).' %'],
                            ['label' => __('messages.admin.manage_drivers_page.cancellation_rate'), 'value' => number_format((float) ($driver_rate_info['cancellation_rate'] ?? 0), 2).' %'],
                            ['label' => __('messages.admin.manage_drivers_page.today_idle_hour_rate'), 'value' => number_format((float) ($driver_rate_info['today_idle_rate'] ?? 0), 2).' %'],
                        ] as $metric)
                            <div class="col-md-6 col-xl">
                                <div class="border rounded-3 p-3 text-center h-100">
                                    <div class="fw-semibold fs-5 mb-1">{{ $metric['value'] }}</div>
                                    <div class="text-muted small">{{ $metric['label'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-4">{{ __('messages.admin.manage_drivers_page.wallet_info') }}</h4>
            <div class="row g-4">
                @foreach([
                    ['title' => __('messages.admin.manage_drivers_page.collectable_cash'), 'value' => $driver_wallet_stats['collectable_cash'] ?? 0],
                    ['title' => __('messages.admin.manage_drivers_page.pending_withdraw'), 'value' => $driver_wallet_stats['pending_withdraw'] ?? 0],
                    ['title' => __('messages.admin.manage_drivers_page.already_withdrawn'), 'value' => $driver_wallet_stats['already_withdrawn'] ?? 0],
                    ['title' => __('messages.admin.manage_drivers_page.withdrawable_amount'), 'value' => $driver_wallet_stats['withdrawable_amount'] ?? 0],
                    ['title' => __('messages.admin.manage_drivers_page.total_earning'), 'value' => $driver_wallet_stats['total_earning'] ?? 0],
                ] as $card)
                    <div class="col-xl col-md-6">
                        <div class="card h-100 mb-0">
                            <div class="card-body">
                                <div class="fs-2 fw-semibold mb-2">{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) $card['value'], 2) }}</div>
                                <div class="text-muted">{{ $card['title'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#driver-overview" type="button">{{ __('messages.admin.manage_drivers_page.overview_tab') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#driver-edit-profile" type="button">{{ __('messages.admin.manage_drivers_page.edit_profile_tab') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#driver-documents" type="button">{{ __('messages.admin.manage_drivers_page.documents_banking_tab') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#driver-trips" type="button">{{ __('messages.admin.manage_drivers_page.trips_transactions_tab') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#driver-reviews" type="button">{{ __('messages.admin.manage_drivers_page.review_tab') }}</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="driver-overview">
                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.driver_details') }}</h4>
                                    <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#driver-trip-stats" type="button">{{ __('messages.admin.manage_drivers_page.trip_tab') }}</button></li>
                                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#driver-duty-review" type="button">{{ __('messages.admin.manage_drivers_page.duty_review_tab') }}</button></li>
                                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#driver-wallet-points" type="button">{{ __('messages.admin.manage_drivers_page.wallet_tab') }}</button></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="driver-trip-stats">
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.total_completed_trip') }}</span><strong>{{ $driver_trip_stats['completed'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.total_cancel_trip') }}</span><strong>{{ $driver_trip_stats['cancelled'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.lowest_price_trip') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($driver_trip_stats['lowest_fare'] ?? 0), 2) }}</strong></div>
                                            <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_drivers_page.highest_price_trip') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($driver_trip_stats['highest_fare'] ?? 0), 2) }}</strong></div>
                                        </div>
                                        <div class="tab-pane fade" id="driver-duty-review">
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.total_review_given') }}</span><strong>{{ $driver_trip_stats['reviews_given'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_drivers_page.total_active_hour') }}</span><strong>{{ $driver_trip_stats['active_hours'] ?? '0:0h' }}</strong></div>
                                        </div>
                                        <div class="tab-pane fade" id="driver-wallet-points">
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>{{ __('messages.admin.manage_drivers_page.total_level_point') }} ( {{ optional(optional($driver_level_assignment)->level)->name ?: __('messages.admin.manage_drivers_page.unassigned') }} - {{ number_format((float) ($driver_trip_stats['level_points'] ?? 0), 0) }}/{{ number_format((float) ($driver_trip_stats['level_target_points'] ?? 0), 0) }} )</span>
                                                <strong>{{ number_format((float) ($driver_trip_stats['level_points'] ?? 0), 0) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_drivers_page.wallet_money') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($driver_trip_stats['wallet_money'] ?? 0), 2) }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.vehicle_snapshot') }}</h4>
                                    @forelse($driver_vehicle_cards as $vehicle)
                                        <div class="border rounded-3 p-3 mb-3">
                                            <div class="fw-semibold">{{ optional($vehicle->car_type)->car_name ?: ($vehicle->vehicle_name ?: __('messages.admin.manage_drivers_page.vehicle_fallback')) }}</div>
                                            <div class="text-muted small">{{ __('messages.admin.manage_drivers_page.number_label') }} {{ $vehicle->vehicle_number ?: __('messages.admin.manage_drivers_page.na') }}</div>
                                            <div class="text-muted small">{{ __('messages.admin.manage_drivers_page.model_label') }} {{ optional($vehicle->model)->model_name ?: optional($vehicle->model)->name ?: ($vehicle->vehicle_model ?: __('messages.admin.manage_drivers_page.na')) }}</div>
                                            <div class="text-muted small">{{ __('messages.admin.manage_drivers_page.status_label_short') }} {{ $vehicle->status ?: __('messages.blade_static.inactive_icon_bc96e971') ?? 'Inactive' }}</div>
                                        </div>
                                    @empty
                                        <div class="text-muted">{{ __('messages.admin.no_vehicle_linked') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @foreach([
                            __('messages.admin.manage_drivers_page.identity_documents') => $driver_documents_grouped['identity'] ?? collect(),
                            __('messages.admin.manage_drivers_page.other_documents') => $driver_documents_grouped['other'] ?? collect(),
                            __('messages.admin.manage_drivers_page.additional_documents') => $driver_documents_grouped['additional'] ?? collect(),
                        ] as $title => $docs)
                            <div class="col-xl-6">
                                <div class="card h-100 mb-0">
                                    <div class="card-body">
                                        <h4 class="mb-3">{{ $title }}</h4>
                                        @forelse($docs as $doc)
                                            <a href="{{ $doc['document'] ?: '#' }}" target="_blank" class="d-flex align-items-center justify-content-between border rounded-3 p-3 mb-3 text-decoration-none {{ empty($doc['document']) ? 'disabled pe-none text-muted' : '' }}">
                                                <div>
                                                    <div class="fw-semibold">{{ $doc['document_name'] ?? __('messages.admin.manage_drivers_page.document_fallback') }}</div>
                                                    <div class="small text-muted">
                                                        {{ ['0' => __('messages.blade_static.pending_96f608c1'), '1' => __('messages.blade_static.approved_41b81eb8'), '2' => __('messages.blade_static.rejected_27eeb7a2')][(string) ($doc['status'] ?? 0)] ?? __('messages.admin.manage_drivers_page.uploaded') }}
                                                    </div>
                                                </div>
                                                <i class="ri-download-line fs-5"></i>
                                            </a>
                                        @empty
                                            <div class="text-muted">{{ __('messages.admin.no_documents_in_section') }}</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.additional_info') }}</h4>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.form.address_line1') }}</span><strong>{{ $address->address_line1 ?? __('messages.admin.not_provided') }}</strong></div>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.form.address_line2') }}</span><strong>{{ $address->address_line2 ?? __('messages.admin.not_provided') }}</strong></div>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.form.city') }}</span><strong>{{ $address->city ?? __('messages.admin.not_provided') }}</strong></div>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_drivers_page.form.state') }}</span><strong>{{ $address->state ?? __('messages.admin.not_provided') }}</strong></div>
                                    <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_drivers_page.form.postal_code') }}</span><strong>{{ $address->postal_code ?? __('messages.admin.not_provided') }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="driver-edit-profile">
                    <div class="row g-4">
                        <div class="col-xl-4">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.profile_image') }}</h4>
                                    <img src="{{ optional($result->profile_picture)->src ?: url('images/user.jpeg') }}" alt="{{ $result->first_name }}" class="rounded-3 border mb-3 w-100" style="max-width:260px;object-fit:cover;">
                                    <input type="file" name="profile_image" class="form-control">
                                    <div class="text-danger mt-1">{{ $errors->first('profile_image') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.first_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('first_name', old('first_name', $result->first_name), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('first_name') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.last_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('last_name', old('last_name', $result->last_name), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('last_name') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.email') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('email', old('email', $result->email), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('email') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.password') }}</label>
                                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => __('messages.admin.manage_drivers_page.leave_blank_password')]) !!}
                                    <div class="text-danger">{{ $errors->first('password') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.country_code') }} <span class="text-danger">*</span></label>
                                    {!! Form::select('country_id', $country_code_option, old('country_id', $result->country_id), ['class' => 'form-select', 'id' => 'input_country_code']) !!}
                                    <div class="text-danger">{{ $errors->first('country_id') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.mobile_number') }}</label>
                                    {!! Form::text('mobile_number', old('mobile_number', $result->env_mobile_number), ['class' => 'form-control', 'id' => 'mobile_number']) !!}
                                    <div class="text-danger">{{ $errors->first('mobile_number') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">{{ __('messages.admin.manage_drivers_page.form.gender') }} <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4 pt-2">
                                        <div class="form-check">
                                            {{ Form::radio('gender', '1', old('gender', $result->getOriginal('gender')) == '1', ['class' => 'form-check-input', 'id' => 'driver_gender_male']) }}
                                            <label class="form-check-label" for="driver_gender_male">{{ __('messages.profile.male') }}</label>
                                        </div>
                                        <div class="form-check">
                                            {{ Form::radio('gender', '2', old('gender', $result->getOriginal('gender')) == '2', ['class' => 'form-check-input', 'id' => 'driver_gender_female']) }}
                                            <label class="form-check-label" for="driver_gender_female">{{ __('messages.profile.female') }}</label>
                                        </div>
                                    </div>
                                    <div class="text-danger">{{ $errors->first('gender') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.status') }} <span class="text-danger">*</span></label>
                                    {!! Form::select('status', ['Active' => __('messages.blade_static.active_ea35693d') ?? 'Active', 'Inactive' => __('messages.blade_static.inactive_icon_bc96e971') ?? 'Inactive', 'Pending' => __('messages.blade_static.pending_96f608c1'), 'Car_details' => __('messages.admin.manage_drivers_page.car_details_option'), 'Document_details' => __('messages.admin.manage_drivers_page.document_details_option')], old('status', $result->status), ['class' => 'form-select', 'id' => 'input_status']) !!}
                                    <div class="text-danger">{{ $errors->first('status') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.driver_level') }}</label>
                                    {!! Form::select('driver_level_id', $driver_level_options ?? [], optional($driver_level_assignment)->user_level_id, ['class' => 'form-select', 'placeholder' => __('messages.admin.manage_drivers_page.auto_assign_placeholder')]) !!}
                                </div>
                                @if(LOGIN_USER_TYPE != 'company')
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.company_name') }} <span class="text-danger">*</span></label>
                                        {!! Form::select('company_name', $company, old('company_name', $result->company_id), ['class' => 'form-select', 'id' => 'input_company_name']) !!}
                                        <div class="text-danger">{{ $errors->first('company_name') }}</div>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.address_line1') }}</label>
                                    {!! Form::text('address_line1', old('address_line1', $address->address_line1 ?? ''), ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.address_line2') }}</label>
                                    {!! Form::text('address_line2', old('address_line2', $address->address_line2 ?? ''), ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.city') }}</label>
                                    {!! Form::text('city', old('city', $address->city ?? ''), ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.state') }}</label>
                                    {!! Form::text('state', old('state', $address->state ?? ''), ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.postal_code') }}</label>
                                    {!! Form::text('postal_code', old('postal_code', $address->postal_code ?? ''), ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="driver-documents">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.compliance_documents') }}</h4>
                                    <div class="loading d-none mb-3" id="document_loading"></div>
                                    <div class="row g-3" ng-repeat="doc in driver_doc" ng-cloak>
                                        <div class="col-12">
                                            <div class="border rounded-3 p-3">
                                                <div class="row g-3 align-items-start">
                                                    <div class="col-md-5">
                                                        <label class="form-label">@{{ doc.document_name }} <span class="text-danger">*</span></label>
                                                        <input type="file" name="file_@{{ doc.id }}" class="form-control">
                                                        <div class="text-danger small mt-1" ng-if="errors['file_'+doc.id]">@{{ errors['file_'+doc.id][0] }}</div>
                                                        <div class="mt-3">
                                                            <a ng-if="doc.document" href="@{{ doc.document }}" target="_blank">
                                                                <img ng-src="@{{ doc.document }}" class="img-thumbnail" style="max-height:110px;">
                                                            </a>
                                                            <img ng-if="!doc.document" src="{{ url('images/driver_doc.png') }}" class="img-thumbnail" style="max-height:110px;">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3" ng-if="doc.expiry_required == '1'">
                                                        <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.expire_date') }} <span class="text-danger">*</span></label>
                                                        <input type="date" name="expired_date_@{{ doc.id }}" value="@{{ doc.expired_date }}" class="form-control document_expired">
                                                        <div class="text-danger small mt-1" ng-if="errors['expired_date_'+doc.id]">@{{ errors['expired_date_'+doc.id][0] }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">{{ __('messages.admin.manage_drivers_page.status_field') }} <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="@{{ doc.doc_name }}_status">
                                                            <option value="0" ng-selected="doc.status == 0">{{ __('messages.blade_static.pending_96f608c1') }}</option>
                                                            <option value="1" ng-selected="doc.status == 1">{{ __('messages.blade_static.approved_41b81eb8') }}</option>
                                                            <option value="2" ng-selected="doc.status == 2">{{ __('messages.blade_static.rejected_27eeb7a2') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted" ng-if="!driver_doc || driver_doc.length === 0">{{ __('messages.admin.manage_drivers_page.no_compliance_docs') }}</div>
                                </div>
                            </div>
                        </div>

                        @if(LOGIN_USER_TYPE != 'company' || Auth::guard('company')->user()->id != 1)
                            <div class="col-12 bank_detail">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.bank_details') }}</h4>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.account_holder_name') }} <span class="text-danger">*</span></label>
                                                {!! Form::text('account_holder_name', old('account_holder_name', optional(optional($result->default_payout_credentials)->payout_preference)->holder_name), ['class' => 'form-control']) !!}
                                                <div class="text-danger">{{ $errors->first('account_holder_name') }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.account_number') }} <span class="text-danger">*</span></label>
                                                {!! Form::text('account_number', old('account_number', optional(optional($result->default_payout_credentials)->payout_preference)->account_number), ['class' => 'form-control']) !!}
                                                <div class="text-danger">{{ $errors->first('account_number') }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.name_of_bank') }} <span class="text-danger">*</span></label>
                                                {!! Form::text('bank_name', old('bank_name', optional(optional($result->default_payout_credentials)->payout_preference)->bank_name), ['class' => 'form-control']) !!}
                                                <div class="text-danger">{{ $errors->first('bank_name') }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.bank_location') }} <span class="text-danger">*</span></label>
                                                {!! Form::text('bank_location', old('bank_location', optional(optional($result->default_payout_credentials)->payout_preference)->bank_location), ['class' => 'form-control']) !!}
                                                <div class="text-danger">{{ $errors->first('bank_location') }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">{{ __('messages.admin.manage_drivers_page.form.code') }} <span class="text-danger">*</span></label>
                                                {!! Form::text('bank_code', old('bank_code', optional(optional($result->default_payout_credentials)->payout_preference)->branch_code), ['class' => 'form-control']) !!}
                                                <div class="text-danger">{{ $errors->first('bank_code') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade" id="driver-trips">
                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.recent_trips') }}</h4>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('messages.admin.manage_drivers_page.trip_header') }}</th>
                                                    <th>{{ __('messages.admin.manage_drivers_page.status_header') }}</th>
                                                    <th>{{ __('messages.admin.manage_drivers_page.fare_header') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($driver_recent_trips as $trip)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">#{{ $trip->id }}</div>
                                                            <div class="text-muted small">{{ $trip->pickup_location ?: __('messages.admin.manage_drivers_page.no_pickup') }}</div>
                                                        </td>
                                                        <td><span class="badge bg-light text-dark">{{ $trip->status }}</span></td>
                                                        <td>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($trip->total_fare ?? 0), 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-muted text-center">{{ __('messages.admin.no_trips_found') }}</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.recent_transactions') }}</h4>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('messages.admin.manage_drivers_page.trip_header') }}</th>
                                                    <th>{{ __('messages.admin.manage_drivers_page.payment_header') }}</th>
                                                    <th>{{ __('messages.admin.manage_drivers_page.payout_header') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($driver_recent_transactions as $trip)
                                                    <tr>
                                                        <td>#{{ $trip->id }}</td>
                                                        <td>{{ $trip->payment_mode ?: __('messages.admin.manage_drivers_page.na') }}</td>
                                                        <td>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($trip->driver_payout ?? 0), 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-muted text-center">{{ __('messages.admin.no_transactions_found') }}</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="driver-reviews">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-3">{{ __('messages.admin.manage_drivers_page.driver_reviews') }}</h4>
                            <div class="row g-3">
                                @forelse($driver_recent_reviews as $review)
                                    <div class="col-xl-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <img src="{{ optional($review->rider->profile_picture)->src ?: url('images/user.jpeg') }}" class="rounded-circle border" style="width:48px;height:48px;object-fit:cover;">
                                                <div>
                                                    <div class="fw-semibold">{{ optional($review->rider)->first_name }} {{ optional($review->rider)->last_name }}</div>
                                                    <div class="text-warning">{{ number_format((float) ($review->rider_rating ?? 0), 1) }} ★</div>
                                                </div>
                                            </div>
                                            <div class="text-muted">{{ $review->rider_comments ?: __('messages.admin.manage_drivers_page.no_comment') }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">{{ __('messages.admin.no_reviews') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ url(LOGIN_USER_TYPE.'/driver') }}" class="btn btn-light">{{ __('messages.admin.cancel') }}</a>
                <button type="submit" name="submit" value="submit" class="btn btn-primary">{{ __('messages.admin.submit') }}</button>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
</div>
@endsection
