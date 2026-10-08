@extends('corporate.portal.layout')
@section('content')<section class="portal-card"><h2>{{ __('corporate.trips') }}</h2>@include('corporate.portal.trip-table')<div class="portal-pagination">{{ $trips->links() }}</div></section>@endsection
