@php $assetVersion = $version ?? config('app.version') ?? '1'; @endphp
{{-- Minimal legacy dependencies for the auth pages: the OTP modal is built
     from bootstrap markup, and pop_up_email / accountkit need these files.
     The visual design comes entirely from marketing.css which loads after. --}}
{!! Html::style('css/bootstrap.css') !!}
{!! Html::style('css/popup.css?v='.$assetVersion) !!}
{!! Html::style('css/jquery-ui.min.css') !!}
{!! Html::style('css/jquery.bxslider.css') !!}
{!! Html::style('css/jquery.sliderTabs.min.css') !!}
