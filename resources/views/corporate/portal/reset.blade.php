@extends('corporate.portal.auth-layout')
@section('title', __('corporate.portal_reset'))
@section('auth-content')
<h1>{{ __('corporate.portal_reset') }}</h1>
<p>{{ __('corporate.portal_password_hint') }}</p>
<form class="portal-form" method="POST" action="{{ route('corporate.portal.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label for="email">{{ __('corporate.portal_email') }}<input id="email" type="email" class="form-control" name="email" value="{{ old('email', $email) }}" required maxlength="255" autocomplete="username"></label>
    @include('corporate.portal.password-field', ['passwordName'=>'password', 'newPassword'=>true])
    @include('corporate.portal.password-field', ['passwordName'=>'password_confirmation', 'newPassword'=>true])
    <button type="submit" class="portal-button portal-button--gold">{{ __('corporate.portal_reset') }}<i data-lucide="arrow-right" aria-hidden="true"></i></button>
    <a class="portal-link" href="{{ route('corporate.portal.login') }}"><i data-lucide="arrow-left" aria-hidden="true"></i>{{ __('corporate.portal_sign_in') }}</a>
</form>
@endsection
