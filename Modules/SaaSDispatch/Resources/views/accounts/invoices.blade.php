<div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>{{ __('corporate.invoice') }}</th><th>{{ __('corporate.account_name') }}</th><th>{{ __('corporate.due_date') }}</th><th>{{ __('corporate.total') }}</th><th>{{ __('corporate.status') }}</th></tr></thead>
    <tbody>@forelse($invoices as $invoice)
        <tr id="search-company_corporate_invoices-{{ $invoice->id }}"><td><a href="{{ route($accountsRoute.'invoices.show', $accountsRouteParameters + ['invoice' => $invoice->id]) }}">{{ $invoice->invoice_number }}</a></td><td>{{ $invoice->account?->account_name }}</td><td>{{ $invoice->due_date?->format('Y-m-d') }}</td><td>{{ $invoice->currency_code }} {{ $invoice->total_amount }}</td><td>{{ __('corporate.states.'.$invoice->status) }}@unless($invoice->trip_backed)<small class="d-block text-muted">{{ __('corporate.legacy_invoice') }}</small>@endunless</td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('corporate.no_invoices') }}</td></tr>@endforelse</tbody>
</table></div>
{{ $invoices->links() }}
