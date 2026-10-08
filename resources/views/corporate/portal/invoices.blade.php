@extends('corporate.portal.layout')
@section('title', __('corporate.invoices'))
@section('content')
<section class="portal-card">
    <div class="portal-heading"><h2>{{ __('corporate.invoices') }}</h2><span class="portal-badge">{{ $invoices->total() }}</span></div>
    <p class="portal-muted">{{ __('corporate.portal_invoice_hint') }}</p>
    @if($invoices->isNotEmpty())<p class="portal-table-hint"><i data-lucide="move-horizontal" aria-hidden="true"></i>{{ __('corporate.portal_table_hint') }}</p>@endif
    <div class="portal-table-wrap" tabindex="0" role="region" aria-label="{{ __('corporate.invoices') }}"><table>
        <thead><tr><th scope="col">{{ __('corporate.invoice') }}</th><th scope="col">{{ __('corporate.status') }}</th><th scope="col">{{ __('corporate.amount') }}</th><th scope="col">{{ __('corporate.due_date') }}</th></tr></thead>
        <tbody>@forelse($invoices as $invoice)<tr>
            <td><a class="portal-link" href="{{ route('corporate.portal.invoices.show', $invoice->id) }}"><i data-lucide="file-text" aria-hidden="true"></i>{{ $invoice->invoice_number }}</a></td><td><span class="portal-badge {{ $invoice->status === 'Paid' ? 'portal-badge--success' : ($invoice->status === 'Sent' ? 'portal-badge--gold' : '') }}">{{ __('corporate.states.'.$invoice->status) }}</span></td><td class="portal-money">{{ $invoice->currency_code }} {{ $invoice->total_amount }}</td><td>{{ optional($invoice->due_date)->format('d M Y') }}</td>
        </tr>@empty<tr><td colspan="4"><div class="portal-empty"><span class="portal-icon"><i data-lucide="receipt-text" aria-hidden="true"></i></span><strong>{{ __('corporate.portal_no_invoices') }}</strong><p>{{ __('corporate.portal_invoice_hint') }}</p></div></td></tr>@endforelse</tbody>
    </table></div><div class="portal-pagination">{{ $invoices->links() }}</div>
</section>
@endsection
