@extends('marketing.layout')
@section('title', __('website.safety_title'))
@section('description', __('website.safety_description'))
@section('main')
<section class="center-hero"><h1 class="preserve-lines">{{ __('website.safety_heading') }}</h1><p class="site-lead preserve-lines">{{ __('website.safety_copy') }}</p><div class="site-actions"><a class="site-btn site-btn--gold" href="#safety-tools">{{ __('website.explore_safety') }} <i data-lucide="arrow-down" aria-hidden="true"></i></a><a class="site-link" href="{{ url('help') }}">{{ __('website.get_help') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a></div></section>
<img class="wide-photo" src="{{ asset('images/marketing/safety-hero.webp') }}" alt="{{ __('website.safety_photo_alt') }}" width="1800" height="1050" fetchpriority="high">
<section class="site-section" id="safety-tools"><div class="site-container"><div class="section-heading"><h2>{{ __('website.safety_tools_heading') }}</h2></div><div class="editorial-grid">
    @foreach(['know' => ['driver-portrait', 'driver_photo_alt'], 'share' => ['safety-hero', 'safety_photo_alt'], 'help' => ['business-travel', 'business_photo_alt']] as $item => [$photo, $alt])
    <article class="editorial-card"><img src="{{ asset('images/marketing/'.$photo.'.webp') }}" alt="{{ __('website.'.$alt) }}" width="1200" height="900" loading="lazy"><h3>{{ __('website.safety_'.$item.'_title') }}</h3><p>{{ __('website.safety_'.$item.'_copy') }}</p>@if($item === 'help')<a class="site-link" href="{{ url('help') }}">{{ __('website.help_centre') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>@endif</article>
    @endforeach
</div></div></section>
<section class="site-section gold-section"><div class="site-container"><div class="section-heading"><h2 class="preserve-lines">{{ __('website.safety_faq_heading') }}</h2></div><div class="faq">
    @foreach(['before', 'during', 'urgent'] as $item)<details><summary>{{ __('website.safety_'.$item.'_title') }}</summary><p>{{ __('website.safety_'.$item.'_copy') }}</p></details>@endforeach
</div></div></section>
@include('marketing.partials.city', ['showCityAction' => true])
@endsection
