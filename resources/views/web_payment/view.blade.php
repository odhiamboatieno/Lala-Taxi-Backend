<!DOCTYPE html>
<html>
<head>
    <title>{{ __('messages.blade_static.payment_response_1747dbd4') }}</title>
    <script>
        // Expose payment_status to the JavaScript context
        window.payment_status = @json($payment_status);
    </script>
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css?v='.filemtime(public_path('css/theme.css'))) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<script src="{{ url('js/colors.js?v='.filemtime(public_path('js/colors.js'))) }}"></script>
  <!-- /lalatokens -->
</head>
<body>
    <h1>{{ __('messages.blade_static.payment_processing_0d203aa0') }}</h1>
    <p>{{ __('messages.blade_static.please_wait_while_we_process_your_payment_243af86b') }}</p>
    <!-- Optional: Keep the JSON data in a hidden div for debugging -->
    <div style="display: none" id="data">
        {!! json_encode($payment_status) !!}
    </div>
</body>
</html>