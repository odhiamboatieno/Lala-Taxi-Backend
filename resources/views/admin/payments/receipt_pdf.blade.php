<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trip Receipt</title>
</head>
<body style="font-family:Arial,sans-serif;background:#ffffff;">
    @include('admin.payments.receipt_sheet', ['result' => $result, 'invoice_data' => $invoice_data, 'printMode' => false])
</body>
</html>
