@extends('corporate.portal.auth-layout')
@section('title', __('corporate.portal_recover'))
@section('auth-content')
<h1>{{ __('corporate.portal_recover') }}</h1>
<p>{{ __('corporate.portal_recover_hint') }}</p>
<form class="portal-form" method="POST" action="{{ route('corporate.portal.password.email') }}">
    @csrf
    <label for="email">{{ __('corporate.portal_email') }}<input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255"></label>
    <button class="portal-button portal-button--gold" type="submit">{{ __('corporate.portal_recover') }}<i data-lucide="arrow-right" aria-hidden="true"></i></button>
    <a class="portal-link" href="{{ route('corporate.portal.login') }}"><i data-lucide="arrow-left" aria-hidden="true"></i>{{ __('corporate.portal_sign_in') }}</a>
</form>
@endsection
