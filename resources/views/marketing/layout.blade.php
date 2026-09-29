<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LalaTaxi — Your day. Your way. Your ride.')</title>
    <meta name="description" content="@yield('description', 'Everyday rides, airport trips and a better way to move through your day with LalaTaxi.')">
    <link rel="icon" href="{{ $favicon ?? asset('favicon.ico') }}">
    <link rel="preload" href="{{ asset('fonts/InterVariable.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/marketing-tokens.css') }}?v={{ filemtime(public_path('css/marketing-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
    @stack('styles')
    <script defer src="{{ asset('js/lucide.min.js') }}"></script>
    <script defer src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}"></script>
</head>
<body class="marketing-page">
    <a class="skip-link" href="#main">Skip to content</a>
    @include('marketing.partials.header')
    <main id="main">@yield('main')</main>
    @include('marketing.partials.footer')
    @include('marketing.partials.app-dialog')
    @stack('scripts')
</body>
</html>
