@php
    $navigation = ['ride', 'drive', 'business', 'safety'];
    $accountUrl = Auth::guard('company')->check() ? url('signin_company') : (Auth::check() ? url(Auth::user()->user_type === 'Driver' ? 'driver_profile' : 'profile') : url('signin'));
    $accountLabel = __('website.'.(Auth::check() || Auth::guard('company')->check() ? 'my_account' : 'sign_in'));
@endphp
<header class="site-header">
    <div class="site-container site-header__inner">
        <a class="site-brand" href="{{ url('/') }}" aria-label="{{ __('website.brand_home') }}"><img src="{{ asset('images/marketing/lala-taxi-logo.webp') }}" alt="{{ $site_name }}" width="144" height="48"></a>
        <nav class="site-nav" aria-label="{{ __('website.main_nav') }}">
            @foreach($navigation as $path)<a href="{{ url($path) }}" @if(request()->is($path, $path.'/*')) aria-current="page" @endif>{{ __('website.'.$path) }}</a>@endforeach
        </nav>
        <div class="site-header__actions">
            <a class="site-header__signin" href="{{ $accountUrl }}">{{ $accountLabel }}</a>
            <a class="site-btn site-btn--gold" href="{{ url('app/rider') }}" data-app-open>{{ __('website.get_app') }}</a>
            <button class="site-menu-button" aria-label="{{ __('website.open_menu') }}" data-open-label="{{ __('website.open_menu') }}" data-close-label="{{ __('website.close_menu') }}" aria-expanded="false" aria-controls="mobile-navigation" data-menu-toggle><i data-lucide="menu" aria-hidden="true"></i></button>
        </div>
    </div>
    <nav class="site-mobile-nav" id="mobile-navigation" aria-label="{{ __('website.mobile_nav') }}" hidden>
        @foreach($navigation as $path)<a href="{{ url($path) }}">{{ __('website.'.$path) }}</a>@endforeach
        <a href="{{ $accountUrl }}">{{ $accountLabel }}</a><a href="{{ url('help') }}">{{ __('website.help_support') }}</a>
    </nav>
</header>
