@extends('marketing.layout')
@section('title', 'LalaTaxi — Good days start with a great ride')
@section('main')
<section class="split-hero">
    <div class="split-hero__copy">
        <p class="site-eyebrow">Made for your everyday</p>
        <h1>Good days<br>start with<br>a great ride.</h1>
        <p class="site-lead">Around the corner or across the city. Wherever life takes you, go with LalaTaxi.</p>
        <div class="site-actions"><a class="site-btn" href="{{ url('app/rider') }}" data-app-open>Get the app <i data-lucide="arrow-right" aria-hidden="true"></i></a><a class="site-link" href="{{ url('drive') }}">Become a driver</a></div>
    </div>
    <img class="split-hero__image" src="{{ asset('images/marketing/landing-hero.webp') }}" alt="A passenger and LalaTaxi driver setting off in Nairobi" width="1182" height="1330" fetchpriority="high">
</section>
<section class="service-band">
    <div class="site-container"><p class="site-eyebrow">A ride for every plan</p><h2>Three easy ways to ride</h2>
        <div class="service-grid">
            <article class="service-item"><i data-lucide="car-front" aria-hidden="true"></i><h3><a href="{{ url('ride') }}">Everyday rides</a></h3><p>Get around for work, errands or a night out.</p></article>
            <article class="service-item"><i data-lucide="plane" aria-hidden="true"></i><h3><a href="{{ url('ride') }}#airport">Airport transfers</a></h3><p>From your doorstep to departures. And home again.</p></article>
            <article class="service-item"><i data-lucide="briefcase-business" aria-hidden="true"></i><h3><a href="{{ url('business') }}">Business travel</a></h3><p>A better way to get to the places work takes you.</p></article>
        </div>
    </div>
</section>
<section class="site-section"><div class="site-container feature-split">
    <img class="feature-split__image" src="{{ asset('images/marketing/driver-portrait.webp') }}" alt="A LalaTaxi driver beside his car with Nairobi in the background" width="1200" height="900" loading="lazy">
    <div class="feature-split__copy"><p class="site-eyebrow">Drive a brighter tomorrow</p><h2>Your time.<br>Your earnings.</h2><p>Join LalaTaxi and drive on your schedule.</p><div class="site-actions"><a class="site-btn site-btn--gold" href="{{ url('signup_driver') }}">Start driving <i data-lucide="arrow-right" aria-hidden="true"></i></a></div></div>
</div></section>
@include('marketing.partials.city')
@endsection
