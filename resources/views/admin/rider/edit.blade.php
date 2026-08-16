@extends('admin.template')

@section('title-sub', __('messages.admin.manage_riders_page.edit_rider'))
@section('pagetitle', __('messages.admin.manage_riders_page.edit_rider'))

@section('main')
<div id="layout-wrapper" ng-controller="destination_admin">
    {!! Form::open(['url' => 'admin/edit_rider/'.$result->id, 'files' => true]) !!}
    {!! Form::hidden('user_type', 'Rider') !!}

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">{{ __('messages.admin.manage_riders_page.customer') }} #{{ $result->uuid ?? $result->id }}</h3>
                    <div class="text-muted">{{ __('messages.admin.manage_riders_page.workspace_description') }}</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if(Auth::guard('admin')->user()->can('delete_rider'))
                        <a href="{{ url('admin/delete_rider/'.$result->id) }}" class="btn btn-outline-danger" onclick="return confirm('{{ __('messages.admin.manage_riders_page.delete_customer_confirm') }}')">
                            <i class="ri-delete-bin-line me-1"></i>{{ __('messages.admin.delete') }}
                        </a>
                    @endif
                    <a href="{{ url('admin/rider') }}" class="btn btn-light">{{ __('messages.admin.back') }}</a>
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
                    <h4 class="mb-4">{{ __('messages.admin.manage_riders_page.customer_info') }}</h4>
                    <div class="d-flex gap-4 flex-wrap align-items-start">
                        <img src="{{ optional($result->profile_picture)->src ?: url('images/user.jpeg') }}" alt="{{ $result->first_name }}" class="rounded-circle border" style="width:132px;height:132px;object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <h3 class="mb-0">{{ $result->first_name }} {{ $result->last_name }}</h3>
                                <span class="badge bg-info-subtle text-info">{{ optional(optional($rider_level_assignment)->level)->name ?: __('messages.admin.manage_riders_page.unassigned') }}</span>
                                <span class="badge {{ $result->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $result->status }}</span>
                            </div>
                            <div class="mb-2"><strong>{{ __('messages.admin.phone_label') }}</strong> +{{ $result->country_code }}{{ $result->mobile_number }}</div>
                            <div class="mb-2"><strong>{{ __('messages.admin.email_label') }}</strong> {{ protectedString($result->email) }}</div>
                            <div><strong>{{ __('messages.admin.home_location') ?? __('messages.blade_static.home_location_e3f72eae') }}:</strong> {{ $location->home ?? __('messages.admin.not_provided') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.rate_info') }}</h4>
                    <div class="row g-3">
                        @foreach([
                            ['label' => __('messages.admin.manage_riders_page.total_digital_payment'), 'value' => number_format((float) ($rider_rate_info['digital_payment_rate'] ?? 0), 2).' %'],
                            ['label' => __('messages.admin.manage_riders_page.success_rate'), 'value' => number_format((float) ($rider_rate_info['success_rate'] ?? 0), 2).' %'],
                            ['label' => __('messages.admin.manage_riders_page.total_review_given'), 'value' => number_format((float) ($rider_rate_info['reviews_given_rate'] ?? 0), 2).' %'],
                            ['label' => __('messages.admin.manage_riders_page.cancellation_rate'), 'value' => number_format((float) ($rider_rate_info['cancellation_rate'] ?? 0), 2).' %'],
                        ] as $metric)
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>{{ $metric['label'] }}</span>
                                        <strong>{{ $metric['value'] }}</strong>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar" style="width: {{ min(100, (float) str_replace(' %', '', $metric['value'])) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#rider-overview" type="button">{{ __('messages.admin.tab_overview') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#rider-edit-profile" type="button">{{ __('messages.admin.tab_edit_profile') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#rider-trips" type="button">{{ __('messages.admin.tab_trips') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#rider-transactions" type="button">{{ __('messages.admin.tab_transaction') }}</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#rider-reviews" type="button">{{ __('messages.blade_static.review_e29a79fe') }}</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="rider-overview">
                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.customer_details') }}</h4>
                                    <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#rider-trip-stats" type="button">{{ __('messages.blade_static.trip_9dda9aa7') }}</button></li>
                                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#rider-duty-review" type="button">{{ __('messages.admin.tab_duty_review') }}</button></li>
                                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#rider-wallet-points" type="button">{{ __('messages.blade_static.wallet_b60862f4') }}</button></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="rider-trip-stats">
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.total_completed_trip') }}</span><strong>{{ $rider_trip_stats['completed'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.total_cancel_trip') }}</span><strong>{{ $rider_trip_stats['cancelled'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.lowest_price_trip') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($rider_trip_stats['lowest_fare'] ?? 0), 2) }}</strong></div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.highest_price_trip') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($rider_trip_stats['highest_fare'] ?? 0), 2) }}</strong></div>
                                            <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_riders_page.wallet_balance') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($rider_trip_stats['wallet_balance'] ?? 0), 2) }}</strong></div>
                                        </div>
                                        <div class="tab-pane fade" id="rider-duty-review">
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.total_review_given') }}</span><strong>{{ $rider_trip_stats['reviews_given'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_riders_page.total_active_hour') }}</span><strong>{{ $rider_trip_stats['active_hours'] ?? '0:0h' }}</strong></div>
                                        </div>
                                        <div class="tab-pane fade" id="rider-wallet-points">
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>{{ __('messages.admin.manage_riders_page.total_level_point') }} ( {{ optional(optional($rider_level_assignment)->level)->name ?: __('messages.admin.manage_riders_page.unassigned') }} - {{ number_format((float) ($rider_trip_stats['level_points'] ?? 0), 0) }}/{{ number_format((float) ($rider_trip_stats['level_target_points'] ?? 0), 0) }} )</span>
                                                <strong>{{ number_format((float) ($rider_trip_stats['level_points'] ?? 0), 0) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_riders_page.wallet_money') }}</span><strong>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($rider_trip_stats['wallet_balance'] ?? 0), 2) }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.attached_documents') }}</h4>
                                    @forelse(($customer_documents ?? collect()) as $doc)
                                        <a href="{{ $doc->document }}" target="_blank" class="d-flex align-items-center justify-content-between border rounded-3 p-3 mb-3 text-decoration-none">
                                            <div>
                                                <div class="fw-semibold">{{ optional($doc->documents)->document_name ?? __('messages.admin.manage_riders_page.document') }}</div>
                                                <div class="text-muted small">{{ ['0' => __('messages.blade_static.pending_96f608c1'), '1' => __('messages.blade_static.approved_41b81eb8'), '2' => __('messages.blade_static.rejected_27eeb7a2')][(string) $doc->status] ?? __('messages.admin.manage_riders_page.uploaded') }}</div>
                                            </div>
                                            <i class="ri-download-line fs-5"></i>
                                        </a>
                                    @empty
                                        <div class="text-muted">{{ __('messages.admin.manage_riders_page.no_documents_uploaded') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.blade_static.additional_info_bdcc9d9b') }}</h4>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.home_location') ?? __('messages.blade_static.home_location_e3f72eae') }}</span><strong>{{ $location->home ?? __('messages.admin.not_provided') }}</strong></div>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.work_location') ?? __('messages.blade_static.work_location_dca2864d') }}</span><strong>{{ $location->work ?? __('messages.admin.not_provided') }}</strong></div>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.kyc_status') }}</span><strong>{{ ucfirst(str_replace('_', ' ', $result->kyc_status ?? 'not_started')) }}</strong></div>
                                    <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ __('messages.admin.manage_riders_page.facial_verification') }}</span><strong>{{ ucfirst(str_replace('_', ' ', $result->facial_verification_status ?? 'not_started')) }}</strong></div>
                                    <div class="d-flex justify-content-between py-2"><span>{{ __('messages.admin.manage_riders_page.overall_verification') }}</span><strong>{{ ucfirst(str_replace('_', ' ', $result->verification_status ?? 'required')) }}</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card h-100 mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.verification_captures') }}</h4>
                                    @php($faceData = is_array($result->facial_verification_data ?? null) ? $result->facial_verification_data : json_decode($result->facial_verification_data ?? '[]', true))
                                    @if(!empty($faceData))
                                        <div class="row g-2">
                                            @foreach(['center_image' => __('messages.admin.manage_riders_page.center'), 'left_image' => __('messages.admin.manage_riders_page.left'), 'right_image' => __('messages.admin.manage_riders_page.right'), 'nod_image' => __('messages.admin.manage_riders_page.nod')] as $key => $label)
                                                @if(!empty($faceData[$key]))
                                                    <div class="col-md-6">
                                                        <a href="{{ $faceData[$key] }}" target="_blank" class="text-decoration-none">
                                                            <img src="{{ $faceData[$key] }}" class="img-fluid rounded border mb-2" alt="{{ $label }}">
                                                            <div class="small fw-semibold">{{ $label }}</div>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted">{{ __('messages.admin.manage_riders_page.no_verification_captures') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rider-edit-profile">
                    <div class="row g-4">
                        <div class="col-xl-4">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.profile_image') }}</h4>
                                    <img src="{{ optional($result->profile_picture)->src ?: url('images/user.jpeg') }}" alt="{{ $result->first_name }}" class="rounded-circle border mb-3" style="width:180px;height:180px;object-fit:cover;">
                                    <input type="file" name="profile_image" class="form-control">
                                    <div class="text-danger mt-1">{{ $errors->first('profile_image') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.first_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('first_name', old('first_name', $result->first_name), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('first_name') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.last_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('last_name', old('last_name', $result->last_name), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('last_name') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.email') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('email', old('email', $result->email), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('email') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.password') }}</label>
                                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => __('messages.blade_static.leave_blank_to_keep_current_password_efaed3a1')]) !!}
                                    <div class="text-danger">{{ $errors->first('password') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.country_code') }} <span class="text-danger">*</span></label>
                                    <select class="form-select" id="input_country_code" name="country_code">
                                        @foreach($country_code_option as $country_code)
                                            <option value="{{ $country_code->phone_code }}" data-id="{{ $country_code->id }}" {{ $result->country_id == $country_code->id ? 'selected' : '' }}>
                                                {{ $country_code->long_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! Form::hidden('country_id', $result->country_id, ['id' => 'country_id']) !!}
                                    <div class="text-danger">{{ $errors->first('country_code') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.mobile_number') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('mobile_number', old('mobile_number', $result->mobile_number), ['class' => 'form-control']) !!}
                                    <div class="text-danger">{{ $errors->first('mobile_number') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">{{ __('messages.admin.manage_riders_page.form.gender') }} <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4 pt-2">
                                        <div class="form-check">
                                            {{ Form::radio('gender', '1', old('gender', $result->getOriginal('gender')) == '1', ['class' => 'form-check-input', 'id' => 'rider_gender_male']) }}
                                            <label class="form-check-label" for="rider_gender_male">{{ __('messages.profile.male') }}</label>
                                        </div>
                                        <div class="form-check">
                                            {{ Form::radio('gender', '2', old('gender', $result->getOriginal('gender')) == '2', ['class' => 'form-check-input', 'id' => 'rider_gender_female']) }}
                                            <label class="form-check-label" for="rider_gender_female">{{ __('messages.profile.female') }}</label>
                                        </div>
                                    </div>
                                    <div class="text-danger">{{ $errors->first('gender') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.form.status') }} <span class="text-danger">*</span></label>
                                    {!! Form::select('status', ['Active' => __('messages.blade_static.active_ea35693d') ?? 'Active', 'Inactive' => __('messages.blade_static.inactive_icon_bc96e971') ?? 'Inactive'], old('status', $result->status), ['class' => 'form-select']) !!}
                                    <div class="text-danger">{{ $errors->first('status') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.customer_level') }}</label>
                                    {!! Form::select('rider_level_id', $rider_level_options ?? [], optional($rider_level_assignment)->user_level_id, ['class' => 'form-select', 'placeholder' => __('messages.admin.manage_riders_page.auto_assign_based_on_rules')]) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.kyc_status') }}</label>
                                    {!! Form::select('kyc_status', ['not_started' => __('messages.admin.manage_riders_page.not_started'), 'pending' => __('messages.admin.manage_riders_page.pending_review'), 'approved' => __('messages.blade_static.approved_41b81eb8'), 'rejected' => __('messages.blade_static.rejected_27eeb7a2')], old('kyc_status', $result->kyc_status ?? 'not_started'), ['class' => 'form-select']) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.manage_riders_page.facial_verification') }}</label>
                                    {!! Form::select('facial_verification_status', ['not_started' => __('messages.admin.manage_riders_page.not_started'), 'pending' => __('messages.admin.manage_riders_page.pending_review'), 'approved' => __('messages.blade_static.approved_41b81eb8'), 'rejected' => __('messages.blade_static.rejected_27eeb7a2')], old('facial_verification_status', $result->facial_verification_status ?? 'not_started'), ['class' => 'form-select']) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.home_location') ?? __('messages.blade_static.home_location_e3f72eae') }}</label>
                                    {!! Form::text('home_location', old('home_location', $location->home ?? ''), ['class' => 'form-control', 'id' => 'input_home_location', 'autocomplete' => 'off']) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.admin.work_location') ?? __('messages.blade_static.work_location_dca2864d') }}</label>
                                    {!! Form::text('work_location', old('work_location', $location->work ?? ''), ['class' => 'form-control', 'id' => 'input_work_location', 'autocomplete' => 'off']) !!}
                                </div>
                                {!! Form::hidden('home_latitude', old('home_latitude', $location->home_latitude ?? ''), ['id' => 'home_latitude']) !!}
                                {!! Form::hidden('home_longitude', old('home_longitude', $location->home_longitude ?? ''), ['id' => 'home_longitude']) !!}
                                {!! Form::hidden('work_latitude', old('work_latitude', $location->work_latitude ?? ''), ['id' => 'work_latitude']) !!}
                                {!! Form::hidden('work_longitude', old('work_longitude', $location->work_longitude ?? ''), ['id' => 'work_longitude']) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rider-trips">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.recent_trips') }}</h4>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.blade_static.trip_9dda9aa7') }}</th>
                                            <th>{{ __('messages.blade_static.driver_b5c7d8b5') }}</th>
                                            <th>{{ __('messages.blade_static.status_bae7d5be') }}</th>
                                            <th>{{ __('messages.blade_static.fare_fccb9084') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rider_recent_trips as $trip)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">#{{ $trip->id }}</div>
                                                    <div class="text-muted small">{{ $trip->pickup_location ?: __('messages.admin.manage_riders_page.no_pickup') }}</div>
                                                </td>
                                                <td>{{ $trip->driver_name ?? optional($trip->driver)->first_name }}</td>
                                                <td><span class="badge bg-light text-dark">{{ $trip->status }}</span></td>
                                                <td>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($trip->total_fare ?? 0), 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-muted text-center">{{ __('messages.admin.manage_riders_page.no_trips_found') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rider-transactions">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.recent_transactions') }}</h4>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.blade_static.trip_9dda9aa7') }}</th>
                                            <th>{{ __('messages.blade_static.payment_b41a92be') }}</th>
                                            <th>{{ __('messages.admin.total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rider_recent_transactions as $trip)
                                            <tr>
                                                <td>#{{ $trip->id }}</td>
                                                <td>{{ $trip->payment_mode ?: 'N/A' }}</td>
                                                <td>{{ html_entity_decode(currency_symbol()) }} {{ number_format((float) ($trip->total_fare ?? 0), 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted text-center">{{ __('messages.admin.manage_riders_page.no_transactions_found') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rider-reviews">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h4 class="mb-3">{{ __('messages.admin.manage_riders_page.customer_reviews') }}</h4>
                            <div class="row g-3">
                                @forelse($rider_recent_reviews as $review)
                                    <div class="col-xl-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <img src="{{ optional($review->driver->profile_picture)->src ?: url('images/user.jpeg') }}" class="rounded-circle border" style="width:48px;height:48px;object-fit:cover;">
                                                <div>
                                                    <div class="fw-semibold">{{ optional($review->driver)->first_name }} {{ optional($review->driver)->last_name }}</div>
                                                    <div class="text-warning">{{ number_format((float) ($review->driver_rating ?? 0), 1) }} ★</div>
                                                </div>
                                            </div>
                                            <div class="text-muted">{{ $review->driver_comments ?: __('messages.admin.no_comment_provided') }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">{{ __('messages.admin.manage_riders_page.no_reviews_available') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ url('admin/rider') }}" class="btn btn-light">{{ __('messages.admin.cancel') }}</a>
                <button type="submit" name="submit" value="submit" class="btn btn-primary">{{ __('messages.admin.submit') }}</button>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countrySelect = document.getElementById('input_country_code');
    if (countrySelect) {
        const syncCountry = function () {
            const selected = countrySelect.options[countrySelect.selectedIndex];
            document.getElementById('country_id').value = selected ? selected.getAttribute('data-id') : '';
        };
        countrySelect.addEventListener('change', syncCountry);
        syncCountry();
    }
});
</script>
@endsection
