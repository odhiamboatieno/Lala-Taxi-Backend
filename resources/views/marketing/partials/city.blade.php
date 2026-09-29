<section class="city-section">
    <img class="city-section__image" src="{{ asset('images/marketing/nairobi-skyline.webp') }}" alt="" width="2048" height="666" loading="lazy">
    <div><h2 class="preserve-lines">{{ __('website.city_heading') }}</h2><p>{{ __('website.city_copy') }}</p>@if($showCityAction ?? false)<div class="site-actions"><a class="site-btn" href="{{ url('app/rider') }}" data-app-open>{{ __('website.city_cta') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a></div>@endif</div>
</section>
