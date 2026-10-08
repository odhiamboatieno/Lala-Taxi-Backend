@extends('corporate.portal.layout')
@section('title', __('corporate.trips'))
@section('content')
<section class="portal-card"><div class="portal-heading"><div><h2>{{ __('corporate.trips') }}</h2></div><span class="portal-badge">{{ trans_choice('corporate.portal_journey_count', $trips->total(), ['count'=>$trips->total()]) }}</span></div><p class="portal-muted">{{ __('corporate.portal_trips_hint') }}</p>@include('corporate.portal.trip-table')<div class="portal-pagination">{{ $trips->links() }}</div></section>
@endsection
