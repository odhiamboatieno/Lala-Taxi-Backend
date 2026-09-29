@extends('marketing.auth-layout')
@section('title', trans('messages.user.reset_paswrd') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper" ng-controller="user">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/nairobi-skyline.webp') }}" alt="{{ $site_name }}" width="1600" height="520" fetchpriority="high">
            <h2>{{ trans('messages.user.reset_paswrd') }}</h2>
        </div>

        <div class="auth-form">
            <p class="site-eyebrow">{{ trans('messages.header.signin') }}</p>
            <h1>{{trans('messages.user.reset_paswrd')}} {{ ucfirst($result->first_name)}}</h1>
            <p class="site-lead">{{ __('website.reset_password_copy') }}</p>

            {!! Form::open(['url' => (Request::path() == 'company/reset_password')?'company/reset_password':'reset_password', 'accept-charset' => 'UTF-8' , 'novalidate' => 'true']) !!}
            <input type="hidden" name="id" value="{{ $result->id }}">
            <div class="site-field">
                <label for="new_password">{{ __('website.new_password') }}</label>
                <input id="new_password" placeholder="{{ __('messages.blade_static.enter_new_password_4cf96dca') }}" autocorrect="off" autocapitalize="off" name="new_password" type="password">
                <span class="field-error">{{ $errors->first('new_password')}}</span>
            </div>
            <div class="site-field">
                <label for="confirm_password">{{ __('website.confirm_password') }}</label>
                <input id="confirm_password" placeholder="{{ __('messages.blade_static.enter_confirm_password_786ddf0f') }}" autocorrect="off" autocapitalize="off" name="confirm_password" type="password">
                <span class="field-error">{{ $errors->first('confirm_password')}}</span>
            </div>
            <button class="site-btn site-btn--gold">
                <span>{{trans('messages.user.next')}}</span>
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
            {!! Form::close() !!}
        </div>
    </section>
</div>
@endsection
