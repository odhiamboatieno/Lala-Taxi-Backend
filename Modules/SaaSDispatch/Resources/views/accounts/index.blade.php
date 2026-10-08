@extends('admin.template')
@section('title-sub', __('corporate.accounts'))
@section('pagetitle', __('corporate.accounts'))
@section('main')
<div id="layout-wrapper">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h2 class="mb-1">{{ __('corporate.accounts') }}</h2><p class="text-muted mb-0">{{ $company->name }}</p></div>
        @if($canCreateAccounts)<a class="btn btn-primary" href="{{ route($accountsRoute.'accounts.create', $accountsRouteParameters) }}">{{ __('corporate.create_account') }}</a>@endif
    </div>
    <div class="card mb-4">
        <div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.accounts') }}</h3></div>
        <div class="card-body">
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>{{ __('corporate.account_name') }}</th><th>{{ __('corporate.contact_email') }}</th><th>{{ __('corporate.billing_cycle') }}</th><th>{{ __('corporate.currency_code') }}</th><th>{{ __('corporate.status') }}</th></tr></thead>
                <tbody>@forelse($accounts as $account)
                    <tr id="search-company_corporate_accounts-{{ $account->id }}">
                        <td><a href="{{ route($accountsRoute.'accounts.show', $accountsRouteParameters + ['account' => $account->id]) }}">{{ $account->account_name }}</a></td>
                        <td>{{ $account->contact_email }}</td><td>{{ __('corporate.cycles.'.$account->billing_cycle) }}</td><td>{{ $account->currency_code }}</td><td>{{ __('corporate.states.'.($account->archived_at ? 'Archived' : $account->status)) }}</td>
                    </tr>
                @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('corporate.no_accounts') }}</td></tr>@endforelse</tbody>
            </table></div>
            {{ $accounts->links() }}
        </div>
    </div>
    <div class="card"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.invoices') }}</h3></div><div class="card-body">@include('saasdispatch::accounts.invoices')</div></div>
</div>
@endsection
