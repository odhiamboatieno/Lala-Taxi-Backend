@extends('marketing.auth-layout')
@section('title', trans('messages.header.signin') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper" ng-controller="user">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/landing-hero.webp') }}" alt="{{ $site_name }}" width="1100" height="1239" fetchpriority="high">
            <h2>{{ trans('messages.user.safe_reliable') }}</h2>
        </div>

        <div class="auth-form">
            <h1>{{ trans('messages.header.signin') }}</h1>
            <p class="site-lead">{{ trans('messages.user.email_mobile') }}</p>

            @if ($errors->any())
                <div class="site-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" novalidate>
                <input type="hidden" name="user_type" value="Rider" id="user_type">

                <div class="site-field email_phone-sec">
                    <label for="email_phone">{{ trans('messages.user.email_mobile') }}</label>
                    <div class="site-field-row">
                        <select name="country_code" id="phone_country" aria-label="{{ trans('messages.profile.country') }}">
                            @foreach($country as $key => $value)
                                <option value="{{ $value->phone_code }}" data-value="+{{ $value->phone_code }}" data-id="{{ $value->id }}" {{ (canDisplayCredentials() && $value->id == 227) ? 'selected' : '' }}>{{ $value->long_name }}</option>
                            @endforeach
                        </select>
                        {!! Form::hidden('country_id', old('country_id'), array('id' => 'country_id')) !!}
                        <input class="site-field-grow" id="email_phone" name="textInputValue" type="text"
                               placeholder="{{ trans('messages.user.email_mobile') }}"
                               value="{{ canDisplayCredentials() ? '98765432106' : old('textInputValue') }}"
                               autocomplete="username" autocorrect="off" autocapitalize="off"
                               ng-init="invalid_email = '{{ trans('messages.user.email_mobile') }}';">
                    </div>
                    <span class="field-error email-error"></span>
                </div>

                <div class="password-sec hide">
                    <h3 class="email_or_phone"></h3>
                    <div class="site-field">
                        <label for="password">{{ trans('messages.user.paswrd') }}</label>
                        <input id="password" class="password_btn" name="password" type="password"
                               placeholder="{{ trans('messages.user.paswrd') }}"
                               value="{{ canDisplayCredentials() ? '123456' : '' }}" autocomplete="current-password">
                        <span class="field-error email-error"></span>
                    </div>
                </div>

                <button class="site-btn site-btn--gold email_phone-sec-1 singin_rider" data-type="email" type="button">
                    {{ trans('messages.user.next') }}
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <div class="auth-options" id="sign-up-link-only">
                <a href="{{ url('signup_rider') }}">{{ trans('messages.home.siginup') }}</a>
                <a href="{{ url('forgot_password_rider') }}" class="password-sec hide">{{ trans('messages.user.forgot_paswrd') }}</a>
            </div>

            @php
                $social_logins = array_filter(explode(',', site_settings('social_logins')));
            @endphp
            @if (count($social_logins) > 0)
                <div class="auth-options" style="justify-content:center;">@lang('messages.home.or')</div>
                <div class="social-login email_phone-sec">
                    @if (in_array('apple', $social_logins))
                        <a href="{{ getAppleLoginUrl() }}" id="apple_login" class="site-btn site-btn--outline auth-social">
                            <i data-lucide="apple" aria-hidden="true"></i>
                            <span>@lang('messages.user.continue_with_apple')</span>
                        </a>
                    @endif
                    @if (in_array('facebook', $social_logins))
                        <a href="{{ url('facebook_login') }}" class="site-btn site-btn--outline auth-social btn-facebook" data-facebook-login>
                            <i data-lucide="facebook" aria-hidden="true"></i>
                            <span>@lang('messages.user.continue_with')</span>
                        </a>
                    @endif
                    @if (in_array('google', $social_logins))
                        <a href="javascript:;" id="google_login" class="site-btn site-btn--outline auth-social google_login g-signin2" data-google-login>
                            <i data-lucide="chrome" aria-hidden="true"></i>
                            <span>@lang('messages.user.continue_with_google')</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
