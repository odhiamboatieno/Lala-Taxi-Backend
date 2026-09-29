@extends('marketing.layout')
@section('title', trans('messages.user.disabled') . ' · ' . $site_name)

@section('main')
<section class="center-hero">
    <p class="site-eyebrow">{{ $site_name }}</p>
    <h1>{{trans('messages.user.disabled')}}</h1>
    <p class="site-lead">{{trans('messages.user.protect')}}</p>
    <div class="site-actions" style="justify-content:center;">
        <form action="mailto:{{ $admin_email }}" method="GET">
            <button class="site-btn site-btn--gold search-button form-inline btn btn-primary btn-large">
                <i data-lucide="mail" aria-hidden="true"></i>
                {{trans('messages.user.email_us')}}
            </button>
        </form>
    </div>
</section>

@include('marketing.partials.city')
@endsection
