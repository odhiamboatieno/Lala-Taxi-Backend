@extends('corporate.portal.layout')
@section('content')
<div class="portal-auth-shell">
    <aside class="portal-auth-visual" aria-label="{{ __('corporate.portal_brand_story') }}">
        <img src="{{ asset('images/marketing/corporate-hero.webp') }}" alt="{{ __('website.business_photo_alt') }}" width="1400" height="855" fetchpriority="high">
        <div class="portal-auth-caption"><p class="portal-eyebrow">{{ __('website.lala_business') }}</p><h2>{{ __('corporate.portal_brand_heading') }}</h2><p>{{ __('corporate.portal_brand_copy') }}</p></div>
    </aside>
    <section class="portal-auth">
        <span class="portal-icon portal-icon--gold"><i data-lucide="briefcase-business" aria-hidden="true"></i></span>
        @yield('auth-content')
        <div class="portal-help portal-auth-note"><i data-lucide="lock-keyhole" aria-hidden="true"></i><p>{{ __('corporate.portal_secure_access') }}</p></div>
    </section>
</div>
@endsection
