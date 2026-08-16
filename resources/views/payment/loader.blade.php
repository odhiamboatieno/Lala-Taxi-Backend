<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('messages.blade_static.payment_success_2d591b04') }}</title>
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css?v='.filemtime(public_path('css/theme.css'))) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<script src="{{ url('js/colors.js?v='.filemtime(public_path('js/colors.js'))) }}"></script>
  <script src="{{ url('js/lucide.min.js?v='.filemtime(public_path('js/lucide.min.js'))) }}"></script>
  <script src="{{ url('js/lucide-loader.js?v='.filemtime(public_path('js/lucide-loader.js'))) }}"></script>
  <!-- /lalatokens -->
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: var(--color-as-rgb-255-255-255); display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
<circle cx="84" cy="50" r="10" fill="var(--color-as-e15b64)">
    <animate attributeName="r" repeatCount="indefinite" dur="0.25s" calcMode="spline" keyTimes="0;1" values="10;0" keySplines="0 0.5 0.5 1" begin="0s"></animate>
    <animate attributeName="fill" repeatCount="indefinite" dur="1s" calcMode="discrete" keyTimes="0;0.25;0.5;0.75;1" values="var(--color-as-e15b64);var(--color-as-abbd81);var(--color-as-f8b26a);var(--color-as-f47e60);var(--color-as-e15b64)" begin="0s"></animate>
</circle><circle cx="16" cy="50" r="10" fill="var(--color-as-e15b64)">
  <animate attributeName="r" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
  <animate attributeName="cx" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
</circle><circle cx="50" cy="50" r="10" fill="var(--color-as-f47e60)">
  <animate attributeName="r" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.25s"></animate>
  <animate attributeName="cx" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.25s"></animate>
</circle><circle cx="84" cy="50" r="10" fill="var(--color-as-f8b26a)">
  <animate attributeName="r" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.5s"></animate>
  <animate attributeName="cx" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.5s"></animate>
</circle><circle cx="16" cy="50" r="10" fill="var(--color-as-abbd81)">
  <animate attributeName="r" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.75s"></animate>
  <animate attributeName="cx" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.75s"></animate>
</circle>
</svg>
</body>
{!! Html::script('js/jquery-1.11.3.js') !!}
{!! Html::script('js/jquery-ui.js') !!}
{!! Html::script('js/angular.js') !!}
{!! Html::script('js/angular-sanitize.js') !!}

<script>
  var app = angular.module('App', ['ngSanitize']);
  var APP_URL = {!! json_encode(url('/')) !!};

    // Get URL to Create Dark theme
    const urlParams = new URLSearchParams(window.location.search);
    const myParam = urlParams.get('mode');
    var element = document.getElementById("theme");
    element.classList.add(myParam);

  </script>
  
  @stack('scripts')
  </html>