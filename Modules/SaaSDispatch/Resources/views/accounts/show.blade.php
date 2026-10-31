@extends('admin.template')
@section('title-sub', $account->account_name)
@section('pagetitle', $account->account_name)
@section('main')
<div id="layout-wrapper" class="corporate-admin">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div><h2 class="mb-1">{{ $account->account_name }}</h2><p class="text-muted mb-0">{{ $company->name }} · {{ __('corporate.states.'.($account->archived_at ? 'Archived' : $account->status)) }}</p></div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-secondary" href="{{ $accountsIndexUrl }}">{{ __('corporate.back_accounts') }}</a>@unless($account->archived_at)<a class="btn btn-primary" href="{{ route($accountsRoute.'accounts.edit', $accountsRouteParameters + ['account' => $account->id]) }}">{{ __('corporate.edit_account') }}</a>@endunless</div>
    </div>
    <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="{{ __('corporate.account_sections') }}">
        <a class="btn btn-outline-secondary" href="#corporate-members">{{ __('corporate.members') }}</a>
        <a class="btn btn-outline-secondary" href="#corporate-portal-access">{{ __('corporate.portal_staff') }}</a>
        <a class="btn btn-outline-secondary" href="#corporate-trips">{{ __('corporate.trips') }}</a>
        <a class="btn btn-outline-secondary" href="#corporate-invoices">{{ __('corporate.invoices') }}</a>
    </nav>
    @include('saasdispatch::accounts.errors')
    @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    <div class="card mb-4" id="corporate-portal-access"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.portal_staff') }}</h3></div><div class="card-body"><p>{{ __('corporate.portal_staff_hint') }}</p><p>{{ __('corporate.portal_role_hint') }}</p><p><a href="{{ route('corporate.portal.login') }}">{{ __('corporate.manager_login') }} — {{ route('corporate.portal.login') }}</a></p>
        @unless($account->archived_at)
        <details class="border p-3 mb-3" @if(old('_portal_form') === 'new')open @endif>
            <summary class="fw-semibold">{{ __('corporate.portal_invite') }}</summary>
            <div class="mt-3">@include('corporate.portal.staff-form', ['staffUser'=>null, 'staffAction'=>route($accountsRoute.'portal.store', $accountsRouteParameters + ['account'=>$account->id])])</div>
        </details>
        @endunless
        @forelse($portalUsers as $staffUser)
        <details class="border p-3 mb-3" @if(old('_portal_form') === (string) $staffUser->id)open @endif>
            <summary><span class="fw-semibold">{{ $staffUser->name }} · {{ $staffUser->email }}</span><small class="d-block text-muted mt-1">{{ __('corporate.portal_roles.'.$staffUser->role) }} · {{ __('corporate.states.'.$staffUser->status) }} · {{ __($staffUser->password_set_at ? 'corporate.portal_ready' : 'corporate.portal_pending') }}</small></summary>
            @unless($account->archived_at)
            <div class="mt-3">@include('corporate.portal.staff-form', ['staffAction'=>route($accountsRoute.'portal.update', $accountsRouteParameters + ['account'=>$account->id,'staff'=>$staffUser->id])])
                @if($staffUser->status === 'Active')<form method="POST" action="{{ route($accountsRoute.'portal.invite', $accountsRouteParameters + ['account'=>$account->id,'staff'=>$staffUser->id]) }}">@csrf<button class="btn btn-outline-secondary" type="submit">{{ __('corporate.portal_resend') }}</button></form>@endif
            </div>
            @endunless
        </details>
        @empty<p>{{ __('corporate.portal_no_staff') }}</p>@endforelse {{ $portalUsers->links() }}
    </div></div>
    @if($account->archived_at)<div class="alert alert-info" role="status">{{ __('corporate.account_archived') }}</div>@endif
    @if($exposure['over_credit_limit'])<div class="alert alert-warning" role="status">{{ __('corporate.credit_warning') }}</div>@endif
    <div class="row g-4 mb-4">
        <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.billing_details') }}</h3></div><div class="card-body">
            <dl class="row mb-0">@foreach(['legal_name', 'billing_address', 'contact_name', 'contact_email', 'contact_phone', 'currency_code', 'billing_timezone', 'payment_terms_days'] as $field)<dt class="col-sm-5">{{ __('corporate.'.$field) }}</dt><dd class="col-sm-7 text-break">{{ $account->$field }}</dd>@endforeach<dt class="col-sm-5">{{ __('corporate.billing_cycle') }}</dt><dd class="col-sm-7">{{ __('corporate.cycles.'.$account->billing_cycle) }}</dd></dl>
            @if($account->notes)<p class="text-muted mt-3 mb-0">{{ $account->notes }}</p>@endif
        </div></div></div>
        <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.credit_exposure') }}</h3></div><div class="card-body">
            <dl class="row"><dt class="col-sm-6">{{ __('corporate.outstanding_amount') }}</dt><dd class="col-sm-6 fs-4 fw-semibold">{{ $exposure['currency_code'] }} {{ $exposure['outstanding_amount'] }}</dd><dt class="col-sm-6">{{ __('corporate.credit_limit') }}</dt><dd class="col-sm-6">{{ $exposure['credit_limit_configured'] ? $exposure['currency_code'].' '.$exposure['credit_limit'] : __('corporate.no_credit_limit') }}</dd><dt class="col-sm-6">{{ __('corporate.remaining_credit') }}</dt><dd class="col-sm-6">{{ $exposure['remaining_credit'] === null ? __('corporate.no_credit_limit') : $exposure['currency_code'].' '.$exposure['remaining_credit'] }}</dd></dl>
            <p class="text-muted mb-0">{{ __('corporate.exposure_hint') }}</p>
        </div></div></div>
    </div>
    <div class="card mb-4" id="corporate-members"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.members') }}</h3></div><div class="card-body">
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('corporate.rider') }}</th><th>{{ __('corporate.role') }}</th><th>{{ __('corporate.actions') }}</th></tr></thead><tbody>
            @forelse($members as $member)<tr><td>{{ $member->first_name }} {{ $member->last_name }}<small class="d-block text-muted">{{ $member->email }}</small></td><td>
                @if($account->archived_at){{ __('corporate.roles.'.$member->corporate_role) }}@else
                <form method="POST" action="{{ route($accountsRoute.'members.store', $accountsRouteParameters + ['account' => $account->id]) }}" class="d-flex flex-wrap gap-2">@csrf<input type="hidden" name="user_id" value="{{ $member->id }}"><label class="visually-hidden" for="role-{{ $member->id }}">{{ __('corporate.role') }}</label><select id="role-{{ $member->id }}" name="role" class="form-control w-auto">@foreach(['member', 'manager', 'finance'] as $role)<option value="{{ $role }}" @selected($member->corporate_role === $role)>{{ __('corporate.roles.'.$role) }}</option>@endforeach</select><button type="submit" class="btn btn-outline-primary">{{ __('corporate.save_role') }}</button></form>
                @endif
            </td><td>@unless($account->archived_at)<form method="POST" action="{{ route($accountsRoute.'members.remove', $accountsRouteParameters + ['account' => $account->id, 'rider' => $member->id]) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">{{ __('corporate.remove_member') }}</button></form>@endunless</td></tr>
            @empty<tr><td colspan="3" class="text-muted text-center py-4">{{ __('corporate.no_members') }}</td></tr>@endforelse
        </tbody></table></div>{{ $members->links() }}
        @unless($account->archived_at)
            <h4 class="h6 mt-4">{{ __('corporate.add_member') }}</h4>
            <form method="GET" class="d-flex gap-2 mb-3"><label for="rider_search" class="visually-hidden">{{ __('corporate.search_rider') }}</label><input id="rider_search" name="rider_search" class="form-control" value="{{ request('rider_search') }}" placeholder="{{ __('corporate.search_rider') }}"><button class="btn btn-outline-secondary" type="submit">{{ __('corporate.search') }}</button></form>
            <form method="POST" action="{{ route($accountsRoute.'members.store', $accountsRouteParameters + ['account' => $account->id]) }}" class="row g-3">@csrf
                <div class="col-md-6"><label class="form-label" for="new_member">{{ __('corporate.rider') }}</label><select id="new_member" name="user_id" class="form-control" required><option value="">{{ __('corporate.select_rider') }}</option>@foreach($eligibleRiders as $rider)<option value="{{ $rider->id }}" @selected((string) old('user_id') === (string) $rider->id)>{{ $rider->first_name }} {{ $rider->last_name }} · {{ $rider->email ?: $rider->mobile_number }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label" for="new_role">{{ __('corporate.role') }}</label><select id="new_role" name="role" class="form-control" required>@foreach(['member', 'manager', 'finance'] as $role)<option value="{{ $role }}" @selected(old('role', 'member') === $role)>{{ __('corporate.roles.'.$role) }}</option>@endforeach</select></div>
                <div class="col-md-3 align-self-end"><button type="submit" class="btn btn-primary">{{ __('corporate.add_member') }}</button></div>
            </form><p class="text-muted mt-2">{{ __('corporate.membership_hint') }}</p>{{ $eligibleRiders->links() }}
        @endunless
    </div></div>
    <div class="card mb-4" id="corporate-trips"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.trips') }}</h3></div><div class="card-body">
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('corporate.trip') }}</th><th>{{ __('corporate.route') }}</th><th>{{ __('corporate.status') }}</th><th>{{ __('corporate.billing_status') }}</th><th>{{ __('corporate.amount') }}</th></tr></thead><tbody>
            @forelse($trips as $trip)<tr><td><a href="{{ route($accountsRoute.'trips.show', $accountsRouteParameters + ['account' => $account->id, 'trip' => $trip->id]) }}">{{ __('corporate.trip_charge', ['id' => $trip->id]) }}</a></td><td>{{ $trip->pickup_location }} → {{ $trip->drop_location }}</td><td>{{ __('corporate.states.'.$trip->status) }}</td><td>{{ __('corporate.billing_states.'.($trip->billing_status ?: 'pending')) }}</td><td>{{ $trip->corporate_currency_code }} {{ $trip->corporate_amount }}</td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('corporate.no_trips') }}</td></tr>@endforelse
        </tbody></table></div>{{ $trips->links() }}
    </div></div>
    <div class="card mb-4" id="corporate-invoices"><div class="card-header"><h3 class="h5 mb-0">{{ __('corporate.invoices') }}</h3></div><div class="card-body">
        <form method="POST" action="{{ route($accountsRoute.'invoices.store', $accountsRouteParameters) }}" class="row g-3 mb-4">@csrf<input type="hidden" name="corporate_account_id" value="{{ $account->id }}"><div class="col-md-6"><label for="period_start" class="form-label">{{ __('corporate.period_start') }}</label><input id="period_start" type="date" name="period_start" class="form-control" value="{{ old('period_start') }}" required><small class="text-muted">{{ __('corporate.period_hint', ['cycle' => __('corporate.cycles.'.$account->billing_cycle), 'timezone' => $account->billing_timezone]) }}</small></div><div class="col-md-6 align-self-end"><button class="btn btn-primary" type="submit">{{ __('corporate.generate_invoice') }}</button></div></form>
        @include('saasdispatch::accounts.invoices')
    </div></div>
    @unless($account->archived_at)<div class="card" id="corporate-archive"><div class="card-body"><h3 class="h5">{{ __('corporate.archive_account') }}</h3><p class="text-muted">{{ __('corporate.archive_hint') }}</p><form method="POST" action="{{ route($accountsRoute.'accounts.archive', $accountsRouteParameters + ['account' => $account->id]) }}" data-confirm="{{ __('corporate.archive_confirm') }}" onsubmit="return window.confirm(this.dataset.confirm)">@csrf<button type="submit" class="btn btn-outline-danger">{{ __('corporate.archive_account') }}</button></form></div></div>@endunless
</div>
@endsection
