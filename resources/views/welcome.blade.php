<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('messages.blade_static.laravel_e31cfe3e') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: var(--color-white);
                color: var(--color-as-636b6f);
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: var(--color-as-636b6f);
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
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
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">{{ __('messages.blade_static.home_70f8bb9a') }}</a>
                    @else
                        <a href="{{ url('/login') }}">{{ __('messages.blade_static.login_4e5a2893') }}</a>
                        <a href="{{ url('/register') }}">{{ __('messages.blade_static.register_d672995a') }}</a>
                    @endif
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    {{ __('messages.blade_static.laravel_e31cfe3e') }}
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">{{ __('messages.blade_static.documentation_9e9cf322') }}</a>
                    <a href="https://laracasts.com">{{ __('messages.blade_static.laracasts_4fa3e7c6') }}</a>
                    <a href="https://laravel-news.com">{{ __('messages.blade_static.news_34c808d0') }}</a>
                    <a href="https://forge.laravel.com">{{ __('messages.blade_static.forge_3ab11b75') }}</a>
                    <a href="https://github.com/laravel/laravel">{{ __('messages.blade_static.github_5442e2b6') }}</a>
                </div>
            </div>
        </div>
    </body>
</html>
