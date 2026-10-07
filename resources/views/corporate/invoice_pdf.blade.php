<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ $invoice->invoice_number }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#111}h1{font-size:24px}table{width:100%;border-collapse:collapse;margin-top:24px}th,td{text-align:left;padding:10px 6px;border-bottom:1px solid #ddd}th{background:#f8f7f3}.amount{text-align:right}.muted{color:#666}.total{font-weight:bold;font-size:15px}p{line-height:1.6;white-space:pre-line}</style></head>
<body>
<h1>{{ __('corporate.invoice') }}</h1>
<p><strong>{{ $invoice->invoice_number }}</strong><br>{{ __('corporate.invoice_date') }}: {{ $invoice->invoice_date->format('Y-m-d') }}<br>{{ __('corporate.due_date') }}: {{ optional($invoice->due_date)->format('Y-m-d') }}</p>
<h2>{{ data_get($invoice->bill_to_snapshot, 'legal_name') }}</h2>
<p>{{ data_get($invoice->bill_to_snapshot, 'billing_address') }}<br>{{ data_get($invoice->bill_to_snapshot, 'contact_email') }}</p>
<p class="muted">{{ __('corporate.billing_period') }}: {{ $invoice->period_start->copy()->timezone($invoice->billing_timezone)->format('Y-m-d') }} — {{ $invoice->period_end->copy()->timezone($invoice->billing_timezone)->format('Y-m-d') }} ({{ $invoice->billing_timezone }})<br>{{ __('corporate.revision') }}: {{ $invoice->revision }}</p>
<table><thead><tr><th>{{ __('corporate.trip') }}</th><th>{{ __('corporate.completed_at') }}</th><th>{{ __('corporate.route') }}</th><th class="amount">{{ __('corporate.amount') }} ({{ $invoice->currency_code }})</th></tr></thead><tbody>
@foreach($invoice->items as $item)
<tr><td>{{ $item->description }}</td><td>{{ \Carbon\Carbon::parse(data_get($item->trip_snapshot, 'completed_at'))->timezone($invoice->billing_timezone)->format('Y-m-d H:i') }}</td><td>{{ data_get($item->trip_snapshot, 'pickup_location') }} → {{ data_get($item->trip_snapshot, 'drop_location') }}</td><td class="amount">{{ $item->total_amount }}</td></tr>
@endforeach
</tbody></table>
<p class="amount total">{{ __('corporate.total') }}: {{ $invoice->currency_code }} {{ $invoice->total_amount }}</p>
<p>{{ __('corporate.status') }}: {{ $invoice->status }}<br>{{ __('corporate.no_personal_collection') }}</p>
</body></html>
