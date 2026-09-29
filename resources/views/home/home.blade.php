@extends('marketing.layout')
@section('title', __('website.home_title'))
@section('main')
<section class="photo-hero home-hero">
    <img class="photo-hero__image" src="{{ asset('images/marketing/corporate-hero.webp') }}" alt="{{ __('website.home_image_alt') }}" width="1604" height="980" fetchpriority="high">
    <div class="site-container photo-hero__copy">
        <h1 class="preserve-lines">{{ __('website.home_heading') }}</h1>
        <p class="site-lead preserve-lines">{{ __('website.home_description') }}</p>
        <div class="site-actions">
            <a class="site-btn site-btn--gold" href="{{ url('app/rider') }}" data-app-open>{{ __('website.get_app') }}</a>
            <a class="site-link" href="{{ url('drive') }}">{{ __('website.drive_with_lala') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
<section class="site-section home-intro">
    <div class="site-container intro-row">
        <h2 class="preserve-lines">{{ __('website.home_services_heading') }}</h2>
        <div class="service-grid">
            <article class="service-item"><i data-lucide="car-front" aria-hidden="true"></i><h3>{{ __('website.ride_with_lala') }}</h3><p>{{ __('website.home_rides_copy') }}</p><a class="service-arrow" href="{{ url('ride') }}" aria-label="{{ __('website.explore_rides') }}"><i data-lucide="arrow-right" aria-hidden="true"></i></a></article>
            <article class="service-item"><i data-lucide="briefcase-business" aria-hidden="true"></i><h3>{{ __('website.lala_business') }}</h3><p>{{ __('website.home_business_copy') }}</p><a class="service-arrow" href="{{ url('business') }}" aria-label="{{ __('website.lala_business') }}"><i data-lucide="arrow-right" aria-hidden="true"></i></a></article>
        </div>
    </div>
</section>
<section class="driver-banner gold-section">
    <div class="driver-banner__copy">
        <p class="site-eyebrow">{{ __('website.home_driver_eyebrow') }}</p>
        <h2 class="preserve-lines">{{ __('website.home_driver_heading') }}</h2>
        <p class="site-lead preserve-lines">{{ __('website.home_driver_copy') }}</p>
        <div class="site-actions"><a class="site-btn" href="{{ url('signup_driver') }}">{{ __('website.start_driving') }}</a></div>
    </div>
    <img src="{{ asset('images/marketing/driver-window.webp') }}" alt="{{ __('website.home_driver_alt') }}" width="1300" height="1000" loading="lazy">
</section>
@include('marketing.partials.city')
@endsection
