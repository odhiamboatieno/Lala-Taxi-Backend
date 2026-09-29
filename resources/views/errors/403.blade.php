@extends('marketing.layout')
@section('title', trans('messages.errors.unauthorize'))
@push('styles')
@include('marketing.partials.legacy-styles')
@endpush
@section('main')
<section class="center-hero">
    <p class="site-eyebrow">{{ trans('messages.errors.oops') }}</p>
    <h1>{{ trans('messages.errors.unauthorize') }}</h1>
    <p class="site-lead">{{ trans('messages.errors.content_block') }}</p>
    <div class="site-actions">
        <a class="site-btn site-btn--gold" href="{{ url('/') }}">{{ __('website.brand_home') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>
        <a class="site-link" href="{{ url('help') }}">{{ __('website.get_help') }}</a>
    </div>
</section>
@endsection
