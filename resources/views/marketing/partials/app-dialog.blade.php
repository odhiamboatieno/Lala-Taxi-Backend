@php
    $downloadLinks = $app_links ?? collect();
    $riderStores = ['App Store' => optional($downloadLinks->get(0))->value, 'Google Play' => optional($downloadLinks->get(2))->value];
    $riderStores = array_filter($riderStores, fn($link) => is_string($link) && preg_match('~^https://~i', $link));
@endphp
<dialog class="app-dialog" id="app-download" aria-labelledby="app-download-title">
    <form method="dialog"><button class="app-dialog__close" aria-label="Close download options"><i data-lucide="x" aria-hidden="true"></i></button></form>
    <h2 id="app-download-title">Your next ride<br>starts here.</h2>
    <p>@if(count($riderStores))Choose your store to get the LalaTaxi passenger app.@else App download links aren’t available just yet. You can still create your rider account on the website.@endif</p>
    <div class="store-links">@foreach($riderStores as $store => $link)<a class="site-btn" href="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $store }} <i data-lucide="external-link" aria-hidden="true"></i></a>@endforeach<a class="site-btn site-btn--outline" href="{{ url('signup_rider') }}">Create a rider account</a></div>
</dialog>
