@extends('marketing.auth-layout')
@section('title', trans('messages.user.need_partner') . ' · ' . $site_name)

@section('main')
<div ng-controller="facebook_account_kit">
    @include('user.otp_popup')

    @if(Session::has('message'))
        <div class="site-alert" role="alert">{!! Session::get('message') !!}</div>
    @endif

    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/driver-portrait.webp') }}" alt="{{ $site_name }} {{ trans('messages.user.need_partner') }}" width="1200" height="900" fetchpriority="high">
            <h2>{{ trans('messages.user.drive_with_Cabme') }} {{ $site_name }}</h2>
        </div>

        <div class="auth-form">
            <h1>{{ trans('messages.user.create_account') }}</h1>
            <p>{{ trans('messages.user.need_partner_content') }}</p>

            @if ($errors->any())
                <div class="site-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            {{ Form::open(array('url' => 'driver_register','id'=>'form')) }}
            {{ csrf_field() }}
            {!! Form::hidden('request_type', '', ['id' => 'request_type' ]) !!}
            {!! Form::hidden('otp', '', ['id' => 'otp' ]) !!}
            <input type="hidden" name="user_type" value="Driver">

            <div class="field-grid">
                <div class="site-field">
                    <label for="fname">{{ trans('messages.user.firstname') }}</label>
                    {!! Form::text('first_name', '', ['placeholder' => trans('messages.user.firstname'),'id' => 'fname' ]) !!}
                    <span class="field-error first_name_error">{{ $errors->first('first_name') }}</span>
                </div>
                <div class="site-field">
                    <label for="lname">{{ trans('messages.user.lastname') }}</label>
                    {!! Form::text('last_name', '', ['placeholder' => trans('messages.user.lastname'),'id' => 'lname' ]) !!}
                    <span class="field-error last_name_error">{{ $errors->first('last_name') }}</span>
                </div>
            </div>

            <div class="site-field">
                <label for="input-email">{{ trans('messages.user.email') }}</label>
                {!! Form::text('email', '', ['placeholder' => trans('messages.user.email'),'id' => 'input-email' ]) !!}
                <span class="field-error email_error">{{ $errors->first('email') }}</span>
            </div>

            <div class="site-field" ng-init="old_country_code={{ old('country_code') != null ? old('country_code') : '1' }}">
                <label for="mobile_country">{{ trans('messages.profile.mobile') }}</label>
                <div class="site-field-row">
                    <select name="country_code" id="mobile_country" aria-label="{{ trans('messages.profile.country') }}">
                        @foreach($country as $key => $value)
                            <option value="{{ $value->phone_code }}" {{ ($value->id == (old('country_id') != null ? old('country_id') : '1')) ? 'selected' : '' }} data-value="+{{ $value->phone_code }}" data-id="{{ $value->id }}">{{ $value->long_name }}</option>
                        @endforeach
                    </select>
                    {!! Form::hidden('country_id', old('country_id'), array('id'=>'country_id')) !!}
                    <div class="site-field-grow">
                        {!! Form::tel('mobile_number', isset($phone_number) ? $phone_number : '', ['placeholder' => trans('messages.profile.mobile'),'id' => 'mobile' ]) !!}
                    </div>
                </div>
                {!! Form::hidden('country_code', old('country_code', (isset($country_code) ? $country_code : ''))) !!}
                <span class="field-error country_code_error">{{ $errors->first('country_code') }}</span>
                <span class="field-error mobile_number_error">{{ $errors->first('mobile_number') }}</span>
            </div>

            <div class="site-field">
                <label for="gender_options">{{ trans('messages.profile.gender') }}</label>
                <select name="gender" id="gender_options" >
                    <option value="" disabled selected hidden>{{ __('messages.driver_dashboard.select').' '.__('messages.profile.gender') }}</option>
                    <option value="1">{{ __('messages.profile.male') }}</option>
                    <option value="2">{{ __('messages.profile.female') }}</option>
                </select>
                <span class="field-error gender_error">{{ $errors->first('gender') }}</span>
            </div>

            <div class="site-field">
                <label for="password">{{ trans('messages.user.paswrd') }}</label>
                {!! Form::password('password', array(placeholder' => trans('messages.user.paswrd'),'id' => 'password') ) !!}
                <span class="field-error password_error">{{ $errors->first('password') }}</span>
            </div>

            <div class="site-field">
                <label for="home_address">{{ trans('messages.profile.profile_city') }}</label>
                <div class="autocomplete-input">
                    {!! Form::text('home_address', '', ['placeholder' => trans('messages.profile.profile_city'),'id' => 'home_address','autocomplete' => 'false']) !!}
                </div>
                <ul class="autocomplete-results home_address"></ul>
                <input type="hidden" name="city" id='city' value="">
                <input type="hidden" name="state" id="state" value="">
                <input type="hidden" name="country" id="country" value="">
                <input type="hidden" name="address_line1" id="address_line1" value="">
                <input type="hidden" name="address_line2" id="address_line2" value="">
                <input type="hidden" name="postal_code" id="postal_code">
                <input type="hidden" name="latitude" id="latitude" value="">
                <input type="hidden" name="longitude" id="longitude" value="">
                <span class="field-error home_address_error">{{ $errors->first('home_address') }}</span>
            </div>

            <div class="site-field">
                <label for="referral_code">{{ trans('messages.referrals.referral_code') }}</label>
                {!! Form::text('referral_code','', array('class' => 'text-uppercase','placeholder' => trans('messages.referrals.referral_code'),'id' => 'referral_code') ) !!}
                <span class="field-error referral_code_error">{{ $errors->first('referral_code') }}</span>
            </div>

            <input type="hidden" name="step" value="basics">
            <input type="hidden" name="code" id="code" />
            @php $submit_method = site_settings('otp_verification') ? 'send_otp' : 'check_otp'; @endphp

            <button name="step" value="basics" class="site-btn site-btn--gold" id="submit-btn" ng-click="showPopup('{{ $submit_method }}');" type="button">
                {{ trans('messages.user.submit') }}
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
            <p class="auth-terms">{{ trans('messages.user.proceed') }} {{ $site_name }} {{ trans('messages.user.contact') }}</p>
            <p class="auth-switch"><a href="{{ url('signin_driver') }}">{{ trans('messages.ride.already_have_account') }}</a></p>
            {{ Form::close() }}
        </div>
    </section>

@endsection
