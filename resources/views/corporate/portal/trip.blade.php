@extends('corporate.portal.layout')
@section('title', __('corporate.trip'))
@section('content')
<a class="portal-link" href="{{ route('corporate.portal.trips') }}"><i data-lucide="arrow-left" aria-hidden="true"></i>{{ __('corporate.trips') }}</a>
<section class="portal-card">
    <div class="portal-heading"><h2>{{ __('corporate.trip') }} #{{ $trip->id }}</h2><span class="portal-badge {{ in_array($trip->getRawOriginal('status'), ['Completed','completed']) ? 'portal-badge--success' : '' }}">{{ __('corporate.states.'.$trip->getRawOriginal('status')) }}</span></div>
    <div class="portal-route"><span><i data-lucide="circle-dot" aria-hidden="true"></i>{{ $trip->pickup_location }}</span><span><i data-lucide="map-pin" aria-hidden="true"></i>{{ $trip->drop_location }}</span></div>
    <dl class="portal-pairs"><div><dt>{{ __('corporate.portal_passenger') }}</dt><dd>{{ trim(($trip->users?->first_name ?? '').' '.($trip->users?->last_name ?? '')) }}</dd></div><div><dt>{{ __('corporate.billing_status') }}</dt><dd>{{ __('corporate.billing_states.'.$trip->billing_status) }}</dd></div><div><dt>{{ __('corporate.completed_at') }}</dt><dd>{{ $trip->completed_at?->timezone($account->billing_timezone)?->format('d M Y H:i') }} {{ $account->billing_timezone }}</dd></div></dl>
    <div class="portal-total"><span>{{ __('corporate.amount') }}</span><strong>{{ $trip->corporate_currency_code ?? $account->currency_code }} {{ $trip->corporate_amount ?? __('corporate.billing_states.pending') }}</strong></div>
    <div class="portal-help"><i data-lucide="building-2" aria-hidden="true"></i><p>{{ __('corporate.no_personal_collection') }}</p></div>
</section>
@endsection
