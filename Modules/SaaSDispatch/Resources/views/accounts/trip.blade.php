@extends('admin.template')
@section('title-sub', __('corporate.trip_charge', ['id' => $trip->id]))
@section('pagetitle', __('corporate.trip_charge', ['id' => $trip->id]))
@section('main')
<div id="layout-wrapper">
    <div class="d-flex justify-content-between flex-wrap gap-3 mb-4"><h2>{{ __('corporate.trip_charge', ['id' => $trip->id]) }}</h2><a class="btn btn-outline-secondary" href="{{ route($accountsRoute.'accounts.show', $accountsRouteParameters + ['account' => $account->id]) }}">{{ __('corporate.back_account') }}</a></div>
    <div class="alert alert-info">{{ __('corporate.no_personal_collection') }}</div>
    <div class="card"><div class="card-body"><dl class="row mb-0">
        <dt class="col-sm-4">{{ __('corporate.account_name') }}</dt><dd class="col-sm-8">{{ data_get($trip->billing_snapshot, 'account_name', $account->account_name) }}</dd>
        <dt class="col-sm-4">{{ __('corporate.route') }}</dt><dd class="col-sm-8">{{ $trip->pickup_location }} → {{ $trip->drop_location }}</dd>
        <dt class="col-sm-4">{{ __('corporate.status') }}</dt><dd class="col-sm-8">{{ __('corporate.states.'.$trip->status) }}</dd>
        <dt class="col-sm-4">{{ __('corporate.completed_at') }}</dt><dd class="col-sm-8">{{ $trip->completed_at?->timezone($account->billing_timezone)->format('Y-m-d H:i') }} · {{ $account->billing_timezone }}</dd>
        <dt class="col-sm-4">{{ __('corporate.billing_status') }}</dt><dd class="col-sm-8">{{ __('corporate.billing_states.'.($trip->billing_status ?: 'pending')) }}</dd>
        <dt class="col-sm-4">{{ __('corporate.amount') }}</dt><dd class="col-sm-8">{{ $trip->corporate_currency_code }} {{ $trip->corporate_amount }}</dd>
        <dt class="col-sm-4">{{ __('corporate.payment_status') }}</dt><dd class="col-sm-8">{{ __('corporate.states.'.$trip->payment_status) }}</dd>
    </dl></div></div>
</div>
@endsection
