@extends('marketing.layout')
@section('title', 'Safety with LalaTaxi — More confidence in every journey')
@section('description', 'Check your driver and vehicle, share your trip and keep your trusted contacts close. Explore safety with LalaTaxi.')
@section('main')
<section class="center-hero"><h1>Your ride.<br>Your peace of mind.</h1><p class="site-lead">From the moment you book to the moment you arrive.<br>Simple tools to help you feel more in control.</p><div class="site-actions"><a class="site-btn site-btn--gold" href="#safety-tools">Explore safety <i data-lucide="arrow-down" aria-hidden="true"></i></a><a class="site-link" href="{{ url('help') }}">Get help <i data-lucide="arrow-right" aria-hidden="true"></i></a></div></section>
<img class="wide-photo" src="{{ asset('images/marketing/safety-hero.webp') }}" alt="A passenger checking her phone beside her LalaTaxi vehicle in Nairobi" width="1800" height="1050" fetchpriority="high">
<section class="site-section" id="safety-tools"><div class="site-container"><div class="section-heading"><h2>Confidence at every step.</h2></div><div class="editorial-grid">
    <article class="editorial-card"><img src="{{ asset('images/marketing/driver-portrait.webp') }}" alt="A LalaTaxi driver beside his car" width="1200" height="900" loading="lazy"><h3>Know your ride</h3><p>Match the driver, vehicle and number plate to the details in your app before getting in.</p></article>
    <article class="editorial-card"><img src="{{ asset('images/marketing/safety-hero.webp') }}" alt="A passenger using her phone before a trip" width="1800" height="1050" loading="lazy"><h3>Keep someone close</h3><p>Share your trip with someone you trust and keep your emergency contacts up to date.</p></article>
    <article class="editorial-card"><img src="{{ asset('images/marketing/business-travel.webp') }}" alt="A passenger wearing her seatbelt" width="1200" height="900" loading="lazy"><h3>Help when you need it</h3><p>Use the in-app safety and support tools to report a concern or get help with a trip.</p><a class="site-link" href="{{ url('help') }}">Visit the help centre <i data-lucide="arrow-right" aria-hidden="true"></i></a></article>
</div></div></section>
<section class="site-section gold-section"><div class="site-container"><div class="section-heading"><h2>A little preparation.<br>A better journey.</h2></div><div class="faq">
    <details><summary>Before you get in</summary><p>Wait somewhere well-lit where the driver can stop safely. Check the number plate and driver details in the app. If something doesn’t match, don’t enter the vehicle.</p></details>
    <details><summary>While you’re on the road</summary><p>Wear your seatbelt, follow your trip in the app, and share the journey with a trusted contact. Speak up if you feel uncomfortable.</p></details>
    <details><summary>If you need urgent help</summary><p>If you are in immediate danger, contact local emergency services directly. For non-urgent trip concerns, use in-app support or our <a href="{{ url('help') }}">help centre</a>.</p></details>
</div></div></section>
@include('marketing.partials.city', ['showCityAction' => true])
@endsection
