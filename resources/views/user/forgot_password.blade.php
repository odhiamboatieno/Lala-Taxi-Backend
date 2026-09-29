@extends('marketing.auth-layout')
@section('title', trans('messages.user.forgot_paswrd') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/nairobi-skyline.webp') }}" alt="{{ $site_name }}" width="1600" height="520" fetchpriority="high">
            <h2>{{ trans('messages.user.forgot_paswrd') }}</h2>
        </div>

        <div class="auth-form">
            <p class="site-eyebrow">{{ trans('messages.header.signin') }}</p>
            <h1>{{ trans('messages.user.forgot_paswrd') }}</h1>
            <p class="site-lead">{{ __('website.forgot_password_copy') }}</p>

            @if ($errors->any())
                <div class="site-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            {!! Form::open(['action' => 'UserController@forgotpassword']) !!}
            <input type="hidden" name="user_type" value="{{ Route::current()->uri() == 'forgot_password_rider' ? 'Rider' : (Route::current()->uri() == 'forgot_password_driver' ? 'Driver' : 'Company') }}">

            <div class="site-field">
                <label for="forgot-email">{{ trans('messages.user.email') }}</label>
                {!! Form::email('email', old('email'), ['id' => 'forgot-email', 'placeholder' => trans('messages.user.email'), 'autocomplete' => 'email', 'autocorrect' => 'off', 'autocapitalize' => 'off']) !!}
                <span class="field-error">{{ $errors->first('email') }}</span>
            </div>

            <button class="site-btn site-btn--gold" type="submit">
                {{ trans('messages.user.next') }}
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
            {!! Form::close() !!}

            <div class="auth-options">
                <a class="site-link" href="{{ Route::current()->uri() == 'forgot_password_rider' ? url('signup_rider') : url('signup_driver') }}">{{ trans('messages.home.siginup') }}</a>
            </div>
        </div>
    </section>
</div>
@endsection
