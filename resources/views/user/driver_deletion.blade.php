@extends('marketing.auth-layout')
@section('title', trans('messages.header.authorize_deletion') . ' · ' . $site_name)

@section('main')
<div class="ux-content signin" ng-controller="user">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/driver-portrait.webp') }}" alt="{{ $site_name }}" width="1200" height="1500" fetchpriority="high">
            <h2>{{trans('messages.header.authorize_deletion')}}</h2>
        </div>

        <div class="auth-form">
            <p class="site-eyebrow">{{ trans('messages.profile.driver') }}</p>
            <h1>{{trans('messages.header.authorize_deletion')}}</h1>
            <p>{{trans('messages.profile.delete_driver')}}</p>

            <form class="push--top-small forward" method="POST" data-reactid="13">
                <input type="hidden" name="user_type" value="Driver" id="users_type">
                <div data-reactid="15" class="site-field email_phone-sec">
                    <label for="email_phone">{{ trans('messages.user.email_address') }}</label>
                    <div class="_style_CZTQ8" data-reactid="20" style="display:flex;gap:10px;">
                        <select name="country_code" tabindex="-1" id="phone_country" class="text-select input-group-addon" data-reactid="21" style="max-width:42%;">
                            @foreach($country as $key => $value)
                            <option value="{{ $value->phone_code }}" data-value="+{{ $value->phone_code}}" data-id="{{ $value->id }}" {{ (canDisplayCredentials() && $value->id==227) ?' selected':''}}>{{ $value->long_name }}</option>
                            @endforeach
                        </select>
                        <input class="text-phone input-group-addon" id="email_phone" placeholder="{{trans('messages.user.email_address')}}" autocorrect="off" autocapitalize="off" name="textInputValue" data-reactid="21" type="text" value="{{ canDisplayCredentials() ? '98765432101':'' }}" style="flex:1;">
                    </div>
                    {!! Form::hidden('country_id', old('country_id'), array('id'=>'country_id')) !!}
                    <div class="_style_CZTQ8 signin-email-error">
                        <span class="field-error text-danger email-error" id="email-error"></span>
                    </div>
                </div>

                <h3 class="email_or_phone password-sec hide text-center" style="margin-top: 0px;margin-bottom: 20px;"></h3>
                <div data-reactid="15" class="site-field password-sec hide">
                    <label for="password">{{trans('messages.user.paswrd')}}</label>
                    <div class="_style_CZTQ8" data-reactid="20">
                        <input class="text-input input-group-addon password_btn" id="password" placeholder="{{trans('messages.user.paswrd')}}" autocorrect="off" autocapitalize="off" name="password" data-reactid="21" type="password" value="{{ canDisplayCredentials() ? '123456':'' }}">
                    </div>
                    <div class="_style_CZTQ8 signin-email-error">
                        <span class="field-error text-danger email-error"></span>
                    </div>
                </div>

                <button class="site-btn site-btn--gold btn btn--arrow btn--full blue-signin-btn singin_rider email_phone-sec-1" data-reactid="22" data-type='email'>
                    <span class="push-small--right" data-reactid="23">{{trans('messages.user.next')}}</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .hide { display: none !important; }
</style>
@endpush
