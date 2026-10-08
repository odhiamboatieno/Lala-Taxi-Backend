@extends('corporate.portal.layout')
@section('title', __('corporate.portal_overview'))
@section('content')
<section class="portal-card portal-welcome">
    <div class="portal-welcome-copy"><h2>{{ __('corporate.portal_overview') }}</h2><p>{{ __('corporate.portal_overview_hint') }}</p><div class="portal-actions"><span class="portal-badge {{ $account->archived_at ? '' : ($account->status === 'Active' ? 'portal-badge--success' : '') }}"><i data-lucide="{{ $account->archived_at ? 'archive' : 'building-2' }}" aria-hidden="true"></i>{{ __('corporate.states.'.($account->archived_at ? 'Archived' : $account->status)) }}</span><span class="portal-muted">{{ $account->currency_code }} · {{ $account->billing_timezone }}</span></div></div>
    <img class="portal-welcome-photo" src="{{ asset('images/marketing/corporate-hero.webp') }}" alt="{{ __('website.business_photo_alt') }}" width="1400" height="855">
</section>
<div class="portal-metrics">
    <section class="portal-card"><span class="portal-icon"><i data-lucide="users-round" aria-hidden="true"></i></span><strong>{{ $membersCount }}</strong><p>{{ __('corporate.portal_active_members') }}</p></section>
    <section class="portal-card"><span class="portal-icon"><i data-lucide="route" aria-hidden="true"></i></span><strong>{{ $tripsCount }}</strong><p>{{ __('corporate.portal_finalized_trips') }}</p></section>
    <section class="portal-card portal-metric--accent"><span class="portal-icon"><i data-lucide="wallet-cards" aria-hidden="true"></i></span><strong>{{ $exposure['currency_code'] }} {{ $exposure['outstanding_amount'] }}</strong><p>{{ __('corporate.outstanding_amount') }}</p></section>
</div>
<section class="portal-card"><div class="portal-heading"><h2>{{ __('corporate.portal_recent_trips') }}</h2><a class="portal-link" href="{{ route('corporate.portal.trips') }}">{{ __('corporate.portal_view_all') }}<i data-lucide="arrow-right" aria-hidden="true"></i></a></div>@include('corporate.portal.trip-table')</section>
@if($actor->canManageUsers() || $actor->canViewInvoices())
<div class="portal-quicklinks">
    @if($actor->canManageUsers())<a class="portal-quicklink" href="{{ route('corporate.portal.members') }}"><span class="portal-icon"><i data-lucide="users-round" aria-hidden="true"></i></span><span><strong>{{ __('corporate.members') }}</strong><small>{{ __('corporate.portal_members_short') }}</small></span><i data-lucide="arrow-right" aria-hidden="true"></i></a>@endif
    @if($actor->canViewInvoices())<a class="portal-quicklink" href="{{ route('corporate.portal.invoices') }}"><span class="portal-icon"><i data-lucide="receipt-text" aria-hidden="true"></i></span><span><strong>{{ __('corporate.invoices') }}</strong><small>{{ __('corporate.portal_invoices_short') }}</small></span><i data-lucide="arrow-right" aria-hidden="true"></i></a>@endif
</div>
@endif
@endsection
