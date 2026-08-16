<!DOCTYPE html>
<html>
<head>
  <title>{{ __('messages.blade_static.payment_b41a92be') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
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

{!! Html::style('css/payment.css') !!}
<body ng-app="App" class="">

  <main>
    <div class="payment-form" id="theme">
      <div class="main-header">
        <div>{{$currency_code}} {{$amount}}</div>
      </div>
      <form id="checkout_payment" method="post" action="{{route('payment.success')}}">
        <input type="hidden" name="pay_key" id="nonce"> 
        <input type="hidden" name="payment_type" id="payment_type"> 
        @foreach($view as $key => $paymet_methods)
        <div class="row" >
         @if ($loop->even)
         <span style="text-align: center;">{{ __('messages.blade_static.or_f579c523') }}</span>
         @endif
         @include($paymet_methods['view'],['data' =>  ['currency_code' => $currency_code,'amount' => $amount,]+$paymet_methods] )
       </div>
       @endforeach
     </form>
   </div>
 </main>
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