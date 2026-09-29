<footer class="site-footer">
    <div class="site-container">
        <div class="footer-grid">
            <div class="footer-brand"><a class="site-brand" href="{{ url('/') }}"><img src="{{ asset('images/marketing/lala-taxi-logo.webp') }}" alt="LalaTaxi" width="144" height="48" loading="lazy"></a><p>People. Places. Possibilities.<br>Move together with LalaTaxi.</p></div>
            <div class="footer-column"><h2>Go with Lala</h2><ul><li><a href="{{ url('ride') }}">Ride with us</a></li><li><a href="{{ url('business') }}">Business travel</a></li><li><a href="{{ url('safety') }}">Safety</a></li><li><a href="{{ url('app/rider') }}" data-app-open>Get the app</a></li></ul></div>
            <div class="footer-column"><h2>Earn with Lala</h2><ul><li><a href="{{ url('drive') }}">Become a driver</a></li><li><a href="{{ url('requirements') }}">Driver requirements</a></li><li><a href="{{ url('business/fleets') }}">Fleet partners</a></li><li><a href="{{ url('signin_company') }}">Company sign in</a></li></ul></div>
            <div class="footer-column"><h2>Here to help</h2><ul><li><a href="{{ url('help') }}">Help & support</a></li><li><a href="{{ url('drive_safety') }}">Driver safety</a></li><li><a href="{{ url('signin') }}">Your account</a></li></ul></div>
        </div>
        <div class="footer-bottom"><span>© {{ date('Y') }} {{ $site_name ?? 'LalaTaxi' }}. All rights reserved.</span><div class="footer-legal">@foreach(($company_pages ?? collect()) as $page)<a href="{{ url($page->url) }}">{{ $page->name }}</a>@endforeach<span>Kenya · English</span></div></div>
    </div>
</footer>
