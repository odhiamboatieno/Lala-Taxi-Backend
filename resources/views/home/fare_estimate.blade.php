@extends('marketing.layout')
@section('title', $site_name.' '.trans('messages.home.fare'))
@section('main')
<section class="center-hero">
	<p class="site-eyebrow">{{ __('website.ride_from_here_to_there') }}</p>
	<h1>{{ $site_name }} {{ trans('messages.home.fare') }}</h1>
	<p class="site-lead">{{ trans('messages.home.how_much') }}{{ $site_name }}{{ trans('messages.home.cost') }}</p>
</section>
<section class="site-section">
	<div class="site-container feature-split feature-split--compact">
		<div class="feature-split__copy estimate-card">
			<h2>{{ trans('messages.home.get_started') }}</h2>
			<div class="position--relative inline-estimate__route-entry" data-reactid="285">
				<div class="site-field">
					<label for="fare_pickup">{{ __('messages.blade_static.enter_pickup_location_ce4420ab') }}</label>
					<input id="fare_pickup" value="" placeholder="{{ __('messages.blade_static.enter_pickup_location_ce4420ab') }}" autocomplete="off" aria-label="{{ __('messages.blade_static.enter_a_pickup_location_address_city_and_state_required_92bd2b8b') }}" data-reactid="297">
					<i class="icon icon_location fare-estimate__location-icon position--absolute" tabindex="0" role="button" aria-label="{{ __('messages.blade_static.locate_me_b89c3714') }}" data-reactid="298"></i>
				</div>
				<div class="fare-estimate__destination-row" data-reactid="299">
					<div class="fare-estimate__destination" data-reactid="300">
						<div class="site-field">
							<label for="fare_destination">{{ __('messages.blade_static.enter_destination_990155df') }}</label>
							<input id="fare_destination" value="" placeholder="{{ __('messages.blade_static.enter_destination_990155df') }}" autocomplete="off" aria-label="{{ __('messages.blade_static.enter_a_destination_address_city_and_state_required_e15d3d5d') }}" data-reactid="305">
						</div>
					</div>
					<button tabindex="-1" class="btn btn--bit position--absolute right transition fare-estimate__button--inactive pointer-events--none" aria-label="{{ __('messages.blade_static.request_estimate_24789618') }}" data-reactid="306"><i class="icon_right-arrow-thin icon" data-reactid="307"></i></button>
				</div>
			</div>
			<div class="site-actions">
				<a class="site-btn site-btn--gold btn--primary btn--arrow position--relative error-retry-btn get-started" href="{{ url('ride') }}">
					<span class="block-context">{{ trans('messages.home.get_started') }}</span>
					<i data-lucide="arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		</div>
		<div class="estimate-map">
			<div id="map_wrapper">
				<div id="map_canvas" class="mapping"></div>
			</div>
		</div>
	</div>
</section>
@endsection
@push('styles')
@include('marketing.partials.legacy-styles')
<style type="text/css">
	.estimate-card .site-field { position: relative; }
	.estimate-card .icon_location { position: absolute; right: 18px; bottom: 18px; cursor: pointer; }
	.estimate-map { border-radius: var(--site-radius-image); overflow: hidden; background: var(--site-surface); }
	.estimate-map #map_wrapper, .estimate-map #map_canvas { width: 100% !important; min-height: 420px; height: 100%; }
</style>
@endpush
@push('scripts')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $map_key }}&libraries=places"></script>
<script type="text/javascript">
	if(document.getElementById("map_canvas")){
		google.maps.event.addDomListener(window, 'load', initialize);
	}

	function initialize() {
		var map;
		var myLatlng = new google.maps.LatLng(51.503454,-0.119562);
		var mapOptions = {
			center: myLatlng,
			zoom: 14,
			mapTypeId: 'roadmap'
		};
		var styles = [{
			"stylers": [
				{ "saturation": -100 },
				{ "lightness": 40 }
			]
		}];
		var styledMap = new google.maps.StyledMapType(styles, {name: "Styled Map"});
		map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
		map.setTilt(45);
		map.mapTypes.set('map_style', styledMap);
		map.setMapTypeId('map_style');
	}
</script>
@endpush
