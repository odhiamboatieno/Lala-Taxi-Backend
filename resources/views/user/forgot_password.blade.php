@extends('marketing.auth-layout')
@section('title', trans('messages.user.forgot_paswrd') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper" class="sigin-riders">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/safety-hero.webp') }}" alt="{{ $site_name }}" width="1604" height="980" fetchpriority="high">
            <h2>{{ trans('messages.user.forgot_paswrd') }}</h2>
        </div>

        <div class="auth-form">
            <p class="site-eyebrow">{{ trans('messages.header.signin') }}</p>
            <h1>{{ trans('messages.user.forgot_paswrd') }}</h1>
            <p>{{ __('website.forgot_password_copy') }}</p>

            {!! Form::open(['action' => 'UserController@forgotpassword', 'class' => 'push--top-small forward']) !!}
            <input type="hidden" name="user_type" value="{{ Route::current()->uri() == 'forgot_password_rider' ? 'Rider' : (Route::current()->uri() == 'forgot_password_driver' ? 'Driver' : 'Company')}}">
            <div class="site-field">
                <label for="forgot-email">{{ trans('messages.user.email') }}</label>
                {!! Form::email('email', '', ['class' => 'text-input input-group-addon ae-form-field','placeholder' => 'Enter your email','autocorrect' => 'off','autocapitalize' => 'off', 'id' => 'forgot-email']) !!}
                <span class="field-error text-danger">{{$errors->first('email')}}</span>
            </div>
            <button class="site-btn site-btn--gold btn btn--arrow btn--full blue-signin-btn" data-reactid="22" type="submit">
                <span class="push-small--right" data-reactid="23">{{trans('messages.user.next')}}</span>
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
            {!! Form::close() !!}

            <div class="small push-small--bottom push-small--top" id="sign-up-link-only" data-reactid="26">
                <p class="display--inline email_phone-sec" data-reactid="27">{{trans('messages.user.no_account')}}
                <a class="site-link" href="{{ Route::current()->uri() == 'forgot_password_rider' ? url('signup_rider') : url('signup_driver')}}">{{trans('messages.home.siginup')}}</a></p>
            </div>
        </div>
    </section>
</div>
@endsection
