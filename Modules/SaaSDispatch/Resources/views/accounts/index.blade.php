@extends('admin.template')

@section('title-sub', 'Accounts / Invoicing')
@section('pagetitle', 'Accounts / Invoicing')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Corporate Accounts</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.self.accounts.accounts.store') }}" class="row g-3 mb-4">
                        @csrf
                        <div class="col-12"><input class="form-control" name="account_name" placeholder="Account name"></div>
                        <div class="col-md-6"><input class="form-control" name="contact_name" placeholder="Contact name"></div>
                        <div class="col-md-6"><input class="form-control" name="contact_email" placeholder="Contact email"></div>
                        <div class="col-md-6"><input class="form-control" name="contact_phone" placeholder="Contact phone"></div>
                        <div class="col-md-6">
                            <select name="billing_cycle" class="form-control">
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>
                        <div class="col-12"><input class="form-control" name="credit_limit" placeholder="Credit limit"></div>
                        <div class="col-12"><textarea class="form-control" name="notes" rows="3" placeholder="Notes"></textarea></div>
                        <div class="col-12"><button class="btn btn-primary">Create Account</button></div>
                    </form>
                    @foreach($accounts as $account)
                        <div class="border rounded p-3 mb-3" id="search-company_corporate_accounts-{{ $account->id }}">
                            <div class="fw-semibold">{{ $account->account_name }}</div>
                            <div class="text-muted">{{ $account->contact_email ?: 'No email' }} | {{ ucfirst($account->billing_cycle) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Generate Invoice</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.self.accounts.invoices.store') }}" class="row g-3 mb-4">
                        @csrf
                        <div class="col-md-6">
                            <select name="corporate_account_id" class="form-control">
                                <option value="">Select account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><input type="date" class="form-control" name="invoice_date"></div>
                        <div class="col-md-3"><input type="date" class="form-control" name="due_date"></div>
                        <div class="col-md-4"><input class="form-control" name="subtotal" placeholder="Subtotal"></div>
                        <div class="col-md-4"><input class="form-control" name="tax_amount" placeholder="Tax amount"></div>
                        <div class="col-md-12"><textarea class="form-control" name="notes" rows="3" placeholder="Invoice notes"></textarea></div>
                        <div class="col-12"><button class="btn btn-primary">Create Invoice</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Invoice</th><th>Account</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr id="search-company_corporate_invoices-{{ $invoice->id }}">
                                        <td>{{ $invoice->invoice_number }}<br><small class="text-muted">{{ optional($invoice->invoice_date)->format('d M Y') }}</small></td>
                                        <td>{{ optional($invoice->account)->account_name }}</td>
                                        <td>{{ number_format((float) $invoice->total_amount, 2) }}</td>
                                        <td><span class="badge bg-info">{{ $invoice->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-5">No invoices created yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
