@php $assetVersion = $version ?? config('app.version') ?? '1'; @endphp
{{-- Legacy site styles, loaded BEFORE the marketing design system so that
     marketing.css / marketing-tokens.css always win the cascade. Only needed
     by pages that still contain legacy markup (form controls, bootstrap grid,
     OTP modal, help navtree). --}}
{!! Html::style('css/bootstrap.css') !!}
{!! Html::style('css/font-awesome.min.css') !!}
{!! Html::style('css/main.css?v='.$assetVersion) !!}
{!! Html::style('css/common.css?v='.$assetVersion) !!}
{!! Html::style('css/common1.css?v='.$assetVersion) !!}
{!! Html::style('css/popup.css?v='.$assetVersion) !!}
{!! Html::style('css/jquery.bxslider.css') !!}
{!! Html::style('css/jquery.sliderTabs.min.css') !!}
{!! Html::style('css/jquery-ui.min.css') !!}
{!! Html::style('css/theme.css?v='.filemtime(public_path('css/theme.css'))) !!}
