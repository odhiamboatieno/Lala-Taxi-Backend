@extends('corporate.portal.auth-layout')
@section('title', __('corporate.portal_sign_in'))
@section('auth-content')
<h1>{{ __('corporate.portal_intro') }}</h1>
<p>{{ __('corporate.portal_login_hint') }}</p>
<form class="portal-form" method="POST" action="{{ route('corporate.portal.login.submit') }}">
    @csrf
    <label for="email">{{ __('corporate.portal_email') }}<input id="email" class="form-control" type="email" name="email" autocomplete="username" value="{{ old('email') }}" required maxlength="255"></label>
    @include('corporate.portal.password-field', ['passwordName'=>'password', 'newPassword'=>false])
    <button class="portal-button portal-button--gold" type="submit">{{ __('corporate.portal_sign_in') }}<i data-lucide="arrow-right" aria-hidden="true"></i></button>
    <a class="portal-link" href="{{ route('corporate.portal.password.request') }}">{{ __('corporate.portal_forgot') }}</a>
</form>
@endsection
