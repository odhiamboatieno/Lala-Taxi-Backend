<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('website.site_title'))</title>
    <meta name="description" content="@yield('description', __('website.site_description'))">
    <link rel="icon" href="{{ $favicon ?? asset('favicon.ico') }}">
    <link rel="preload" href="{{ asset('fonts/InterVariable.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/marketing-tokens.css') }}?v={{ filemtime(public_path('css/marketing-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
    @stack('styles')
    <script defer src="{{ asset('js/lucide.min.js') }}"></script>
    <script defer src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}"></script>
</head>
<body class="marketing-page">
    <a class="skip-link" href="#main">{{ __('website.skip_content') }}</a>
    @include('marketing.partials.header')
    <main id="main">@yield('main')</main>
    @include('marketing.partials.footer')
    @include('marketing.partials.app-dialog')
    @stack('scripts')
    {!! $head_code ?? '' !!}
</body>
</html>
