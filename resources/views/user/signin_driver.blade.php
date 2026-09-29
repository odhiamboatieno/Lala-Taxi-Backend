@extends('marketing.auth-layout')
@section('title', trans('messages.header.signin') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper" ng-controller="user">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/driver-window.webp') }}" alt="{{ $site_name }}" width="1200" height="923" fetchpriority="high">
            <h2>{{ trans('messages.user.need_partner') }}</h2>
        </div>

        <div class="auth-form">
            <h1>{{ trans('messages.header.signin') }}</h1>
            <p class="site-lead">{{ trans('messages.user.email_address') }}</p>

            @if ($errors->any())
                <div class="site-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" novalidate>
                <input type="hidden" name="user_type" value="Driver" id="user_type">

                <div class="site-field email_phone-sec">
                    <label for="email_phone">{{ trans('messages.user.email_address') }}</label>
                    <div class="site-field-row">
                        <select name="country_code" id="phone_country" aria-label="{{ trans('messages.profile.country') }}">
                            @foreach($country as $key => $value)
                                <option value="{{ $value->phone_code }}" data-value="+{{ $value->phone_code }}" data-id="{{ $value->id }}" {{ (canDisplayCredentials() && $value->id == 227) ? 'selected' : '' }}>{{ $value->long_name }}</option>
                            @endforeach
                        </select>
                        {!! Form::hidden('country_id', old('country_id'), array('id' => 'country_id')) !!}
                        <input class="site-field-grow" id="email_phone" name="textInputValue" type="text"
                               placeholder="{{ trans('messages.user.email_address') }}"
                               value="{{ canDisplayCredentials() ? '98765432101' : old('textInputValue') }}"
                               autocomplete="username" autocorrect="off" autocapitalize="off"
                               ng-init="invalid_email = '{{ trans('messages.user.email_address') }}';">
                    </div>
                    <span class="field-error signin-email-error"><span class="email-error" id="email-error"></span></span>
                </div>

                <div class="password-sec hide">
                    <h3 class="email_or_phone"></h3>
                    <div class="site-field">
                        <label for="password">{{ trans('messages.user.paswrd') }}</label>
                        <input id="password" class="password_btn" name="password" type="password"
                               placeholder="{{ trans('messages.user.paswrd') }}"
                               value="{{ canDisplayCredentials() ? '123456' : '' }}" autocomplete="current-password">
                        <span class="field-error signin-email-error"><span class="email-error"></span></span>
                    </div>
                </div>

                <button class="site-btn site-btn--gold email_phone-sec-1 singin_rider" data-type="email" type="button">
                    {{ trans('messages.user.next') }}
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <div class="auth-options" id="sign-up-link-only">
                <a href="{{ url('signup_driver') }}">{{ trans('messages.home.siginup') }}</a>
                <a href="{{ url('forgot_password_driver') }}" class="password-sec hide">{{ trans('messages.user.forgot_paswrd') }}</a>
            </div>
        </div>
    </section>
</div>
@endsection
