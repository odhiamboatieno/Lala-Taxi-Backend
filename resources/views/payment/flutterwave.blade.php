<!DOCTYPE html>
<html>
<head>
  <title>{{ __('messages.blade_static.payment_b41a92be') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css?v='.filemtime(public_path('css/theme.css'))) }}">
  <script src="{{ url('js/colors.js?v='.filemtime(public_path('js/colors.js'))) }}"></script>
  <!-- /lalatokens -->
</head>

{!! Html::style('css/webpayment.css') !!}

  {!! Html::style('css/bootstrap.css') !!}
  {!! Html::style('css/font-awesome.min.css') !!}
  {!! Html::style('css/main.css?v='.$version) !!}
  {!! Html::style('css/common.css?v='.$version) !!}
  {!! Html::style('css/common1.css?v='.$version) !!}
  {!! Html::style('css/styles.css?v='.$version) !!}
  {!! Html::style('css/home.css?v='.$version) !!}
  
  {!! Html::style('css/popup.css?v='.$version) !!}

  {!! Html::style('css/jquery.bxslider.css') !!}
  {!! Html::style('css/jquery.sliderTabs.min.css') !!}
  @if (Route::current()->uri() != 'driver_payment')
  {!! Html::style('css/jquery-ui.min.css') !!} 
  @endif
<body ng-app="App" class="">

  <main>
    <div class="payment-form" id="theme">
      <div class="main-header">
        <img src="{{ url('images/new/flutterwave.png') }}" alt="{{ __('messages.blade_static.pay_with_flutterwave_de0d2220') }}" class="CToWUd bot_footimg">
      </div>
      <form id="checkout_payment" method="post" action="{{route('payment.flutterwave')}}">
        <input type="hidden" name="pay_key" id="nonce"> 
      
      <!-- Phone Number -->
      <div class="form-outline mb-4">
        <label class="form-label" for="phoneNumber">{{ __('messages.blade_static.phone_number_ab25d61b') }}</label>
        <input type="tel" id="phoneNumber" class="form-control" required/>
       
      </div>
        <input type="hidden" name="amount" value="{{$amount}}"> 
        <input type="hidden" name="user_id" value="{{$user_id}}"> 
        <input type="hidden" name="pay_for" value="{{$pay_for}}">
        <input type="hidden" name="currency_code" value="{{$currency_code}}"> 
      <!-- Email Address -->
      <div class="form-outline mb-4">
        <label class="form-label" for="Email">{{ __('messages.blade_static.email_address_09ba557f') }}</label>
        <input type="email" id="Email" name="email"class="form-control" required />
      </div>
      <br>
      <!-- Submit button -->
       <button type="submit">Pay {{$currency_code}} {{$amount}}</button>

       </div>
       
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