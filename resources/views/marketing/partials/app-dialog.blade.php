@php
    $downloadLinks = $app_links ?? collect();
    $riderStores = [__('website.store_apple') => optional($downloadLinks->get(0))->value, __('website.store_google') => optional($downloadLinks->get(2))->value];
    $riderStores = array_filter($riderStores, fn($link) => is_string($link) && preg_match('~^https://~i', $link));
@endphp
<dialog class="app-dialog" id="app-download" aria-labelledby="app-download-title">
    <form method="dialog"><button class="app-dialog__close" aria-label="{{ __('website.close_download') }}"><i data-lucide="x" aria-hidden="true"></i></button></form>
    <h2 class="preserve-lines" id="app-download-title">{{ __('website.download_heading') }}</h2>
    <p>{{ __('website.'.(count($riderStores) ? 'download_copy' : 'download_unavailable')) }}</p>
    <div class="store-links">@foreach($riderStores as $store => $link)<a class="site-btn" href="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $store }} <i data-lucide="external-link" aria-hidden="true"></i></a>@endforeach<a class="site-btn site-btn--outline" href="{{ url('signup_rider') }}">{{ __('website.create_rider_account') }}</a></div>
</dialog>
