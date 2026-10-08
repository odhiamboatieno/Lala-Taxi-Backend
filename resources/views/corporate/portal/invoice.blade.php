@extends('corporate.portal.layout')
@section('title', __('corporate.invoice'))
@section('content')
<a class="portal-link" href="{{ route('corporate.portal.invoices') }}"><i data-lucide="arrow-left" aria-hidden="true"></i>{{ __('corporate.invoices') }}</a>
<section class="portal-card">
    <div class="portal-heading"><div><p class="portal-eyebrow">{{ __('corporate.invoice') }}</p><h2>{{ $invoice->invoice_number }}</h2></div><a class="portal-button portal-button--gold" href="{{ route('corporate.portal.invoices.download', $invoice->id) }}"><i data-lucide="download" aria-hidden="true"></i>{{ __('corporate.download_pdf') }}</a></div>
    <span class="portal-badge {{ $invoice->status === 'Paid' ? 'portal-badge--success' : 'portal-badge--gold' }}">{{ __('corporate.states.'.$invoice->status) }}</span>
    <dl class="portal-pairs"><div><dt>{{ __('corporate.billing_period') }}</dt><dd>{{ $invoice->period_start->copy()->timezone($invoice->billing_timezone)->format('d M Y') }} — {{ $invoice->period_end->copy()->timezone($invoice->billing_timezone)->format('d M Y') }}</dd></div><div><dt>{{ __('corporate.due_date') }}</dt><dd>{{ optional($invoice->due_date)->format('d M Y') }}</dd></div><div><dt>{{ __('corporate.billing_timezone') }}</dt><dd>{{ $invoice->billing_timezone }}</dd></div><div><dt>{{ __('corporate.currency_code') }}</dt><dd>{{ $invoice->currency_code }}</dd></div></dl>
    <div class="portal-table-wrap portal-table-wrap--detail" tabindex="0" role="region" aria-label="{{ __('corporate.invoice_items') }}"><table><thead><tr><th scope="col">{{ __('corporate.trip') }}</th><th scope="col">{{ __('corporate.amount') }}</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td class="portal-money">{{ $item->currency_code }} {{ $item->total_amount }}</td></tr>@endforeach</tbody></table></div>
    <div class="portal-total"><span>{{ __('corporate.total') }}</span><strong>{{ $invoice->currency_code }} {{ $invoice->total_amount }}</strong></div>
    <div class="portal-help"><i data-lucide="info" aria-hidden="true"></i><p>{{ __('corporate.portal_invoice_hint') }}</p></div>
</section>
@endsection
