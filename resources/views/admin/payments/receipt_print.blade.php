@extends('template_without_header_footer')

@section('main')
<body onload="window.print()" style="background:var(--color-as-f4f7fb);font-family:Arial,sans-serif;">
@include('admin.payments.receipt_sheet', ['result' => $result, 'invoice_data' => $invoice_data, 'printMode' => true])
</body>
@endsection
