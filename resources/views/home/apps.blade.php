@extends('marketing.layout')
@section('title', trans('messages.home.title'))
@section('main')
<section class="center-hero" ng-controller="user">
    <p class="site-eyebrow">{{ __('website.get_app') }}</p>
    <h1>{{ trans('messages.home.title') }}</h1>
    <p class="site-lead">{{ trans('messages.home.desc') }}</p>
    <div class="site-actions">
        <a class="site-btn site-btn--gold" href="{{ url('app/rider') }}" data-app-open>{{ __('website.get_app') }} <i data-lucide="arrow-right" aria-hidden="true"></i></a>
        <a class="site-link" href="{{ url('signup_rider') }}">{{ __('website.create_rider_account') }}</a>
    </div>
</section>
@endsection
@push('styles')
@include('marketing.partials.legacy-styles')
<style type="text/css">
    .page-footer-back { background: var(--site-surface); }
</style>
@endpush
@push('scripts')
<script type="text/javascript">
	/**
	 * Determine the mobile operating system.
	 * This function returns one of 'iOS', 'Android', 'Windows Phone', or 'unknown'.
	 *
	 * @returns {String}
	 */
	function getMobileOperatingSystem()
	{
	  	var userAgent = navigator.userAgent || navigator.vendor || window.opera;
	    // Windows Phone must come first because its UA also contains "Android"
	    if (/windows phone/i.test(userAgent)) {
	        return "Windows Phone";
	    }

	    if (/android/i.test(userAgent)) {
	        return "Android";
	    }

	    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
	        return "iOS";
	    }

	    return "unknown";
	}
	os_type = getMobileOperatingSystem();
	if(os_type == 'Android') {
		window.location.href = '{!! $play_store_link !!}';
	}
	else if(os_type == 'iOS') {
		window.location.href = '{!! $app_store_link !!}';
	}
	else {
		window.location.href = '{!! url("/") !!}';
	}
</script>
@endpush
