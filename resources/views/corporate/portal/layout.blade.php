<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('corporate.portal_title')) · {{ $site_name ?? config('app.name') }}</title>
    <link rel="icon" href="{{ $favicon ?? asset('favicon.ico') }}">
    <link rel="preload" href="{{ asset('fonts/InterVariable.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/marketing-tokens.css') }}?v={{ filemtime(public_path('css/marketing-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/corporate-portal.css') }}?v={{ filemtime(public_path('css/corporate-portal.css')) }}">
    <script defer src="{{ asset('js/lucide.min.js') }}"></script>
    <script defer src="{{ asset('js/corporate-portal.js') }}?v={{ filemtime(public_path('js/corporate-portal.js')) }}"></script>
</head>
<body class="{{ isset($actor) ? 'portal-workspace' : 'portal-guest' }}">
<a class="portal-skip" href="#main">{{ __('website.skip_content') }}</a>
@isset($actor)
    <aside class="portal-sidebar">
        <a class="portal-brand" href="{{ url('/') }}"><img src="{{ asset('images/marketing/lala-taxi-logo.webp') }}" alt="{{ $site_name ?? config('app.name') }}" width="128" height="44"></a>
        <p class="portal-eyebrow">{{ __('corporate.portal_title') }}</p>
        <div class="portal-company"><span class="portal-icon portal-icon--gold"><i data-lucide="building-2" aria-hidden="true"></i></span><strong>{{ $account->account_name }}</strong></div>
        <nav class="portal-nav" aria-label="{{ __('corporate.portal_title') }}">
            @foreach(['dashboard'=>['portal_overview','layout-dashboard'], 'trips'=>['trips','route']] as $name=>[$label,$icon])
                <a href="{{ route('corporate.portal.'.$name) }}" @if(request()->routeIs('corporate.portal.'.$name, 'corporate.portal.'.$name.'.*')) aria-current="page" @endif><i data-lucide="{{ $icon }}" aria-hidden="true"></i>{{ __('corporate.'.$label) }}</a>
            @endforeach
            @if($actor->canManageUsers())
                <a href="{{ route('corporate.portal.members') }}" @if(request()->routeIs('corporate.portal.members*')) aria-current="page" @endif><i data-lucide="users-round" aria-hidden="true"></i>{{ __('corporate.members') }}</a>
                <a href="{{ route('corporate.portal.staff') }}" @if(request()->routeIs('corporate.portal.staff*')) aria-current="page" @endif><i data-lucide="shield-check" aria-hidden="true"></i>{{ __('corporate.portal_staff') }}</a>
            @endif
            @if($actor->canViewInvoices())
                <a href="{{ route('corporate.portal.invoices') }}" @if(request()->routeIs('corporate.portal.invoices*')) aria-current="page" @endif><i data-lucide="receipt-text" aria-hidden="true"></i>{{ __('corporate.invoices') }}</a>
            @endif
        </nav>
        <div class="portal-sidebar-note"><i data-lucide="lock-keyhole" aria-hidden="true"></i><p>{{ __('corporate.portal_workspace_hint') }}</p></div>
    </aside>
    <div class="portal-workspace-body">
        <header class="portal-header">
            <span class="portal-workspace-label"><i data-lucide="briefcase-business" aria-hidden="true"></i>{{ __('corporate.portal_workspace') }}</span>
            <div class="portal-actions portal-user"><span class="portal-avatar" aria-hidden="true">{{ mb_substr($actor->name, 0, 1) }}</span><span><strong>{{ $actor->name }}</strong><small>{{ __('corporate.portal_roles.'.$actor->role) }}</small></span><form method="POST" action="{{ route('corporate.portal.logout') }}">@csrf<button class="portal-button portal-button--quiet" type="submit"><i data-lucide="log-out" aria-hidden="true"></i>{{ __('corporate.portal_sign_out') }}</button></form></div>
        </header>
@else
    <header class="portal-header portal-guest-header">
        <a class="portal-brand" href="{{ url('/') }}"><img src="{{ asset('images/marketing/lala-taxi-logo.webp') }}" alt="{{ $site_name ?? config('app.name') }}" width="128" height="44"></a>
        <span class="portal-workspace-label"><i data-lucide="briefcase-business" aria-hidden="true"></i>{{ __('corporate.portal_title') }}</span>
    </header>
@endisset
<main id="main" class="portal-wrap" tabindex="-1">
    @isset($actor)
        <div class="portal-page-intro"><p class="portal-eyebrow">{{ __('corporate.portal_workspace') }}</p><h1>{{ $account->account_name }}</h1></div>
        @if($account->archived_at)<div class="portal-alert" role="status"><i data-lucide="archive" aria-hidden="true"></i><span>{{ __('corporate.account_archived') }}</span></div>@endif
    @endisset
    @if(session('status'))<div class="portal-alert" role="status"><i data-lucide="circle-check" aria-hidden="true"></i><span>{{ session('status') }}</span></div>@endif
    @if($errors->any())<div class="portal-alert portal-alert--error" role="alert"><i data-lucide="circle-alert" aria-hidden="true"></i><div><strong>{{ __('corporate.correct_errors') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    @yield('content')
</main>
@isset($actor)</div>@endisset
</body>
</html>
