@extends('admin.template')

@section('title', 'Site Settings | FabKin Admin & Dashboards Template')
@section('title-sub', 'Configuration')
@section('pagetitle', 'Site Settings')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="assets/libs/air-datepicker/air-datepicker.css">

    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 34px;
            height: 20px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--color-as-cccccc);
            transition: .4s;
            border-radius: 0;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 0;
        }
        input:checked + .slider {
            background-color: var(--color-as-1fbad6);
        }
        input:checked + .slider:before {
            transform: translateX(14px);
        }
        .form-label {
            font-weight: 600;
        }
        .input-group-text {
            background-color: var(--color-as-f8f9fa);
            border-radius: 0;
        }
        .image-preview {
            max-width: 140px;
            margin-top: 8px;
            border-radius: 0;
            box-shadow: 0 2px 8px var(--color-as-rgba-0-0-0-0-08);
        }
    </style>
@endsection

@section('main')
<div id="layout-wrapper">

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">{{ __('messages.admin.site_setting.site_settings_form') }}</h5>
                    </div>

                    {!! Form::open(['url' => 'admin/site_setting', 'class' => 'row g-4', 'files' => true, 'id' => 'site_settings_form']) !!}

                    <div class="col-12">
                        <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                    </div>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">{{ __('messages.blade_static.general_9239ee2c') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">{{ __('messages.blade_static.logos_branding_ecfd4e37') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" type="button" role="tab">{{ __('messages.blade_static.currency_payment_0a948a94') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab">{{ __('messages.blade_static.location_map_dd7bc43c') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="auth-tab" data-bs-toggle="tab" data-bs-target="#auth" type="button" role="tab">{{ __('messages.blade_static.social_auth_1751fc55') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">{{ __('messages.blade_static.other_settings_30b66a8e') }}</button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="settingsTabContent">

                        <!-- General Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="row g-4">
                                <!-- Site Name -->
                                <div class="col-xl-6 col-lg-6">
                                    <label class="form-label" for="input_site_name">
                                        {{ __('messages.admin.site_setting.form.site_name') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('site_name', old('site_name', site_settings('site_name')), [
                                        'class' => 'form-control',
                                        'id' => 'input_site_name',
                                        'placeholder' => __('messages.admin.site_setting.form.site_name')
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('site_name') }}</span>
                                </div>

                                <!-- Version -->
                                <div class="col-xl-6 col-lg-6">
                                    <label class="form-label" for="input_version">
                                        {{ __('messages.admin.site_setting.form.version') }}
                                    </label>
                                    {!! Form::text('version', old('version', site_settings('version')), [
                                        'class' => 'form-control',
                                        'id' => 'input_version',
                                        'placeholder' => __('messages.admin.site_setting.form.version')
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('version') }}</span>
                                </div>

                                <!-- Default Language -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_default_language">
                                        {{ __('messages.admin.site_setting.form.default_language') }}
                                    </label>
                                    {!! Form::select('default_language', $language, old('default_language', $default_language[0]->value ?? ''), [
                                        'class' => 'form-select',
                                        'id' => 'input_default_language'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('default_language') }}</span>
                                </div>

                                <!-- Admin Country Code -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_admin_country_code">
                                        {{ __('messages.admin.site_setting.form.country_code') }} <em class="text-danger">*</em>
                                    </label>
                                    <select class="form-select" id="input_admin_country_code" name="admin_country_code">
                                        <option value="" disabled>{{ __('messages.blade_static.select_85982229') }}</option>
                                        @foreach($countries as $country_code)
                                            <option value="{{ $country_code->id }}" {{ old('admin_country_code', site_settings('admin_country_code')) == $country_code->id ? 'selected' : '' }}>
                                                {{ $country_code->long_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger">{{ $errors->first('admin_country_code') }}</span>
                                </div>

                                <!-- Timezone -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_timezone">
                                        {{ __('messages.admin.site_setting.form.timezones') }}
                                    </label>
                                    {!! Form::select('timezone', $timezones, old('timezone', site_settings('timezone')), [
                                        'class' => 'form-select',
                                        'id' => 'input_timezone'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('timezone') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Branding Tab -->
                        <div class="tab-pane fade" id="branding" role="tabpanel">
                            <div class="row g-4">
                                <!-- Header Logo -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_logo">
                                        {{ __('messages.admin.site_setting.form.header_logo') }}
                                    </label>
                                    <small class="d-block text-muted mb-2">{{ __('messages.blade_static.size_140_80_5ed9d5d5') }}</small>
                                    {!! Form::file('logo', ['class' => 'form-control', 'id' => 'input_logo', 'accept' => 'image/*']) !!}
                                    <span class="text-danger">{{ $errors->first('logo') }}</span>
                                    @if($logo_url)
                                        <img src="{{ $logo_url }}" class="image-preview mt-2" alt="{{ __('messages.blade_static.header_logo_95717bc3') }}">
                                    @endif
                                </div>

                                <!-- Header White Logo -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_page_logo">
                                        {{ __('messages.admin.site_setting.form.header_white_logo') }}
                                    </label>
                                    <small class="d-block text-muted mb-2">{{ __('messages.blade_static.size_140_80_5ed9d5d5') }}</small>
                                    {!! Form::file('page_logo', ['class' => 'form-control', 'id' => 'input_page_logo', 'accept' => 'image/*']) !!}
                                    <span class="text-danger">{{ $errors->first('page_logo') }}</span>
                                    <img src="{{ url(PAGE_LOGO_URL).'?v='.rand() }}" class="image-preview mt-2" alt="{{ __('messages.blade_static.white_logo_780260ad') }}">
                                </div>

                                <!-- Favicon -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_favicon">
                                        {{ __('messages.admin.site_setting.form.favicon') }}
                                    </label>
                                    <small class="d-block text-muted mb-2">{{ __('messages.blade_static.size_16_16_a63386fc') }}</small>
                                    {!! Form::file('favicon', ['class' => 'form-control', 'id' => 'input_favicon', 'accept' => 'image/*']) !!}
                                    <span class="text-danger">{{ $errors->first('favicon') }}</span>
                                    <img src="{{ $favicon.'?v='.rand() }}" class="image-preview mt-2" alt="{{ __('messages.blade_static.favicon_b0f47aaf') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Currency & Payment Tab -->
                        <div class="tab-pane fade" id="currency" role="tabpanel">
                            <div class="row g-4">
                                <!-- Default Currency -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_default_currency">
                                        {{ __('messages.admin.site_setting.form.default_currency') }}
                                    </label>
                                    {!! Form::select('default_currency', $currency, old('default_currency', $default_currency), ['class' => 'form-select', 'id' => 'input_default_currency']) !!}
                                    <span class="text-danger">{{ $errors->first('default_currency') }}</span>
                                </div>

                                <!-- Payment Currency -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_payment_currency">
                                        {{ __('messages.admin.site_setting.form.payment_currency') }}
                                    </label>
                                    {!! Form::select('payment_currency', $currency, old('payment_currency', site_settings('payment_currency')), ['class' => 'form-select', 'id' => 'input_payment_currency']) !!}
                                    <span class="text-danger">{{ $errors->first('payment_currency') }}</span>
                                </div>

                                <!-- Trip Default Payment Method -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_trip_default_paymode">
                                        {{ __('messages.admin.site_setting.form.trip_default_payment_method') }}
                                    </label>
                                    <div ng-init="trip_default = '{{ payment_gateway('trip_default','Common') }}'">
                                        <select name="trip_default_paymode" ng-model="trip_default" class="form-select" id="input_trip_default_paymode">
                                            @foreach($payment_methods as $pay_method)
                                                <option value="{{ $pay_method['key'] }}">{{ $pay_method['value'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="text-danger">{{ $errors->first('trip_default_paymode') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_fare_mode">Fare Mode</label>
                                    {!! Form::select('fare_mode', ['Taximeter' => 'Taximeter', 'Pre-Calculated' => 'Pre-Calculated'], old('fare_mode', site_settings('fare_mode') ?: 'Taximeter'), ['class' => 'form-select', 'id' => 'input_fare_mode']) !!}
                                    <span class="text-danger">{{ $errors->first('fare_mode') }}</span>
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <h5 class="mb-3">Wallet Payment Owner</h5>
                                    <p class="text-muted mb-3">
                                        Choose who owns the payment gateway credentials used for wallet topups, trip payments, and driver payouts.
                                        <br>
                                        <strong>Admin</strong> &mdash; every payment uses the platform owner's credentials (the existing behaviour).
                                        <br>
                                        <strong>Company</strong> &mdash; each SaaS company uses its own credentials.  When a tenant has no credentials the platform's credentials are used as a fallback.
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-6">
                                    <label class="form-label" for="input_wallet_payment_owner">Payment Gateway Ownership Mode</label>
                                    @php $walletPaymentOwner = old('wallet_payment_owner', site_settings('wallet_payment_owner') ?: 'admin'); @endphp
                                    {!! Form::select('wallet_payment_owner', ['admin' => 'Admin (Platform Owned)', 'company' => 'Company (Tenant Owned)'], $walletPaymentOwner, ['class' => 'form-select', 'id' => 'input_wallet_payment_owner']) !!}
                                    <span class="text-danger">{{ $errors->first('wallet_payment_owner') }}</span>
                                </div>

                                <div class="col-xl-6 col-lg-6">
                                    <label class="form-label" for="input_user_level_owner">User Level Ownership Mode</label>
                                    @php $userLevelOwner = old('user_level_owner', site_settings('user_level_owner') ?: 'admin'); @endphp
                                    {!! Form::select('user_level_owner', ['admin' => 'Admin (Platform Owned)', 'company' => 'Company (Adopt & Override)'], $userLevelOwner, ['class' => 'form-select', 'id' => 'input_user_level_owner']) !!}
                                    <span class="text-danger">{{ $errors->first('user_level_owner') }}</span>
                                    <div class="form-text">
                                        When set to <strong>Company</strong>, SaaS tenants can adopt the admin-defined driver and customer levels and override only the values they care about (thresholds, reward amount, icon, etc.).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location & Map Tab -->
                        <div class="tab-pane fade" id="location" role="tabpanel">
                            <div class="row g-4">
                                <!-- Driver Kilometer -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_driver_km">
                                        {{ __('messages.admin.site_setting.form.driver_kilometer') }}
                                    </label>
                                    {!! Form::number('driver_km', old('driver_km', site_settings('driver_km')), ['class'=>'form-control','id'=>'input_driver_km','min'=>'1']) !!}
                                    <span class="text-danger">{{ $errors->first('driver_km') }}</span>
                                </div>

                                <!-- Pickup Kilometer -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_pickup_km">
                                        {{ __('messages.admin.site_setting.form.pickup_kilometer') }}
                                        <small class="text-muted ms-2">(* {{ __('messages.admin.site_setting.pool') }})</small>
                                    </label>
                                    {!! Form::number('pickup_km', old('pickup_km', site_settings('pickup_km')), ['class'=>'form-control','id'=>'input_pickup_km','min'=>'1']) !!}
                                    <span class="text-danger">{{ $errors->first('pickup_km') }}</span>
                                </div>

                                <!-- Drop Kilometer -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_drop_km">
                                        {{ __('messages.admin.site_setting.form.drop_kilometer') }}
                                        <small class="text-muted ms-2">(* {{ __('messages.admin.site_setting.pool') }})</small>
                                    </label>
                                    {!! Form::number('drop_km', old('drop_km', site_settings('drop_km')), ['class'=>'form-control','id'=>'input_drop_km','min'=>'1']) !!}
                                    <span class="text-danger">{{ $errors->first('drop_km') }}</span>
                                </div>

                                <!-- Heat Map -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_heat_map">
                                        {{ __('messages.admin.site_setting.form.heat_map') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::select('heat_map',['On'=>'On','Off'=>'Off'],old('heat_map',site_settings('heat_map')),['class'=>'form-select','id'=>'input_heat_map']) !!}
                                    <span class="text-danger">{{ $errors->first('heat_map') }}</span>
                                </div>

                                <!-- Heat Map Hours -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_heat_map_hours">
                                        {{ __('messages.admin.site_setting.form.heat_map_hours') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('heat_map_hours', old('heat_map_hours', site_settings('heat_map_hours')), ['class'=>'form-control','id'=>'input_heat_map_hours']) !!}
                                    <span class="text-danger">{{ $errors->first('heat_map_hours') }}</span>
                                </div>

                                <!-- Update Location Interval -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_update_loc_interval">
                                        {{ __('messages.admin.site_setting.form.update_location_interval') }}
                                        <i class="glyphicon glyphicon-info-sign ms-1" data-toggle="tooltip" title="Interval in Seconds to update driver's current location"></i>
                                        <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('update_loc_interval', old('update_loc_interval', site_settings('update_loc_interval')), ['class'=>'form-control','id'=>'input_update_loc_interval']) !!}
                                    <span class="text-danger">{{ $errors->first('update_loc_interval') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Social & Auth Tab -->
                        <div class="tab-pane fade" id="auth" role="tabpanel">
                            <div class="row g-4">
                                @php $social_logins = explode(',', site_settings('social_logins') ?? ''); @endphp

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">{{ __('messages.blade_static.facebook_login_bba1c5c5') }}</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('social_logins[]', 'facebook', in_array('facebook', $social_logins)) !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">{{ __('messages.blade_static.google_login_2f7074e8') }}</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('social_logins[]', 'google', in_array('google', $social_logins)) !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <!-- OTP Verification -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">
                                        {{ __('messages.admin.site_setting.form.otp_verification') }}
                                        <i class="glyphicon glyphicon-info-sign ms-1" data-toggle="tooltip" title="{{ __('messages.blade_static.otp_for_phone_number_verification_when_creating_an_accou_c69d6282') }}"></i>
                                    </label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('otp_verification', 1, site_settings('otp_verification')) !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">
                                        Demo Mode
                                        <i class="glyphicon glyphicon-info-sign ms-1" data-toggle="tooltip" title="When enabled, all backend OTP checks use 123456 and skip live SMS delivery."></i>
                                    </label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('demo_mode', 1, site_settings('demo_mode') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                    <div class="form-text">Test OTP: <strong>123456</strong></div>
                                </div>

                                <!-- COVID Feature -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">{{ __('messages.admin.site_setting.form.covid_feature') }}</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('covid_enable', 1, site_settings('covid_enable')) !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <!-- Default OTP Method -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_default_otp">
                                        {{ __('messages.admin.site_setting.form.default_auth') }} <em class="text-danger">*</em>
                                    </label>
                                                                        <select name="default_otp" class="form-select" id="input_default_otp">
                                        <option value="twilio" {{ site_settings('default_otp') == 'twilio' ? 'selected' : '' }}>{{ __('messages.blade_static.twilio_26dbb083') }}</option>
                                        <option value="firebase" {{ site_settings('default_otp') == 'firebase' ? 'selected' : '' }}>{{ __('messages.blade_static.firebase_aa234fb6') }}</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <h5 class="mb-3">Company Onboarding</h5>
                                    <p class="text-muted mb-0">Control whether companies use the self-onboarding wizard, what steps are required, and whether admin approval is mandatory before dashboard access.</p>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Enable Company Self-Onboarding Wizard</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('company_onboarding_enabled', 1, site_settings('company_onboarding_enabled') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Require Phone Verification</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('company_onboarding_require_phone_verification', 1, site_settings('company_onboarding_require_phone_verification') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Require Email Verification</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('company_onboarding_require_email_verification', 1, site_settings('company_onboarding_require_email_verification') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Require Logo Upload</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('company_onboarding_require_logo_upload', 1, site_settings('company_onboarding_require_logo_upload') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Require Company Documents Upload</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('company_onboarding_require_documents_upload', 1, site_settings('company_onboarding_require_documents_upload') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Require Admin Approval Before Dashboard Access</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('company_onboarding_require_admin_approval', 1, site_settings('company_onboarding_require_admin_approval') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Auto-approve Driver Profile Image</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('driver_profile_image_auto_approval', 1, site_settings('driver_profile_image_auto_approval') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Other Settings Tab -->
                        <div class="tab-pane fade" id="other" role="tabpanel">
                            <div class="row g-4">
                                <!-- Driver Request Seconds -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_driver_request_seconds">
                                        {{ __('messages.admin.site_setting.form.driver_request_seconds') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('driver_request_seconds', old('driver_request_seconds', site_settings('driver_request_seconds')), [
                                        'class' => 'form-control',
                                        'id' => 'input_driver_request_seconds',
                                        'placeholder' => 'Driver Request Seconds'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('driver_request_seconds') }}</span>
                                </div>

                                <!-- Driver Request Limit -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_driver_request_limit">
                                        {{ __('messages.admin.site_setting.form.driver_request_limit') }}
                                        <i class="glyphicon glyphicon-info-sign ms-1" data-toggle="tooltip" title="{{ __('messages.blade_static.maximum_drivers_to_send_trip_request_to_7b8554a4') }}"></i>
                                        <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('driver_request_limit', old('driver_request_limit', site_settings('driver_request_limit')), [
                                        'class' => 'form-control',
                                        'id' => 'input_driver_request_limit',
                                        'placeholder' => 'Driver Request Limit'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('driver_request_limit') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Favourite Driver Priority</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('favorite_driver_enabled', 1, site_settings('favorite_driver_enabled') === '1') !!}
                                        <span class="slider round"></span>
                                    </label>
                                    <div class="form-text">When enabled, riders can favourite drivers and dispatch will try those nearby favourites first.</div>
                                </div>

                                <!-- Driver Owe Limit -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_driver_owe_limit">
                                        {{ __('messages.admin.site_setting.form.pay_to_admin_driver_limit') }}
                                        <i class="glyphicon glyphicon-info-sign ms-1" data-toggle="tooltip" title="{{ __('messages.blade_static.maximum_owe_amount_before_driver_cannot_accept_trips_3761c99e') }}"></i>
                                        <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('driver_owe_limit', old('driver_owe_limit', site_settings('driver_owe_limit')), [
                                        'class' => 'form-control',
                                        'id' => 'input_driver_owe_limit',
                                        'placeholder' => 'Driver Owe Amount Limit'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('driver_owe_limit') }}</span>
                                </div>

                                <!-- Manual Booking Contact Number -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_admin_contact">
                                        {{ __('messages.admin.site_setting.form.manual_booking_contact_number') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('admin_contact', old('admin_contact', site_settings('admin_contact')), [
                                        'class' => 'form-control',
                                        'id' => 'input_admin_contact',
                                        'placeholder' => 'Contact Number'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('admin_contact') }}</span>
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <h5 class="mb-3">Dispatch Console Settings</h5>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_minimum_scheduled_lead_minutes">Minimum Scheduled Lead Minutes <em class="text-danger">*</em></label>
                                    {!! Form::number('minimum_scheduled_lead_minutes', old('minimum_scheduled_lead_minutes', site_settings('minimum_scheduled_lead_minutes') ?: 15), [
                                        'class' => 'form-control',
                                        'id' => 'input_minimum_scheduled_lead_minutes',
                                        'min' => 0
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('minimum_scheduled_lead_minutes') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_scheduled_dispatch_lead_minutes">Scheduled Dispatch Lead Minutes <em class="text-danger">*</em></label>
                                    {!! Form::number('scheduled_dispatch_lead_minutes', old('scheduled_dispatch_lead_minutes', site_settings('scheduled_dispatch_lead_minutes') ?: 5), [
                                        'class' => 'form-control',
                                        'id' => 'input_scheduled_dispatch_lead_minutes',
                                        'min' => 0
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('scheduled_dispatch_lead_minutes') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_default_radius_miles">Default Radius (Miles) <em class="text-danger">*</em></label>
                                    {!! Form::number('default_radius_miles', old('default_radius_miles', site_settings('default_radius_miles') ?: 10), [
                                        'class' => 'form-control',
                                        'id' => 'input_default_radius_miles',
                                        'min' => 1
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('default_radius_miles') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_default_assignment_timeout_seconds">Default Assignment Timeout (Seconds) <em class="text-danger">*</em></label>
                                    {!! Form::number('default_assignment_timeout_seconds', old('default_assignment_timeout_seconds', site_settings('default_assignment_timeout_seconds') ?: 30), [
                                        'class' => 'form-control',
                                        'id' => 'input_default_assignment_timeout_seconds',
                                        'min' => 5
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('default_assignment_timeout_seconds') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_asap_assignment_timeout_seconds">ASAP Assignment Timeout (Seconds) <em class="text-danger">*</em></label>
                                    {!! Form::number('asap_assignment_timeout_seconds', old('asap_assignment_timeout_seconds', site_settings('asap_assignment_timeout_seconds') ?: 20), [
                                        'class' => 'form-control',
                                        'id' => 'input_asap_assignment_timeout_seconds',
                                        'min' => 5
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('asap_assignment_timeout_seconds') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_scheduled_assignment_timeout_seconds">Scheduled Assignment Timeout (Seconds) <em class="text-danger">*</em></label>
                                    {!! Form::number('scheduled_assignment_timeout_seconds', old('scheduled_assignment_timeout_seconds', site_settings('scheduled_assignment_timeout_seconds') ?: 45), [
                                        'class' => 'form-control',
                                        'id' => 'input_scheduled_assignment_timeout_seconds',
                                        'min' => 5
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('scheduled_assignment_timeout_seconds') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_max_assignment_attempts">Max Assignment Attempts <em class="text-danger">*</em></label>
                                    {!! Form::number('max_assignment_attempts', old('max_assignment_attempts', site_settings('max_assignment_attempts') ?: 10), [
                                        'class' => 'form-control',
                                        'id' => 'input_max_assignment_attempts',
                                        'min' => 1
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('max_assignment_attempts') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Enable Manual Booking Module</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('manual_booking_module_enabled', 1, site_settings('manual_booking_module_enabled') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                    <div class="form-text">Admin can disable company access to manual booking without uninstalling the feature.</div>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Allow ASAP Manual Booking</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('allow_asap_manual_booking', 1, site_settings('allow_asap_manual_booking') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Allow Dispatch Time Override</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('allow_dispatch_time_override', 1, site_settings('allow_dispatch_time_override') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Auto Expand Radius</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('auto_expand_radius', 1, site_settings('auto_expand_radius') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Enable Broadcast Dispatch</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('enable_broadcast_dispatch', 1, site_settings('enable_broadcast_dispatch') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Enable Sequential Dispatch</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('enable_sequential_dispatch', 1, site_settings('enable_sequential_dispatch') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label">Enable Force Dispatch</label>
                                    <label class="switch ms-2">
                                        {!! Form::checkbox('enable_force_dispatch', 1, site_settings('enable_force_dispatch') !== '0') !!}
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <!-- Head Code -->
                                <div class="col-xl-8 col-lg-12">
                                    <label class="form-label" for="input_head_code">
                                        {{ __('messages.admin.site_setting.form.head_code') }}
                                    </label>
                                    {!! Form::textarea('head_code', old('head_code', site_settings('head_code')), [
                                        'class' => 'form-control',
                                        'id' => 'input_head_code',
                                        'rows' => 3
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('head_code') }}</span>
                                </div>

                                <!-- Copyright Settings -->
                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_copyright_year">
                                        {{ __('messages.admin.site_setting.form.copyright_year') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('copyright_year', old('copyright_year', site_settings('copyright_year')), [
                                        'class' => 'form-control',
                                        'id' => 'input_copyright_year',
                                        'placeholder' => 'Copyright Year'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('copyright_year') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_copyright_url">
                                        {{ __('messages.admin.site_setting.form.copyright_url') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('copyright_url', old('copyright_url', site_settings('copyright_url')), [
                                        'class' => 'form-control',
                                        'id' => 'input_copyright_url',
                                        'placeholder' => 'https://yourdomain.com'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('copyright_url') }}</span>
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <label class="form-label" for="input_copyright_name">
                                        {{ __('messages.admin.site_setting.form.copyright_name') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('copyright_name', old('copyright_name', site_settings('copyright_name')), [
                                        'class' => 'form-control',
                                        'id' => 'input_copyright_name',
                                        'placeholder' => 'Copyright Name'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('copyright_name') }}</span>
                                </div>
                            </div>
                        </div>

                    </div> <!-- End Tabs Content -->

                    <!-- Submit / Reset -->
                    <div class="col-12 mt-5 text-center">
                        <button type="submit" name="submit" value="submit" class="btn btn-primary px-5 py-2">
                            {{ __('messages.admin.submit') }}
                        </button>
                        <button type="reset" class="btn btn-outline-secondary px-5 py-2 ms-3">
                            {{ __('messages.admin.cancel') }}
                        </button>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div><!-- End row -->

</div><!-- End layout-wrapper -->
</main>
@endsection

@section('js')
    <!-- Select js -->
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="assets/js/form/forms-select.init.js"></script>

    <!-- Datepicker Js -->
    <script src="assets/libs/air-datepicker/air-datepicker.js"></script>

    <!-- App js -->
    <script type="module" src="assets/js/app.js"></script>

    <!-- Tooltip initialization -->
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
