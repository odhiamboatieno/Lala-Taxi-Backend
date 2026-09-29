@extends('marketing.layout')
@section('title', trans('messages.header.signin-delete') . ' · ' . $site_name)

@section('main')
<section class="center-hero">
    <h1>{{trans('messages.header.signin-delete')}}</h1>
</section>

<section class="site-section account-section">
    <div class="site-container editorial-grid">
        @if(Auth::user()==null)
        <a class="account-choice" href="{{ url('rider_deletion') }}">
            <i data-lucide="user-round-x" aria-hidden="true"></i>
            <h2>{{trans('messages.profile.rider')}}</h2>
            <p>{{trans('messages.profile.delete_rider')}}</p>
            <span>{{trans('messages.profile.driver_proceed')}} <i data-lucide="arrow-right" aria-hidden="true"></i></span>
        </a>
        <a class="account-choice" href="{{ url('driver_deletion') }}">
            <i data-lucide="car-front" aria-hidden="true"></i>
            <h2>{{trans('messages.profile.driver')}}</h2>
            <p>{{trans('messages.profile.delete_driver')}}</p>
            <span>{{trans('messages.profile.driver_proceed')}} <i data-lucide="arrow-right" aria-hidden="true"></i></span>
        </a>
        @endif
    </div>
</section>

@include('marketing.partials.city')
@endsection
