@extends('marketing.layout')
@section('title', __('website.company_login_title'))
@section('main')
<section class="auth-grid">
    <div class="auth-photo"><img src="{{ asset('images/marketing/corporate-hero.webp') }}" alt="" width="1604" height="980" fetchpriority="high"><h2 class="preserve-lines">{{ __('website.company_login_photo_heading') }}</h2></div>
    <div class="auth-form">
        <p class="site-eyebrow">{{ __('website.company_signin') }}</p><h1>{{ __('website.company_login_heading') }}</h1><p>{{ __('website.company_login_copy') }}</p>
        @if(Session::has('message'))<div class="site-alert" role="status">{{ Session::get('message') }}</div>@endif
        <form method="POST" action="{{ url('admin/authenticate') }}">
            @csrf
            <input type="hidden" name="user_type" value="Company">
            <div class="site-field"><label for="company-username">{{ __('website.username') }}</label><input id="company-username" type="text" name="username" value="{{ old('username', old('email')) }}" autocomplete="username" required aria-invalid="{{ $errors->has('username') || $errors->has('email') ? 'true' : 'false' }}" aria-describedby="company-username-error"><span class="field-error" id="company-username-error">{{ $errors->first('username') ?: $errors->first('email') }}</span></div>
            <div class="site-field"><label for="company-password">{{ __('website.password') }}</label><input id="company-password" type="password" name="password" autocomplete="current-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" aria-describedby="company-password-error"><span class="field-error" id="company-password-error">{{ $errors->first('password') }}</span></div>
            <div class="auth-options"><label><input type="checkbox" name="remember" value="1">{{ __('website.remember') }}</label><a href="{{ url('forgot_password_company') }}">{{ __('website.forgot_password') }}</a></div>
            <button type="submit" class="site-btn site-btn--gold">{{ __('website.sign_in') }}</button>
        </form>
        <p>{{ __('website.new_company') }} <a class="site-link" href="{{ url('signup_company') }}">{{ __('website.register_company') }}</a></p>
    </div>
</section>
@endsection
