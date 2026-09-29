@extends('marketing.layout')
@section('title', 'Help | '.$site_name)
@section('main')
<section class="center-hero">
    <p class="site-eyebrow">{{ __('website.help_support') }}</p>
    <h1>{{ trans('messages.help.welcome') }}</h1>
    <p class="site-lead">{{ __('website.help_centre') }}</p>
    <div class="site-actions">
        <a class="site-btn site-btn--gold" href="{{ url('help') }}">{{ trans('messages.help.help_center') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>
    </div>
</section>
@endsection
