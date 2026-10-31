@extends('admin.template')
@section('title-sub', __('corporate.accounts'))
@section('pagetitle', __('corporate.accounts'))
@section('main')
<div id="layout-wrapper" class="corporate-admin corporate-admin-directory">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div><h2 class="mb-2">{{ __('corporate.accounts') }}</h2><p class="text-muted mb-0">{{ __('corporate.directory_hint') }}</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('corporate.portal.login') }}">{{ __('corporate.manager_login') }}</a>
            @if($canCreateAccounts)<a class="btn btn-primary" href="{{ route('saasdispatch.admin.corporate-accounts.accounts.create') }}">{{ __('corporate.create_account') }}</a>@endif
        </div>
    </div>
    @include('saasdispatch::accounts.errors')
    <div class="card mb-4"><div class="card-body">
        <form method="GET" action="{{ route('saasdispatch.admin.corporate-accounts.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-5"><label for="account-search" class="form-label">{{ __('corporate.search') }}</label><input id="account-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('corporate.directory_search') }}" maxlength="191"></div>
            <div class="col-lg-3"><label for="account-operator" class="form-label">{{ __('corporate.operator') }}</label><select id="account-operator" name="company_id" class="form-control"><option value="">{{ __('corporate.all_operators') }}</option>@foreach($operators as $operator)<option value="{{ $operator->id }}" @selected((string) request('company_id') === (string) $operator->id)>{{ $operator->name }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label for="account-status" class="form-label">{{ __('corporate.status') }}</label><select id="account-status" name="status" class="form-control"><option value="">{{ __('corporate.all_statuses') }}</option>@foreach(['Active', 'Inactive', 'Archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __('corporate.states.'.$status) }}</option>@endforeach</select></div>
            <div class="col-lg-2 d-flex gap-2"><button type="submit" class="btn btn-primary">{{ __('corporate.search') }}</button><a class="btn btn-outline-secondary" href="{{ route('saasdispatch.admin.corporate-accounts.index') }}">{{ __('corporate.clear_filters') }}</a></div>
        </form>
    </div></div>
    <div class="card"><div class="card-header d-flex flex-wrap justify-content-between gap-2"><h3 class="h5 mb-0">{{ __('corporate.accounts') }}</h3><span class="text-muted">{{ trans_choice('corporate.directory_count', $accounts->total(), ['count' => $accounts->total()]) }}</span></div>
        <div class="card-body">
            <div class="table-responsive" role="region" tabindex="0" aria-label="{{ __('corporate.accounts') }}"><table class="table align-middle">
                <thead><tr><th scope="col">{{ __('corporate.account_name') }}</th><th scope="col">{{ __('corporate.operator') }}</th><th scope="col">{{ __('corporate.portal_active_members') }}</th><th scope="col">{{ __('corporate.activated_admins') }}</th><th scope="col">{{ __('corporate.status') }}</th><th scope="col">{{ __('corporate.actions') }}</th></tr></thead>
                <tbody>
                    @forelse($accounts as $account)
                    @php($accountParameters = ['company' => $account->company_id, 'account' => $account->id])
                    @php($accountUrl = route('saasdispatch.companies.accounts.accounts.show', $accountParameters))
                    <tr>
                        <td><a class="fw-semibold" href="{{ $accountUrl }}">{{ $account->account_name }}</a><small class="d-block text-muted text-break">{{ $account->contact_email }}</small><small class="d-block text-muted">{{ $account->currency_code }}</small></td>
                        <td>{{ $account->company?->name }}</td>
                        <td><a href="{{ $accountUrl }}#corporate-members" aria-label="{{ __('corporate.members_for', ['account' => $account->account_name]) }}">{{ $account->active_members_count }}</a></td>
                        <td><a href="{{ $accountUrl }}#corporate-portal-access">{{ $account->ready_admins_count > 0 ? $account->ready_admins_count : __('corporate.setup_access') }}</a></td>
                        <td><span class="badge {{ $account->archived_at || $account->status !== 'Active' ? 'bg-secondary' : 'bg-success' }}">{{ __('corporate.states.'.($account->archived_at ? 'Archived' : $account->status)) }}</span></td>
                        <td><div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $accountUrl }}">{{ __('corporate.view_details') }}</a>
                            @unless($account->archived_at)<a class="btn btn-sm btn-outline-primary" href="{{ route('saasdispatch.companies.accounts.accounts.edit', $accountParameters) }}">{{ __('corporate.edit_account') }}</a>@endunless
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $accountUrl }}#corporate-members">{{ __('corporate.members') }}</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $accountUrl }}#corporate-portal-access">{{ __('corporate.portal_staff') }}</a>
                            @unless($account->archived_at)<a class="btn btn-sm btn-outline-danger" href="{{ $accountUrl }}#corporate-archive">{{ __('corporate.archive_account') }}</a>@endunless
                        </div></td>
                    </tr>
                    @empty<tr><td colspan="6" class="text-center py-5"><h4 class="h6">{{ __('corporate.directory_empty') }}</h4><p class="text-muted mb-0">{{ __('corporate.directory_empty_hint') }}</p></td></tr>@endforelse
                </tbody>
            </table></div>
            {{ $accounts->links() }}
            <p class="text-muted mt-3 mb-0">{{ __('corporate.directory_access_hint') }}</p>
        </div>
    </div>
</div>
@endsection
