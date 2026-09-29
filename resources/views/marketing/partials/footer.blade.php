<footer class="site-footer">
    <div class="site-container">
        <div class="footer-grid">
            <div class="footer-brand"><a class="site-brand" href="{{ url('/') }}"><img src="{{ asset('images/marketing/lala-taxi-logo.webp') }}" alt="{{ $site_name }}" width="144" height="48" loading="lazy"></a><p class="preserve-lines">{{ __('website.footer_tagline') }}</p></div>
            <div class="footer-column"><h2>{{ __('website.footer_go') }}</h2><ul><li><a href="{{ url('ride') }}">{{ __('website.ride_with_us') }}</a></li><li><a href="{{ url('business') }}">{{ __('website.business_travel') }}</a></li><li><a href="{{ url('safety') }}">{{ __('website.safety') }}</a></li><li><a href="{{ url('app/rider') }}" data-app-open>{{ __('website.get_app') }}</a></li></ul></div>
            <div class="footer-column"><h2>{{ __('website.footer_earn') }}</h2><ul><li><a href="{{ url('drive') }}">{{ __('website.become_driver') }}</a></li><li><a href="{{ url('requirements') }}">{{ __('website.driver_requirements') }}</a></li><li><a href="{{ url('business/fleets') }}">{{ __('website.fleet_partners') }}</a></li><li><a href="{{ url('signin_company') }}">{{ __('website.company_signin') }}</a></li></ul></div>
            <div class="footer-column"><h2>{{ __('website.footer_help') }}</h2><ul><li><a href="{{ url('help') }}">{{ __('website.help_support') }}</a></li><li><a href="{{ url('drive_safety') }}">{{ __('website.driver_safety') }}</a></li><li><a href="{{ url('signin') }}">{{ __('website.your_account') }}</a></li></ul></div>
        </div>
        <div class="footer-bottom"><span>{{ __('website.copyright', ['year' => date('Y'), 'brand' => $site_name]) }}</span><div class="footer-legal">@foreach(($company_pages ?? collect()) as $page)<a href="{{ url($page->url) }}">{{ $page->name }}</a>@endforeach<span>{{ __('website.location_language', ['language' => ($language ?? collect())->get(app()->getLocale(), app()->getLocale())]) }}</span></div></div>
    </div>
</footer>
