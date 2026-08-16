<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trip Receipt</title>
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css') }}">
  <script src="{{ url('js/colors.js') }}"></script>
  <!-- /lalatokens -->
</head>
<body style="font-family:Arial,sans-serif;background:var(--color-white);">
    @include('admin.payments.receipt_sheet', ['result' => $result, 'invoice_data' => $invoice_data, 'printMode' => false])
</body>
</html>
