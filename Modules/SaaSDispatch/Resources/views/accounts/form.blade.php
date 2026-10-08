@extends('admin.template')
@section('title-sub', __($account->exists ? 'corporate.edit_account' : 'corporate.create_account'))
@section('pagetitle', __($account->exists ? 'corporate.edit_account' : 'corporate.create_account'))
@section('main')
<div id="layout-wrapper">
    <div class="d-flex justify-content-between flex-wrap gap-3 mb-4"><h2 class="mb-0">{{ __($account->exists ? 'corporate.edit_account' : 'corporate.create_account') }}</h2><a class="btn btn-outline-secondary" href="{{ route($accountsRoute.'index', $accountsRouteParameters) }}">{{ __('corporate.back_accounts') }}</a></div>
    <div class="card"><div class="card-body">
        @include('saasdispatch::accounts.errors')
        <form method="POST" action="{{ route($accountsRoute.($account->exists ? 'accounts.update' : 'accounts.store'), $accountsRouteParameters + ($account->exists ? ['account' => $account->id] : [])) }}" class="row g-3">
            @csrf
            @if($account->exists)@method('PUT')@endif
            @foreach(['account_name', 'legal_name', 'contact_name', 'contact_email', 'contact_phone'] as $field)
            <div class="col-md-6"><label class="form-label" for="{{ $field }}">{{ __('corporate.'.$field) }}</label><input id="{{ $field }}" class="form-control" name="{{ $field }}" type="{{ $field === 'contact_email' ? 'email' : 'text' }}" value="{{ old($field, $account->$field) }}" maxlength="{{ $field === 'contact_phone' ? 50 : 191 }}" @if(in_array($field, ['account_name', 'legal_name', 'contact_email']))required @endif></div>
            @endforeach
            <div class="col-12"><label class="form-label" for="billing_address">{{ __('corporate.billing_address') }}</label><textarea id="billing_address" name="billing_address" class="form-control" rows="3" maxlength="2000" required>{{ old('billing_address', $account->billing_address) }}</textarea></div>
            <div class="col-md-4"><label class="form-label" for="currency_code">{{ __('corporate.currency_code') }}</label><select id="currency_code" name="currency_code" class="form-control" required><option value="">{{ __('corporate.select_currency') }}</option>@foreach($currencies as $currency)<option value="{{ $currency->code }}" @selected(old('currency_code', $account->currency_code) === $currency->code)>{{ $currency->code }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="billing_timezone">{{ __('corporate.billing_timezone') }}</label><select id="billing_timezone" name="billing_timezone" class="form-control" required>@foreach(\DateTimeZone::listIdentifiers() as $timezone)<option value="{{ $timezone }}" @selected(old('billing_timezone', $account->billing_timezone) === $timezone)>{{ $timezone }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="billing_cycle">{{ __('corporate.billing_cycle') }}</label><select id="billing_cycle" name="billing_cycle" class="form-control" required>@foreach(['weekly', 'monthly', 'quarterly'] as $cycle)<option value="{{ $cycle }}" @selected(old('billing_cycle', $account->billing_cycle) === $cycle)>{{ __('corporate.cycles.'.$cycle) }}</option>@endforeach</select></div>
            <div class="col-12"><p class="text-muted mb-0">{{ __('corporate.immutable_billing_settings') }}</p></div>
            <div class="col-md-4"><label class="form-label" for="payment_terms_days">{{ __('corporate.payment_terms_days') }}</label><input id="payment_terms_days" type="number" name="payment_terms_days" class="form-control" min="0" max="365" value="{{ old('payment_terms_days', $account->payment_terms_days) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="credit_limit">{{ __('corporate.credit_limit') }}</label><input id="credit_limit" name="credit_limit" inputmode="decimal" class="form-control" value="{{ old('credit_limit', $account->credit_limit) }}"><small class="text-muted">{{ __('corporate.credit_hint') }}</small></div>
            <div class="col-md-4"><label class="form-label" for="status">{{ __('corporate.status') }}</label><select id="status" name="status" class="form-control" required>@foreach(['Active', 'Inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $account->status) === $status)>{{ __('corporate.states.'.$status) }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label" for="notes">{{ __('corporate.notes') }}</label><textarea id="notes" name="notes" class="form-control" rows="3" maxlength="1000">{{ old('notes', $account->notes) }}</textarea></div>
            <div class="col-12"><button class="btn btn-primary" type="submit">{{ __('corporate.save_account') }}</button></div>
        </form>
    </div></div>
</div>
@endsection
