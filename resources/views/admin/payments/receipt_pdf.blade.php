<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trip Receipt</title>
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
<body style="font-family:Arial,sans-serif;background:var(--color-white);">
    @include('admin.payments.receipt_sheet', ['result' => $result, 'invoice_data' => $invoice_data, 'printMode' => false])
</body>
</html>
