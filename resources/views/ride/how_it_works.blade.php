@extends('marketing.layout')
@section('title', trans('messages.ride.how_works', ['site_name' => $site_name]))
@push('styles')
@include('marketing.partials.legacy-styles')
@endpush
@section('main')
<section class="split-hero">
	<div class="split-hero__copy">
		<p class="site-eyebrow">{{ trans('messages.ride.ride_with_Cabme', ['site_name' => $site_name]) }}</p>
		<h1>{{ trans('messages.ride.how_works', ['site_name' => $site_name]) }}</h1>
		<p class="site-lead">{{ trans('messages.ride.start_riding_content') }}</p>
		@if(Auth::user()==null)
		<div class="site-actions">
			<a class="site-btn site-btn--gold" href="{{ url('signup_rider') }}">{{ trans('messages.home.siginup') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>
		</div>
		@endif
	</div>
	<img class="split-hero__image" src="{{ asset('images/marketing/driver-window.webp') }}" alt="{{ __('website.home_driver_alt') }}" width="1300" height="1000" fetchpriority="high">
</section>
<section class="service-band">
	<div class="site-container">
		<p class="site-eyebrow">{{ __('website.ride_from_here_to_there') }}</p>
		<h2>{{ trans('messages.ride.what_expect') }}</h2>
		<div class="service-grid">
			<article class="service-item"><i data-lucide="map-pin" aria-hidden="true"></i><h3>{{ trans('messages.ride.request') }}</h3><p>{{ trans('messages.ride.request_content') }}</p></article>
			<article class="service-item"><i data-lucide="car-front" aria-hidden="true"></i><h3>{{ trans('messages.ride.delivery') }}</h3><p>{{ trans('messages.ride.delivery_content') }}</p></article>
			<article class="service-item"><i data-lucide="wallet" aria-hidden="true"></i><h3>{{ trans('messages.ride.pay_go') }}</h3><p>{{ trans('messages.ride.pay_go_content') }}</p></article>
		</div>
	</div>
</section>
<section class="site-section">
	<div class="site-container">
		<div class="section-heading">
			<p class="site-eyebrow">{{ trans('messages.ride.faq') }}</p>
			<h2>{{ trans('messages.ride.what_expect') }}</h2>
		</div>
		<div class="faq">
			<details>
				<summary>{{ trans('messages.ride.make_reserve') }}</summary>
				<p>{{ trans('messages.ride.make_reserve_content', ['site_name' => $site_name]) }}</p>
				<ol class="order-list">
					<li>{{ trans('messages.ride.schedule_ride') }}</li>
					<li>{{ trans('messages.ride.select_date') }}</li>
					<li>{{ trans('messages.ride.select_pickup_location') }}</li>
					<li>{{ trans('messages.ride.review_fare') }}</li>
					<li>{{ trans('messages.ride.after_confirm') }}</li>
				</ol>
			</details>
			<details>
				<summary>{{ trans('messages.ride.do_i_need') }}</summary>
				<p>{{ trans('messages.ride.do_i_need_content', ['site_name' => $site_name]) }}</p>
			</details>
			<details>
				<summary>{{ trans('messages.ride.cancel_request') }}</summary>
				<p>{{ trans('messages.ride.cancel_request_content', ['site_name' => $site_name]) }}</p>
			</details>
		</div>
	</div>
</section>
<section class="site-section gold-section">
	<div class="site-container editorial-grid">
		<article class="editorial-card">
			<i data-lucide="car-front" aria-hidden="true"></i>
			<h3>{{ trans('messages.ride.start_riding') }}</h3>
			<p>{{ trans('messages.ride.start_riding_content') }}</p>
			@if(Auth::user()==null)
			<div class="site-actions"><a class="site-btn site-btn--gold" href="{{ url('signup_rider') }}">{{ trans('messages.footer.siginup_ride') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a></div>
			@endif
		</article>
		<article class="editorial-card">
			<i data-lucide="user" aria-hidden="true"></i>
			<h3>{{ trans('messages.ride.already_have_account') }}</h3>
			<p>{{ trans('messages.ride.invite', ['site_name' => $site_name]) }}</p>
			<a class="site-link" href="{{ url('signin_rider') }}">{{ __('website.sign_in') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>
		</article>
	</div>
</section>
@include('marketing.partials.city')
@endsection
