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
    @include('marketing.partials.auth-styles')
    <link rel="stylesheet" href="{{ asset('css/marketing-tokens.css') }}?v={{ filemtime(public_path('css/marketing-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/marketing-compat.css') }}?v={{ filemtime(public_path('css/marketing-compat.css')) }}">
    <script defer src="{{ asset('js/lucide.min.js') }}"></script>
    <script defer src="{{ asset('js/lucide-loader.js') }}"></script>
    <script defer src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}"></script>
</head>
<body class="marketing-page" ng-app="App">
    <a class="skip-link" href="#main">{{ __('website.skip_content') }}</a>
    @include('marketing.partials.header')
    <main id="main">@yield('main')</main>
    @include('marketing.partials.footer')

    @php $assetVersion = $version ?? config('app.version') ?? '1'; @endphp
    {!! Html::script('js/jquery-1.11.3.js') !!}
    {!! Html::script('js/jquery-ui.js') !!}
    {!! Html::script('js/angular.js') !!}
    {!! Html::script('js/angular-sanitize.js') !!}
    <script>
        var app = angular.module('App', ['ngSanitize']);
        var APP_URL = {!! json_encode(url('/')) !!};
        var LOGIN_USER_TYPE = '{!! LOGIN_USER_TYPE !!}';
        var STRIPE_PUBLISH_KEY = "{{ payment_gateway('publish', 'Stripe') }}";
    </script>
    {!! Html::script('js/common.js?v='.$assetVersion) !!}
    {!! Html::script('js/user.js?v='.$assetVersion) !!}
    {!! Html::script('js/main.js?v='.$assetVersion) !!}
    {!! Html::script('js/bootstrap.min.js') !!}
    {{-- Required by accountkit.js for the home_address Places autocomplete --}}
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $map_key }}&libraries=places"></script>
    <script src="{{ url('js/accountkit.js?v='.$assetVersion) }}"></script>
    <script type="text/javascript" src="https://apis.google.com/js/api:client.js"></script>
    <script src="{{ url('js/googleapilogin.js?v='.$assetVersion) }}"></script>
    {!! Html::script('js/pop_up_email.js?v='.$assetVersion) !!}
    @stack('scripts')
    {!! $head_code ?? '' !!}
</body>
</html>
