@extends('marketing.auth-layout')
@section('title', __('messages.footer.siginup_ride') . ' · ' . __('website.site_title'))

@section('main')
<div id="app-wrapper" ng-controller="facebook_account_kit">
    @include('user.otp_popup')

    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/landing-hero.webp') }}" alt="{{ __('messages.user.safe_reliable') }}" width="1100" height="1239" fetchpriority="high">
            <h2>{{ __('messages.footer.siginup_ride') }}</h2>
        </div>

        <div class="auth-form">
            <h1>{{ __('messages.footer.siginup_ride') }}</h1>
            <p>{{ __('messages.user.safe_reliable') }}</p>

            @if ($errors->any())
                <div class="site-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            {{ Form::open(array('url' => 'rider_register','id'=>"form")) }}
            {!! Form::hidden('fb_id', @$user['fb_id'], ['id' => 'fb_id']) !!}
            {{ csrf_field() }}
            {!! Form::hidden('request_type', '', ['id' => 'request_type' ]) !!}
            {!! Form::hidden('otp', '', ['id' => 'otp' ]) !!}
            <input type="hidden" name="user_type" value="Rider">
            <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="code" id="code" />

            <div class="site-field">
                <label for="fname">{{ trans('messages.user.firstname') }}</label>
                {!! Form::text('first_name', @$user['first_name'], ['placeholder' => trans('messages.user.firstname'), 'id' => 'fname']) !!}
                <span class="field-error first_name_error">{{ $errors->first('first_name') }}</span>
            </div>

            <div class="site-field">
                <label for="lname">{{ trans('messages.user.lastname') }}</label>
                {!! Form::text('last_name', @$user['last_name'], ['placeholder' => trans('messages.user.lastname'), 'id' => 'lname']) !!}
                <span class="field-error last_name_error">{{ $errors->first('last_name') }}</span>
            </div>

            <div class="site-field" ng-init="old_country_code={{ old('country_code') != null ? old('country_code') : '1' }}">
                <label for="mobile_country">{{ trans('messages.profile.mobile') }}</label>
                <div class="site-field-row">
                    <select name="country_code" id="mobile_country" aria-label="{{ trans('messages.profile.country') }}">
                        @foreach($country as $key => $value)
                            <option value="{{ $value->phone_code }}" {{ ($value->id == (old('country_id') != null ? old('country_id') : '1')) ? 'selected' : '' }} data-value="+{{ $value->phone_code }}" data-id="{{ $value->id }}">{{ $value->long_name }}</option>
                        @endforeach
                    </select>
                    <div class="site-field-grow" ng-init="old_mobile_number='{{ old('mobile_number') != null ? old('mobile_number') : '' }}'">
                        {!! Form::tel('mobile_number', '', ['placeholder' => trans('messages.profile.mobile'), 'id' => 'mobile', 'maxlength' => '15']) !!}
                    </div>
                </div>
                {!! Form::hidden('country_id', old('country_id'), array('id'=>'country_id')) !!}
                <span class="field-error country_code_error">{{ $errors->first('country_code') }}</span>
                <span class="field-error mobile_number_error">{{ $errors->first('mobile_number') }}</span>
                <span class="field-error mobile-text-danger">{{ __('messages.blade_static.mobile_number_is_required_a8d2ac9f') }}</span>
            </div>

            <div class="site-field">
                <label for="gender_options">{{ trans('messages.profile.gender') }}</label>
                <select name="gender" id="gender_options">
                    <option value="" disabled selected hidden>{{ __('messages.driver_dashboard.select').' '.__('messages.profile.gender') }}</option>
                    <option value="1">{{ __('messages.profile.male') }}</option>
                    <option value="2">{{ __('messages.profile.female') }}</option>
                </select>
                <span class="field-error gender_error">{{ $errors->first('gender') }}</span>
            </div>

            <div class="site-field">
                <label for="input-email">{{ trans('messages.user.email') }}</label>
                {!! Form::text('email', '', ['placeholder' => trans('messages.user.email'), 'id' => 'input-email']) !!}
                <span class="field-error email_error">{{ $errors->first('email') }}</span>
            </div>

            <div class="site-field">
                <label for="password">{{ trans('messages.user.paswrd') }}</label>
                {!! Form::password('password', ['placeholder' => trans('messages.user.paswrd'), 'id' => 'password']) !!}
                <span class="field-error password_error">{{ $errors->first('password') }}</span>
            </div>

            <div class="site-field">
                <label for="referral_code">{{ trans('messages.referrals.referral_code') }}</label>
                {!! Form::text('referral_code', '', ['id' => 'referral_code', 'placeholder' => trans('messages.referrals.referral_code')]) !!}
                <span class="field-error referral_code_error">{{ $errors->first('referral_code') }}</span>
            </div>

            <div id="captcha-form-container" style="display: none;">
                <div id="captcha" style="margin-bottom:22px;"></div>
            </div>

            @php $submit_method = site_settings('otp_verification') ? 'send_otp' : 'check_otp'; @endphp

            <button id="submit-btn" ng-click="showPopup('{{ $submit_method }}');" type="button" class="site-btn site-btn--gold">
                {{ trans('messages.home.siginup') }}
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
            {{ Form::close() }}

            <p class="auth-terms">
                {{ trans('messages.user.clicking_terms') }}
                <a href="{{ url('terms_of_service') }}">{{ $site_name }}{{ trans('messages.user.terms') }}</a>
                {{ trans('messages.user.and') }}
                <a href="{{ url('privacy_policy') }}">{{ trans('messages.user.privacy') }}</a>.
            </p>
            <p class="auth-switch">
                <a href="{{ url('signin_rider') }}">{{ trans('messages.ride.already_have_account') }}</a>
            </p>
        </div>
    </section>
</div>
@endsection
