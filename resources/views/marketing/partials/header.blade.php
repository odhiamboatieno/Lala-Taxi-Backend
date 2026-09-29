@php
    $navigation = ['ride' => 'Ride', 'drive' => 'Drive', 'business' => 'Business', 'safety' => 'Safety'];
    $accountUrl = Auth::guard('company')->check() ? url('signin_company') : (Auth::check() ? url(Auth::user()->user_type === 'Driver' ? 'driver_profile' : 'profile') : url('signin'));
    $accountLabel = Auth::check() || Auth::guard('company')->check() ? 'My account' : 'Sign in';
@endphp
<header class="site-header">
    <div class="site-container site-header__inner">
        <a class="site-brand" href="{{ url('/') }}" aria-label="LalaTaxi home"><img src="{{ asset('images/marketing/lala-taxi-logo.webp') }}" alt="LalaTaxi" width="144" height="48"></a>
        <nav class="site-nav" aria-label="Main navigation">
            @foreach($navigation as $path => $label)
                <a href="{{ url($path) }}" @if(request()->is($path, $path.'/*')) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="site-header__actions">
            <a class="site-header__signin" href="{{ $accountUrl }}">{{ $accountLabel }}</a>
            <a class="site-btn site-btn--gold" href="{{ url('app/rider') }}" data-app-open>Get the app</a>
            <button class="site-menu-button" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-navigation" data-menu-toggle><i data-lucide="menu" aria-hidden="true"></i></button>
        </div>
    </div>
    <nav class="site-mobile-nav" id="mobile-navigation" aria-label="Mobile navigation" hidden>
        @foreach($navigation as $path => $label)<a href="{{ url($path) }}">{{ $label }}</a>@endforeach
        <a href="{{ $accountUrl }}">{{ $accountLabel }}</a><a href="{{ url('help') }}">Help & support</a>
    </nav>
</header>
