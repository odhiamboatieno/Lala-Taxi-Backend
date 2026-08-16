<!DOCTYPE html>
<html>
<head>
    <title>{{ __('messages.blade_static.mpesa_payment_628ddd80') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
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
    <style>
    .preloader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--color-as-rgba-255-255-255-0-8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .spinner {
        border: 4px solid var(--color-as-f3f3f3);
        border-top: 4px solid var(--color-as-28a745);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .payment-form {
        max-width: 500px;
        margin: 100px auto;
        padding: 20px;
        border-radius: 8px;
    }
    .form-control {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid var(--color-as-cccccc);
        border-radius: 4px;
    }
    .form-outline.mb-9 {
        margin-bottom: 20px; /* Adjusted to add 20px space between textbox and button */
    }
    button {
        width: 100%;
        padding: 12px;
        background: var(--color-as-28a745);
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    button:hover {
        background: var(--color-as-218838);
    }
    #phoneError {
        color: red;
        font-size: 0.9em;
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
<body ng-app="App" class="">
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    <main>
        <div class="payment-form" id="theme">
            <div class="main-header">
                <img src="{{ url('images/new/mpesa.png') }}" alt="{{ __('messages.blade_static.pay_with_mpesa_c08bc00a') }}" width="180" class="CToWUd bot_footimg">
            </div>
            <form id="checkout_payment" method="post" action="{{ route('payment.mpesa') }}">
                @csrf
                <div class="form-outline mb-9">
                    <label class="form-label" for="PhoneNumber">{{ __('messages.blade_static.please_enter_phone_number_9286ed09') }}</label>
                    <input type="tel" id="PhoneNumber" name="phone_number" class="form-control" required minlength="10" maxlength="10" pattern="07[0-9]{8}" placeholder="{{ __('messages.blade_static.07xxxxxxxx_89a2e30b') }}" />
                    <span id="phoneError" style="display: none;">{{ __('messages.blade_static.please_enter_a_valid_phone_number_starting_with_07_and_1_0065897c') }}</span>
                    <input type="hidden" name="amount" value="{{ $amount }}">
                    <input type="hidden" name="user_id" value="{{ $user_id }}">
                    <input type="hidden" name="pay_for" value="{{ $pay_for }}">
                    <input type="hidden" name="currency_code" value="{{ $currency_code }}">
                </div>
                <button type="submit" id="payButton">Pay {{ $currency_code }} {{ $amount }}</button>
            </form>
        </div>
    </main>

    {!! Html::script('js/jquery-1.11.3.js') !!}
    {!! Html::script('js/jquery-ui.js') !!}
    {!! Html::script('js/angular.js') !!}
    {!! Html::script('js/angular-sanitize.js') !!}

    <script>
        var app = angular.module('App', ['ngSanitize']);
        var APP_URL = {!! json_encode(url('/')) !!};

        const urlParams = new URLSearchParams(window.location.search);
        const myParam = urlParams.get('mode');
        var element = document.getElementById("theme");
        if (myParam) {
            element.classList.add(myParam);
        }

        document.getElementById('checkout_payment').addEventListener('submit', function(e) {
            e.preventDefault();
            var phoneNumber = document.getElementById('PhoneNumber').value;
            var phoneError = document.getElementById('phoneError');
            var preloader = document.getElementById('preloader');

            if (!phoneNumber.match(/^07[0-9]{8}$/)) {
                phoneError.style.display = 'block';
                return;
            }

            phoneError.style.display = 'none';
            preloader.style.display = 'flex';
            document.getElementById('payButton').disabled = true;

            this.submit();
        });
    </script>
</body>
</html>