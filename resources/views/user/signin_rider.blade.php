@extends('marketing.auth-layout')
@section('title', trans('messages.header.signin') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper" class="sigin-riders" ng-controller="user">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/driver-window.webp') }}" alt="{{ __('messages.user.safe_reliable') }}" width="1200" height="1500" fetchpriority="high">
            <h2>{{ trans('messages.header.signin') }}</h2>
        </div>

        <div class="auth-form">
            <p class="site-eyebrow">{{ __('website.account_rider') }}</p>
            <h1>{{ trans('messages.header.signin') }}</h1>
            <p>{{ trans('messages.user.safe_reliable') }}</p>

            <form class="push--top-small forward" method="POST" data-reactid="13">
                <input type="hidden" name="user_type" value="Rider" id="user_type">
                <div data-reactid="15" class="site-field email_phone-sec">
                    <label for="email_phone">{{ trans('messages.user.email_mobile') }}</label>
                    <div class="_style_CZTQ8 input-group" data-reactid="20" style="display:flex;gap:10px;">
                        <select name="country_code" tabindex="-1" id="phone_country" class="text-select input-group-addon" data-reactid="21" style="max-width:42%;">
                            @foreach($country as $key => $value)
                            <option value="{{ $value->phone_code }}" data-value="+{{ $value->phone_code}}" data-id="{{ $value->id }}" {{ (canDisplayCredentials() && $value->id==227) ?' selected':''}}>{{ $value->long_name }}</option>
                            @endforeach
                        </select>
                        <input class="text-phone input-group-addon" id="email_phone" placeholder="{{trans('messages.user.email_mobile')}}" autocorrect="off" autocapitalize="off" name="textInputValue" data-reactid="21" type="text" value="{{ canDisplayCredentials() ? '98765432106':'' }}" ng-init="invalid_email= '{{trans('messages.user.email_mobile')}}';" style="flex:1;">
                    </div>
                    {!! Form::hidden('country_id', old('country_id'), array('id'=>'country_id')) !!}
                    <span class="field-error text-danger email-error"></span>
                </div>

                <h3 class="email_or_phone password-sec hide text-center" style="margin-top: 0px;margin-bottom: 20px;"></h3>
                <div data-reactid="15" class="site-field password-sec hide">
                    <label for="password">{{trans('messages.user.paswrd')}}</label>
                    <div class="_style_CZTQ8" data-reactid="20">
                        <input class="text-input input-group-addon password_btn" id="password" placeholder="{{trans('messages.user.paswrd')}}" autocorrect="off" autocapitalize="off" name="password" data-reactid="21" type="password" value="{{ canDisplayCredentials() ? '123456':'' }}">
                    </div>
                    <span class="field-error text-danger email-error"></span>
                </div>

                <button class="site-btn site-btn--gold btn btn--arrow btn--full blue-signin-btn singin_rider email_phone-sec-1" data-reactid="22" data-type='email'>
                    <span class="push-small--right" data-reactid="23">{{trans('messages.user.next')}}</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <div class="small push-small--bottom push-small--top" id="sign-up-link-only" data-reactid="26">
                <p class="display--inline email_phone-sec" data-reactid="27">{{trans('messages.user.no_account')}} <a class="site-link" href="{{ url('signup_rider')}}">{{trans('messages.home.siginup')}}</a></p>
                <p class="forgot password-sec hide">
                    <a href="{{ url('forgot_password_rider')}}" class="forgot-password">{{trans('messages.user.forgot_paswrd')}}</a>
                </p>
            </div>

            @php
            $social_logins = site_settings('social_logins');
            $social_logins = explode(',',$social_logins);
            @endphp
            @if(count(array_filter($social_logins))>0)
            <div data-reactid="28" class="email_phone-sec social-login">
                <h5 class="push-small--top or-text" data-reactid="29">
                    @lang('messages.home.or')
                </h5>

                @if(in_array('apple',$social_logins))
                <a href="{{ getAppleLoginUrl() }}" id="apple_login" class="site-btn site-btn--outline btn btn--arrow push-small--bottom black-btn">
                    <i data-lucide="apple" aria-hidden="true"></i>
                    <span> @lang('messages.user.continue_with_apple') </span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
                @endif

                @if(in_array('facebook',$social_logins))
                <a href="{{ url('facebook_login') }}" class="site-btn site-btn--outline btn btn--arrow push-small--bottom violet-btn btn-facebook">
                    <i data-lucide="facebook" aria-hidden="true"></i>
                    <span class="push-small--right">@lang('messages.user.continue_with')</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
                @endif

                @if(in_array('google',$social_logins))
                <a href="javascript:;" id="google_login" class="site-btn site-btn--outline google_login g-signin2 btn btn--arrow push-small--bottom white-btn google-btn">
                    <i data-lucide="chrome" aria-hidden="true"></i>
                    <span> @lang('messages.user.continue_with_google') </span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
                @endif
            </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .hide { display: none !important; }
    .social-login .site-btn { width: 100%; margin-bottom: 12px; }
    .or-text { text-align: center; margin-bottom: 16px; color: var(--site-muted); }
</style>
@endpush
