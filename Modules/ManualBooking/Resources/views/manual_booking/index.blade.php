@extends('admin.template')

@section('title-sub', 'Manual Booking')
@section('pagetitle', 'Manual Booking')

@section('css')
<link rel="stylesheet" href="{{ asset('modules/manualbooking/css/manual-booking.css') }}">
@endsection

@section('main')
<div id="layout-wrapper" class="manual-booking-page">
	@php
		$ride = optional($schedule_ride);
		$rideUser = optional($ride->users);
		$dispatchCountry = $default_dispatch_country ?? $country_code_option[0] ?? null;
		$rideCountryCode = $rideUser->country_code ?? optional($dispatchCountry)->phone_code;
		$rideCountryId = $rideUser->country_id ?? optional($dispatchCountry)->id;
		$rideCountryName = optional($dispatchCountry)->long_name ?: ($manual_booking_defaults['country_name'] ?? 'Default Country');
	@endphp
	<script>
		window.MANUAL_BOOKING_DEFAULTS = @json($manual_booking_defaults ?? []);
		window.MANUAL_BOOKING_PAYLOAD = @json($manual_booking_payload ?? []);
	</script>
	<div class="dispatch-console-shell manual-workstation" ng-controller='manual_booking' ng-init='vehicle_types = @json($vehicle_types)'>
		{!! Form::open(['method'=>'POST','url' => LOGIN_USER_TYPE.'/manual_booking/store', 'class' => 'manual_booking','id'=>'manual_booking','name'=>'myForm']) !!}
		{!! Form::hidden('manual_booking_id', $ride->id, ['id' => 'manual_booking_id']) !!}
		{!! Form::hidden('auto_assign_id', $ride->driver_id, ['id' => 'auto_assign_id']) !!}
		{!! Form::hidden('utc_offset', '', ['id' => 'utc_offset']) !!}
		{!! Form::hidden('location_id', $ride->location_id, ['id' => 'location_id']) !!}
		{!! Form::hidden('peak_id', $ride->peak_id, ['id' => 'peak_id']) !!}
		{!! Form::hidden('user_id','',['id'=>'user_id']) !!}
		{!! Form::hidden('booking_mode', $ride->booking_mode ?: 'asap', ['id' => 'booking_mode']) !!}
		{!! Form::hidden('dispatch_mode', $ride->dispatch_mode ?: (($ride->driver_id ?? 0) ? 'manual' : 'auto'), ['id' => 'dispatch_mode']) !!}
		{!! Form::hidden('assign_now', old('assign_now', $ride->assign_now ? 1 : 0), ['id' => 'assign_now']) !!}
		{!! Form::hidden('is_dispatch_override', old('is_dispatch_override', $ride->is_dispatch_override ? 1 : 0), ['id' => 'is_dispatch_override']) !!}
		{!! Form::hidden('booking_experience_tab', old('booking_experience_tab', $manual_booking_payload['booking_experience_tab'] ?? 'asap'), ['id' => 'booking_experience_tab']) !!}
		{!! Form::hidden('capabilities', json_encode($manual_booking_payload['capabilities'] ?? []), ['id' => 'booking_capabilities']) !!}
		{!! Form::hidden('stay_on_console', old('stay_on_console', 1), ['id' => 'stay_on_console']) !!}
		{!! Form::hidden('draft_id', old('draft_id', optional($ride)->draft_id), ['id' => 'draft_id']) !!}
		{!! Form::hidden('template_id', old('template_id', optional($ride)->template_id), ['id' => 'template_id']) !!}
		{!! Form::hidden('return_trip_enabled', old('return_trip_enabled', data_get($manual_booking_payload, 'return_trip.enabled') ? 1 : 0), ['id' => 'return_trip_enabled']) !!}
		{!! Form::hidden('return_date', old('return_date', data_get($manual_booking_payload, 'return_trip.date')), ['id' => 'return_date']) !!}
		{!! Form::hidden('return_time', old('return_time', data_get($manual_booking_payload, 'return_trip.time')), ['id' => 'return_time']) !!}
		{!! Form::hidden('return_pickup_location', old('return_pickup_location', data_get($manual_booking_payload, 'return_trip.pickup_location')), ['id' => 'return_pickup_location']) !!}
		{!! Form::hidden('return_pickup_latitude', old('return_pickup_latitude'), ['id' => 'return_pickup_latitude']) !!}
		{!! Form::hidden('return_pickup_longitude', old('return_pickup_longitude'), ['id' => 'return_pickup_longitude']) !!}
		{!! Form::hidden('return_drop_location', old('return_drop_location', data_get($manual_booking_payload, 'return_trip.drop_location')), ['id' => 'return_drop_location']) !!}
		{!! Form::hidden('return_drop_latitude', old('return_drop_latitude'), ['id' => 'return_drop_latitude']) !!}
		{!! Form::hidden('return_drop_longitude', old('return_drop_longitude'), ['id' => 'return_drop_longitude']) !!}
		{!! Form::hidden('recurring_enabled', old('recurring_enabled', data_get($manual_booking_payload, 'recurring.enabled') ? 1 : 0), ['id' => 'recurring_enabled']) !!}

		<div class="manual-shell-reference">
			<div class="manual-shell-header">
				<div>
					<h1>Manual Booking</h1>
					<p>Create, schedule and dispatch a new booking manually.</p>
				</div>
				<div class="manual-shell-actions">
					<div class="dropdown">
						<button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-lightning-charge"></i>
							Quick Actions
						</button>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#manualBookingQuickActionsModal">Quick Actions</button></li>
							<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#manualBookingDraftModal">Save Draft</button></li>
							<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#manualBookingTemplateModal">Save Template</button></li>
						</ul>
					</div>
					<button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#manualBookingDraftModal">Save Draft</button>
					<button type="button" class="btn btn-primary submit_button change_field manual-toolbar-primary" id="manual-header-submit" disabled ng-disabled="myForm.$invalid && page_loading==0" ng-click="submitForm($event);">Book Now <i class="bi bi-arrow-right-short"></i></button>
				</div>
			</div>

			<div class="manual-shell-tabs" id="workspace-booking-tabs">
				<button type="button" class="workspace-tab active" data-booking-tab="asap"><i class="bi bi-car-front manual-tab-icon"></i>ASAP</button>
				<button type="button" class="workspace-tab" data-booking-tab="later"><i class="bi bi-calendar-check manual-tab-icon"></i>Later</button>
				<button type="button" class="workspace-tab" data-booking-tab="advanced"><i class="bi bi-sliders manual-tab-icon"></i>Advanced</button>
				<button type="button" class="workspace-tab" data-booking-tab="recurring"><i class="bi bi-arrow-repeat manual-tab-icon"></i>Recurring</button>
				<button type="button" class="workspace-tab" data-booking-tab="airport_transfer"><i class="bi bi-airplane manual-tab-icon"></i>Airport Transfer</button>
			</div>

			<div class="alert alert-danger dispatch-form-alert d-none" id="dispatch-form-alert">
				<strong>Form attention needed.</strong> Please review the highlighted booking fields before submitting.
			</div>

			<div class="manual-layout-grid">
				<div class="manual-left-stage">
					<div class="manual-card manual-later-booking-time" data-workspace-panel="later">
						<div class="manual-card-head">
							<div class="manual-card-title"><span class="manual-step-number">1</span>Booking Time</div>
						</div>
						<div class="manual-inline-grid four">
							<div>
								<label class="form-label" for="later_pickup_date">Pickup Date</label>
								<input type="date" class="form-control" id="later_pickup_date" name="later_pickup_date" value="{{ old('later_pickup_date', $ride->schedule_date) }}">
							</div>
							<div>
								<label class="form-label" for="later_pickup_time">Pickup Time</label>
								<input type="time" class="form-control" id="later_pickup_time" name="later_pickup_time" value="{{ old('later_pickup_time', $ride->schedule_time) }}">
							</div>
							<div>
								<label class="form-label" for="later_repeat">Repeat</label>
								<select class="form-select" id="later_repeat" name="later_repeat">
									<option value="none">Does not repeat</option>
									<option value="daily">Daily</option>
									<option value="weekly">Weekly</option>
									<option value="monthly">Monthly</option>
								</select>
							</div>
							<div>
								<label class="form-label" for="later_prebooking_window">Pre-booking Window</label>
								<select class="form-select" id="later_prebooking_window" name="later_prebooking_window">
									<option value="30">30 min before pickup</option>
									<option value="45">45 min before pickup</option>
									<option value="60">60 min before pickup</option>
									<option value="120">2 hrs before pickup</option>
								</select>
							</div>
						</div>
					</div>

					<div class="manual-top-grid">
						<div class="manual-card manual-utility-card" data-workspace-panel="advanced">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">1</span>Booking Details</div>
							</div>
							<div class="manual-form-stack">
								<div>
									<label class="form-label" for="advanced_booking_type">Booking Type</label>
									<select class="form-select" id="advanced_booking_type" name="advanced_booking_type">
										<option value="one_way">One Way</option>
										<option value="return">Return</option>
										<option value="multi_stop">Multi Stop</option>
									</select>
								</div>
								<div class="manual-inline-grid two" data-workspace-panel="asap">
									<div>
										<label class="form-label" for="advanced_pickup_date">Pickup Date</label>
										<input type="date" class="form-control" id="advanced_pickup_date" name="advanced_pickup_date" value="{{ old('advanced_pickup_date', $ride->schedule_date) }}">
									</div>
									<div>
										<label class="form-label" for="advanced_pickup_time">Pickup Time</label>
										<input type="time" class="form-control" id="advanced_pickup_time" name="advanced_pickup_time" value="{{ old('advanced_pickup_time', $ride->schedule_time) }}">
									</div>
								</div>
								<div>
									<label class="form-label" for="advanced_time_flexibility">Time Flexibility <small>(Optional)</small></label>
									<select class="form-select" id="advanced_time_flexibility" name="advanced_time_flexibility">
										<option value="15_before">15 min before</option>
										<option value="15_after">15 min after</option>
										<option value="30_window">30 min window</option>
										<option value="exact">Exact time only</option>
									</select>
								</div>
								<div>
									<label class="form-label" for="advanced_booking_reference">Booking Reference <small>(Optional)</small></label>
									<input type="text" class="form-control" id="advanced_booking_reference" name="advanced_booking_reference" placeholder="Enter booking reference">
								</div>
							</div>
						</div>

						<div class="manual-card manual-utility-card" data-workspace-panel="recurring">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">1</span>Recurring Details</div>
							</div>
							<div class="manual-form-stack">
								<div>
									<label class="form-label" for="recurring_type">Recurrence Type</label>
									<select class="form-select" id="recurring_type" name="recurring_type">
										<option value="daily">Daily</option>
										<option value="weekly">Weekly</option>
										<option value="monthly">Monthly</option>
									</select>
								</div>
								<div class="manual-inline-grid two">
									<div>
										<label class="form-label" for="recurring_start_date">Start Date</label>
										<input type="date" class="form-control" id="recurring_start_date" name="recurring_start_date" value="{{ old('recurring_start_date', $ride->schedule_date) }}">
									</div>
									<div>
										<label class="form-label" for="recurring_start_time">Start Time</label>
										<input type="time" class="form-control" id="recurring_start_time" name="recurring_start_time" value="{{ old('recurring_start_time', $ride->schedule_time) }}">
									</div>
								</div>
								<div>
									<label class="form-label" for="recurring_end_option">End</label>
									<select class="form-select" id="recurring_end_option" name="recurring_end_option">
										<option value="no_end">No End Date</option>
										<option value="after_10">After 10 Occurrences</option>
										<option value="by_date">End By Date</option>
									</select>
								</div>
								<div class="manual-summary-preview" id="recurring-summary-preview">
									Occurs every day at 08:30 AM<br>Starting 20 Nov, 2023
								</div>
							</div>
						</div>

						<div class="manual-card manual-utility-card" data-workspace-panel="airport_transfer">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">1</span>Transfer Details</div>
							</div>
							<div class="manual-form-stack">
								<div>
									<label class="form-label">Transfer Type</label>
									<div class="manual-airport-toggle">
										<button type="button" class="active" data-airport-direction="pickup_from_airport">Airport Pickup</button>
										<button type="button" data-airport-direction="drop_to_airport">Airport Drop-off</button>
									</div>
								</div>
								<div>
									<label class="form-label" for="flight_number">Flight Number <small>(Optional)</small></label>
									<input type="text" class="form-control" id="flight_number" name="flight_number" value="{{ old('flight_number', data_get($manual_booking_payload, 'flight_number')) }}" placeholder="KQ 201">
									<div class="manual-field-help">Helps driver track your flight</div>
								</div>
								<div class="manual-inline-grid two">
									<div>
										<label class="form-label" for="airport_flight_date">Flight Date</label>
										<input type="date" class="form-control" id="airport_flight_date" name="airport_flight_date" value="{{ old('airport_flight_date', $ride->schedule_date) }}">
									</div>
									<div>
										<label class="form-label" for="airport_flight_time">Flight Time</label>
										<input type="time" class="form-control" id="airport_flight_time" name="airport_flight_time" value="{{ old('airport_flight_time', $ride->schedule_time) }}">
									</div>
								</div>
								<div>
									<label class="form-label" for="terminal">Terminal <small>(Optional)</small></label>
									<select class="form-select" id="terminal" name="terminal">
										<option value="Terminal 1A">Terminal 1A</option>
										<option value="Terminal 1B">Terminal 1B</option>
										<option value="Terminal 1C">Terminal 1C</option>
										<option value="Terminal 2">Terminal 2</option>
									</select>
								</div>
								<div>
									<label class="form-label" for="pickup_buffer_time">Pickup Buffer Time</label>
									<select class="form-select" id="pickup_buffer_time" name="pickup_buffer_time">
										<option value="60_after_landing">60 min after landing</option>
										<option value="45_after_landing">45 min after landing</option>
										<option value="30_after_landing">30 min after landing</option>
									</select>
									<div class="manual-field-help">Driver will be assigned after buffer time</div>
								</div>
							</div>
						</div>
						<div class="manual-card" id="manual-pickup-card">
							<div class="manual-card-head">
								<div class="manual-card-title" id="manual-pickup-card-title"><span class="manual-step-number">1</span>Pickup Details</div>
							</div>
							<div class="manual-form-stack">
								<div class="manual-inline-grid two">
									<div ng-init='date_time = "{{$ride->schedule_date}} {{$ride->schedule_time}}"'>
										<label class="form-label" for="input_date_time">Pickup Time</label>
										{!! Form::text('date_time',($ride->schedule_date==null)?'':$ride->schedule_date.' '.$ride->schedule_time, ['class' => 'form-control change_field', 'id' => 'input_date_time', 'placeholder' => 'Select Date / Time','ng-cloak','disabled'=>isset($schedule_ride->id)?false:true]) !!}
									</div>
									<div>
										<label class="form-label">Mode</label>
										<div class="dispatch-mode-pills" id="dispatch-booking-mode-pills">
											<button type="button" class="dispatch-mode-pill {{ (($ride->booking_mode ?: 'asap') === 'asap') ? 'active' : '' }}" data-booking-mode="asap">ASAP</button>
											<button type="button" class="dispatch-mode-pill {{ ($ride->booking_mode === 'scheduled') ? 'active' : '' }}" data-booking-mode="scheduled">Later</button>
										</div>
									</div>
								</div>
								<div ng-init='pickup_latitude = "{{$ride->pickup_latitude}}"' data-workspace-panel="asap later advanced recurring">
									<label class="form-label" for="input_pickup_location">Pickup Location</label>
									{!! Form::hidden('pickup_latitude', $ride->pickup_latitude, ['id' => 'pickup_latitude']) !!}
									{!! Form::hidden('pickup_longitude', $ride->pickup_longitude, ['id' => 'pickup_longitude']) !!}
									{!! Form::text('pickup_location', $ride->pickup_location, ['class' => 'form-control change_field', 'id' => 'input_pickup_location', 'placeholder' => 'Pickup location', 'autocomplete' => 'off']) !!}
								</div>
								<div data-workspace-panel="airport_transfer" style="display:none;">
									<label class="form-label" for="airport_name">Airport</label>
									<input type="text" class="form-control" id="airport_name" name="airport_name" value="{{ old('airport_name', 'Jomo Kenyatta International Airport (NBO)') }}">
								</div>
								<div data-workspace-panel="airport_transfer" style="display:none;">
									<label class="form-label" for="airport_pickup_terminal">Terminal</label>
									<select class="form-select" id="airport_pickup_terminal" name="airport_pickup_terminal">
										<option value="Terminal 1A">Terminal 1A</option>
										<option value="Terminal 1B">Terminal 1B</option>
										<option value="Terminal 1C">Terminal 1C</option>
									</select>
								</div>
								<div data-workspace-panel="airport_transfer" style="display:none;">
									<label class="form-label" for="airport_meeting_point">Pickup Location / Meeting Point</label>
									<select class="form-select" id="airport_meeting_point" name="airport_meeting_point">
										<option value="Arrivals Hall - International">Arrivals Hall - International</option>
										<option value="Terminal Exit">Terminal Exit</option>
										<option value="Parking Bay">Parking Bay</option>
									</select>
									<div class="manual-field-help">Driver will wait at the meeting point</div>
								</div>
								<div data-workspace-panel="asap">
									<label class="form-label" for="input_pickup_note">Pickup Notes</label>
									<input type="text" class="form-control" id="input_pickup_note" placeholder="Add note for driver">
								</div>
								<div class="manual-via-row">
									<button type="button" class="manual-link-button" id="dispatch-add-via"><i class="bi bi-plus-circle"></i> Add via location</button>
								</div>
								<div class="dispatch-via-panel {{ old('via_location', $ride->via_location) ? 'active' : '' }}" id="dispatch-via-panel">
									{!! Form::hidden('via_latitude', old('via_latitude', $ride->via_latitude), ['id' => 'via_latitude']) !!}
									{!! Form::hidden('via_longitude', old('via_longitude', $ride->via_longitude), ['id' => 'via_longitude']) !!}
									<div class="manual-inline-grid auto-via">
										<div>
											<label class="form-label" for="input_via_location">Via</label>
											{!! Form::text('via_location', old('via_location', $ride->via_location), ['class' => 'form-control change_field', 'id' => 'input_via_location', 'placeholder' => 'Add via stop', 'autocomplete' => 'off']) !!}
										</div>
										<button type="button" class="manual-icon-button" id="dispatch-remove-via"><i class="bi bi-trash"></i></button>
									</div>
								</div>
								<div data-workspace-panel="later advanced recurring airport_transfer" style="display:none;">
									<label class="form-label" for="input_pickup_instruction">Pickup Instructions <small>(Optional)</small></label>
									<textarea class="form-control" id="input_pickup_instruction" name="input_pickup_instruction" placeholder="Any special instructions for driver..."></textarea>
									<div class="manual-field-counter">0/200</div>
								</div>
							</div>
						</div>

						<div class="manual-card" id="manual-destination-card">
							<div class="manual-card-head">
								<div class="manual-card-title" id="manual-destination-card-title"><span class="manual-step-number">2</span>Destination Details</div>
							</div>
							<div class="manual-form-stack">
								<div>
									<label class="form-label" for="input_drop_location">Destination</label>
									{!! Form::hidden('drop_latitude', $ride->drop_latitude, ['id' => 'drop_latitude']) !!}
									{!! Form::hidden('drop_longitude', $ride->drop_longitude, ['id' => 'drop_longitude']) !!}
									<div class="manual-inline-grid auto-via">
										{!! Form::text('drop_location', $ride->drop_location, ['class' => 'form-control change_field', 'id' => 'input_drop_location', 'placeholder' => 'Destination', 'autocomplete' => 'off']) !!}
										<button type="button" class="manual-icon-button" id="dispatch-swap-route"><i class="bi bi-arrow-left-right"></i></button>
									</div>
								</div>
								<div data-workspace-panel="asap">
									<label class="form-label" for="dispatch-driver-note">Note</label>
									<textarea class="form-control" id="dispatch-driver-note" name="dispatcher_note" placeholder="Add note for driver">{{ old('dispatcher_note', $ride->dispatcher_note) }}</textarea>
								</div>
								<div data-workspace-panel="later airport_transfer advanced recurring" style="display:none;">
									<label class="form-label" for="destination_via_clone">Via / Stops <small>(Optional)</small></label>
									<div class="manual-inline-grid auto-via">
										<input type="text" class="form-control sync-proxy" id="destination_via_clone" data-sync-target="input_via_location" value="{{ old('via_location', $ride->via_location) }}" placeholder="Add via location">
										<button type="button" class="manual-icon-button"><i class="bi bi-geo-alt"></i></button>
									</div>
								</div>
								<div data-workspace-panel="later airport_transfer advanced recurring" style="display:none;">
									<label class="form-label" for="dispatch-driver-note-advanced">Destination Instructions <small>(Optional)</small></label>
									<textarea class="form-control sync-proxy" id="dispatch-driver-note-advanced" data-sync-target="dispatch-driver-note" placeholder="Any special instructions for driver...">{{ old('dispatcher_note', $ride->dispatcher_note) }}</textarea>
									<div class="manual-field-counter">0/200</div>
								</div>
								<div data-workspace-panel="airport_transfer" style="display:none;">
									<input type="hidden" id="airport_direction" name="airport_direction" value="{{ data_get($manual_booking_payload, 'airport_direction', 'pickup_from_airport') }}">
								</div>
							</div>
						</div>

						<div class="manual-card" id="manual-passenger-card">
							<div class="manual-card-head">
								<div class="manual-card-title" id="manual-passenger-card-title"><span class="manual-step-number">3</span>Passenger Details</div>
							</div>
							<div class="manual-form-stack">
								<div class="cls_manualbook" ng-init="country_code = '{{ $rideCountryCode }}'">
									{!! Form::hidden('country_code', $rideCountryCode, ['id'=>'input_country_code']) !!}
									{!! Form::hidden('country_id', $rideCountryId, ['id'=>'country_id']) !!}
								</div>
								<div>
									<label class="form-label" for="input_first_name">Name</label>
									{!! Form::text('first_name', $rideUser->first_name, ['class' => 'form-control', 'id' => 'input_first_name', 'placeholder' => 'Passenger name', 'autocomplete' => 'off']) !!}
								</div>
								<div>
									<label class="form-label" for="input_last_name">Last Name</label>
									{!! Form::text('last_name', $rideUser->last_name, ['class' => 'form-control', 'id' => 'input_last_name', 'placeholder' => 'Last name', 'autocomplete' => 'off']) !!}
								</div>
								<div data-workspace-panel="advanced airport_transfer recurring" style="display:none;">
									<label class="form-label" for="company_name">Company</label>
									<input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name', data_get($manual_booking_payload, 'company_name')) }}" placeholder="Company name">
								</div>
								<div>
									<label class="form-label" for="input_mobile_number">Phone</label>
									<div class="dispatch-split-phone number-field">
										<div class="dispatch-phone-prefix">
											<input type="text" disabled name="country_code_view" class='form-control text-center border-0 bg-transparent p-0 m-0' id='country_code_view'>
										</div>
										<div>
											{!! Form::text('mobile_number', $rideUser->mobile_number, ['class' => 'form-control', 'id' => 'input_mobile_number', 'placeholder' => 'Phone number', 'autocomplete' => 'off']) !!}
										</div>
									</div>
								</div>
								<div>
									<label class="form-label" for="input_email">Email</label>
									{!! Form::text('email', $rideUser->email, ['class' => 'form-control', 'id' => 'input_email', 'placeholder' => 'john@example.com', 'autocomplete' => 'off']) !!}
								</div>
								<div>
									<label class="form-label" for="input_gender">Gender</label>
									<select name="gender" class="form-select" id="input_gender" ng-model="gender">
										<option value="">Gender</option>
										<option value="1">Male</option>
										<option value="2">Female</option>
									</select>
								</div>
								<div id="manual-passenger-inline-capabilities" data-workspace-panel="asap later advanced recurring">
									<label class="form-label">Capabilities</label>
									<div class="manual-capability-grid">
										<button type="button" class="manual-capability-tile active" data-capability="wheelchair"><i class="bi bi-person-wheelchair"></i> Wheelchair</button>
										<button type="button" class="manual-capability-tile" data-capability="standard_passenger"><i class="bi bi-person"></i> Passenger</button>
										<button type="button" class="manual-capability-tile" data-capability="verified_passenger"><i class="bi bi-person-check"></i> Verified</button>
										<button type="button" class="manual-capability-tile" data-capability="business_luggage"><i class="bi bi-briefcase"></i> Business</button>
										<button type="button" class="manual-capability-tile" data-capability="care_assistance"><i class="bi bi-person-hearts"></i> Assistance</button>
									</div>
								</div>
								<div data-workspace-panel="airport_transfer" style="display:none;">
									<label class="form-label">Capabilities / Needs</label>
									<div class="manual-capability-grid airport">
										<button type="button" class="manual-capability-tile active" data-capability="airport_passenger"><i class="bi bi-person-badge"></i> Passenger</button>
										<button type="button" class="manual-capability-tile" data-capability="wheelchair"><i class="bi bi-person-wheelchair"></i> Wheelchair</button>
										<button type="button" class="manual-capability-tile" data-capability="child_seat"><i class="bi bi-car-front"></i> Child Seat</button>
										<button type="button" class="manual-capability-tile" data-capability="extra_luggage"><i class="bi bi-bag"></i> Extra Luggage</button>
									</div>
								</div>
							</div>
						</div>

						<div class="manual-card" id="manual-capabilities-card">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">4</span>Capabilities</div>
							</div>
							<div class="manual-capability-grid">
								<button type="button" class="manual-capability-tile active" data-capability="wheelchair"><i class="bi bi-person-wheelchair"></i> Wheelchair</button>
								<button type="button" class="manual-capability-tile" data-capability="child_seat"><i class="bi bi-car-front"></i> Child Seat</button>
								<button type="button" class="manual-capability-tile" data-capability="female_driver"><i class="bi bi-person-check"></i> Female Driver</button>
								<button type="button" class="manual-capability-tile" data-capability="extra_luggage"><i class="bi bi-bag"></i> Extra Luggage</button>
								<button type="button" class="manual-capability-tile" data-capability="meet_and_greet"><i class="bi bi-person-badge"></i> Meet & Greet</button>
							</div>
						</div>
					</div>

					<div class="manual-driver-board">
						<div class="manual-board-head">
							<div>
								<div class="manual-card-title" id="manual-driver-card-title"><span class="manual-step-number">4</span>Select Vehicle & Driver <small>Recommended</small></div>
							</div>
							<div class="manual-board-tools">
								<div ng-init="driver_availability = ''" class="manual-hidden-control">
									<select class='form-control change_field' id='input_driver_availability' name='driver_availability' ng-model="driver_availability" ng-change="list_driver()"></select>
								</div>
								<div ng-init="map_radius={{ (int) ($dispatch_settings['default_radius_miles'] ?? 10) }}" class="manual-hidden-control">
									<select class='form-control' id='input_map_zoom' name='map_zoom' ng-model="map_radius" ng-change="map_zoom(map_radius)">
										<option value="0">Select Radius</option>
										<option value="5">5 Miles Radius</option>
										<option value="10">10 Miles Radius</option>
										<option value="20">20 Miles Radius</option>
										<option value="30">30 Miles Radius</option>
									</select>
								</div>
								<input type="text" class="form-control manual-driver-search" id="input_search_driver" placeholder="Search driver" ng-model="search_driver">
								<button type="button" class="manual-mini-filter"><i class="bi bi-funnel"></i> Filter</button>
							</div>
						</div>

						<div class="manual-vehicle-pill-row" ng-init="vehicle_type_value = '{{$ride->car_id}}'">
							<select class='form-control change_field d-none' ng-cloak name="vehicle_type" id="input_vehicle_type" ng-model="vehicle_type_value" ng-change="list_driver()" disabled>
								<option value="" disabled>Select Vehicle Type</option>
								<option ng-repeat="(key,vehicle_type) in vehicle_types" value="@{{vehicle_type.id}}" key=@{{key}} @if(isset($schedule_ride->id)) ng-selected="@{{key}} == @{{vehicle_type_value}}" @endif>@{{vehicle_type.car_name}}</option>
							</select>
							<button type="button" class="manual-vehicle-pill" ng-repeat="vehicle_type in vehicle_types" ng-class="{ active: String(vehicle_type.id) === String(vehicle_type_value) }" ng-click="vehicle_type_value = vehicle_type.id; list_driver();">
								@{{vehicle_type.car_name}}
							</button>
						</div>

						<div class="assigned_driver mb-2" style="display:none">
							<div class="manual-assigned-driver" ng-cloak>Assigned Driver: @{{assigned_driver.first_name}} @{{assigned_driver.hidden_mobile_number}}</div>
						</div>

						<div class="dispatch-assignment-grid mb-3" ng-init="auto_assign_status = {{(($ride->driver_id==0 || $ride->driver_id==null) && $ride->id != '')?'true':'false'}}">
							<div class="dispatch-checkbox-card" id="auto_assign_status_error">
								<div class="dispatch-checkbox-row">
									<div class="dispatch-checkbox-copy">
										<strong>Auto Assign Driver</strong>
										<small class="text-muted">Use dispatch engine to choose a driver</small>
									</div>
									<label class="switch mb-0">
										<input class="change_field" id="input_auto_assign_status" ng-model="auto_assign_status" type="checkbox" name="auto_assign_status" data-error-placement="container" data-error-container="#auto_assign_status_error">
										<span class="slider round"></span>
									</label>
								</div>
							</div>
							<div class="dispatch-checkbox-card">
								<div class="dispatch-checkbox-row">
									<div class="dispatch-checkbox-copy">
										<strong>Dispatch Override</strong>
										<small class="text-muted">Allow close-time dispatch if policy permits</small>
									</div>
									<label class="switch mb-0">
										<input type="checkbox" id="dispatch-override-toggle" {{ $ride->is_dispatch_override ? 'checked' : '' }}>
										<span class="slider round"></span>
									</label>
								</div>
							</div>
							<div class="dispatch-checkbox-card">
								<div class="dispatch-checkbox-row">
									<div class="dispatch-checkbox-copy">
										<strong>Assign Now</strong>
										<small class="text-muted">Send immediately for scheduled bookings</small>
									</div>
									<label class="switch mb-0">
										<input type="checkbox" id="dispatch-assign-now-toggle" {{ $ride->assign_now || (($ride->booking_mode ?: 'asap') === 'asap') ? 'checked' : '' }}>
										<span class="slider round"></span>
									</label>
								</div>
							</div>
						</div>

						<div class="driver_list_div">
							<div style="display:none;" class="driver_list">
								<div class="manual-driver-grid">
									<div class="manual-driver-tile driver_detail_view" ng-repeat="driver in drivers | filter:Driverfilter(search_driver) | filter:{ driver_current_status: driver_availability }" id="@{{driver.id}}" first_name="@{{driver.first_name}}" last_name="@{{driver.last_name}}" phone="@{{driver.mobile_number}}" email="@{{driver.email}}" company="@{{driver.company}}">
										<div class="manual-driver-tile-top">
											<img ng-src="@{{driver.src}}" class="manual-driver-avatar" alt="@{{driver.first_name}}">
											<div>
												<div class="manual-driver-name">@{{driver.first_name}} @{{driver.last_name}}</div>
												<div class="manual-driver-meta">@{{driver.vehicle_number || 'Vehicle pending'}} · @{{driver.distance_km || 0}} km</div>
											</div>
											<div class="manual-driver-score">@{{driver.driver_current_status || 'Available'}}</div>
										</div>
										<div class="manual-driver-actions">
											<a href="tel:@{{driver.mobile_number}}" class="manual-chip">Call</a>
											<button type="button" class="manual-chip primary" ng-click="auto_assign(driver.id)">Assign</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="manual-lower-grid">
						<div class="manual-card">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">5</span>Additional Options</div>
							</div>
							<div class="manual-options-list">
								<label class="manual-checkline"><input type="checkbox" id="workspace-add-via-toggle"> <span>Add Via Stop</span></label>
								<label class="manual-checkline"><input type="checkbox" id="workspace-return-trip-toggle" {{ data_get($manual_booking_payload, 'return_trip.enabled') ? 'checked' : '' }}> <span>Add Return Trip</span></label>
								<label class="manual-checkline"><input type="checkbox" id="workspace-recurring-toggle" {{ data_get($manual_booking_payload, 'recurring.enabled') ? 'checked' : '' }}> <span>Save as Recurring</span></label>
								<label class="manual-checkline"><input type="checkbox" id="workspace-child-seat-toggle"> <span>Child Seat Required</span></label>
								<label class="manual-checkline"><input type="checkbox" id="workspace-extra-luggage-toggle"> <span>Extra Luggage</span></label>
								<label class="manual-checkline"><input type="checkbox" id="workspace-send-sms-toggle" checked> <span>Send SMS to Customer</span></label>
								<label class="manual-checkline" data-workspace-panel="airport_transfer" style="display:none;"><input type="checkbox" id="workspace-meet-greet-toggle" checked> <span>Meet & Greet</span></label>
							</div>
						</div>

						<div class="manual-card">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">6</span>Payment & Notes</div>
							</div>
							<div class="manual-form-stack">
								<div>
									<label class="form-label" for="payment_method">Payment Method</label>
									<select class="form-select" id="payment_method" name="payment_method">
										<option value="Cash" {{ data_get($manual_booking_payload, 'payment_method', 'Cash') === 'Cash' ? 'selected' : '' }}>Cash</option>
										<option value="Card" {{ data_get($manual_booking_payload, 'payment_method') === 'Card' ? 'selected' : '' }}>Card</option>
										<option value="Wallet" {{ data_get($manual_booking_payload, 'payment_method') === 'Wallet' ? 'selected' : '' }}>Wallet</option>
										<option value="Invoice" {{ data_get($manual_booking_payload, 'payment_method') === 'Invoice' ? 'selected' : '' }}>Invoice</option>
									</select>
								</div>
								<div>
									<label class="form-label" for="customer_reference">Customer Note</label>
									<input type="text" class="form-control" id="customer_reference" name="customer_reference" value="{{ old('customer_reference', data_get($manual_booking_payload, 'customer_reference')) }}" placeholder="Any special instruction for driver">
								</div>
							</div>
						</div>

						<div class="manual-card">
							<div class="manual-card-head">
								<div class="manual-card-title"><span class="manual-step-number">7</span>Dispatch Settings</div>
							</div>
							<div class="manual-form-stack">
								<div>
									<label class="form-label">Dispatch Mode</label>
									<div class="dispatch-mode-pills" id="dispatch-mode-pills">
										<button type="button" class="dispatch-mode-pill {{ ($ride->dispatch_mode ?: (($ride->driver_id ?? 0) ? 'manual' : 'auto')) === 'manual' ? 'active' : '' }}" data-dispatch-mode="manual">Manual</button>
										<button type="button" class="dispatch-mode-pill {{ ($ride->dispatch_mode === 'auto' || (!$ride->dispatch_mode && !($ride->driver_id ?? 0))) ? 'active' : '' }}" data-dispatch-mode="auto">Auto</button>
										<button type="button" class="dispatch-mode-pill {{ $ride->dispatch_mode === 'broadcast' ? 'active' : '' }}" data-dispatch-mode="broadcast">Broadcast</button>
										<button type="button" class="dispatch-mode-pill {{ $ride->dispatch_mode === 'sequential' ? 'active' : '' }}" data-dispatch-mode="sequential">Sequence</button>
										<button type="button" class="dispatch-mode-pill {{ $ride->dispatch_mode === 'force' ? 'active' : '' }}" data-dispatch-mode="force">Force</button>
									</div>
								</div>
								<div>
									<label class="form-label" for="dispatch-driver-note-field">Driver Note</label>
									<textarea class="form-control" id="dispatch-driver-note-field" name="driver_note" placeholder="Optional driver note">{{ old('driver_note', $ride->driver_note) }}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="manual-footer-bar">
						<div class="manual-footer-copy" id="manual-footer-copy">
							<strong id="manual-footer-title">Ready to create booking?</strong>
							<span id="manual-footer-subtitle">Click "Book Now" to assign the vehicle and notify the driver.</span>
						</div>
						<div class="manual-footer-actions">
							<button type="button" class="btn btn-light reset">Cancel</button>
							<button type="button" class="btn btn-primary submit_button change_field" id="manual-footer-submit" disabled ng-disabled="myForm.$invalid && page_loading==0" ng-click="submitForm($event);">Book Now <i class="bi bi-arrow-right-short"></i></button>
						</div>
					</div>
				</div>

				<div class="manual-right-rail">
					<div class="manual-estimate-card">
						<div class="manual-estimate-head">Estimated Time & Fare</div>
						<div class="manual-estimate-stats">
							<div><span>8 min</span><small>ETA</small></div>
							<div><span ng-cloak>{{$currency_symbol}}@{{vehicle_detail_total_fare | number: 0}}</span><small>Estimated Fare</small></div>
						</div>
						<div class="map-view clearfix">
							<div class="dispatch-map-overlay">
								<button type="button" class="dispatch-ghost-btn" id="dispatch-map-focus-pickup">Pickup</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-map-focus-drop">Drop</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-refresh-drivers">Refresh</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-fit-route">Fit</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-recenter-map">Center</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-clear-route">Clear</button>
								<select class='form-control change_field d-none' id='input_driver_availability_clone' ng-model="driver_availability" ng-change="list_driver()">
									<option value="">All</option>
									<option value="Online">Available Driver</option>
									<option value="Scheduled">Enroute to Pickup</option>
									<option value="Begin trip">Reached Pickup</option>
									<option value="End trip">Journey Started</option>
									<option value="Offline">Offline</option>
								</select>
								<select class='form-control d-none' id='input_map_zoom_clone' ng-model="map_radius" ng-change="map_zoom(map_radius)">
									<option value="0">Select Radius</option>
									<option value="5">5 Miles Radius</option>
									<option value="10">10 Miles Radius</option>
									<option value="20">20 Miles Radius</option>
									<option value="30">30 Miles Radius</option>
								</select>
							</div>
							<div class="clearfix driver_detail_popup" style="width:100%;background:white;display:none;position:absolute;z-index:100">
								<h4>Driver Details</h4>
								<div class="row"><div class="col-lg-4">Driver :</div><div class="col-lg-8 driver_name_detail"></div></div>
								@if(LOGIN_USER_TYPE!='company')
								<div class="row driver_company_show"><div class="col-lg-4">Company :</div><div class="col-lg-8 driver_company_detail"></div></div>
								@endif
								<div class="row"><div class="col-lg-4">Driver Email :</div><div class="col-lg-8 driver_email_detail"></div></div>
								<div class="row"><div class="col-lg-4">Phone No :</div><div class="col-lg-8 driver_phone_detail"></div></div>
							</div>
							<div id="map"></div>
						</div>
						<div class="manual-estimate-meta">
							<div><strong>@{{vehicle_detail_km || 0}} km</strong><small>Distance</small></div>
							<div><strong>@{{vehicle_detail_minutes || 0}} min</strong><small>Duration</small></div>
							<div><strong ng-cloak>{{$currency_symbol}}@{{vehicle_detail_minimum_fare | number: 0}}</strong><small>Base Fare</small></div>
							<div><strong ng-cloak>{{$currency_symbol}}@{{vehicle_detail_total_fare | number: 0}}</strong><small>Total Fare</small></div>
						</div>
					</div>

					<div class="manual-summary-card">
						<div class="manual-summary-head">
							<h6>Booking Summary</h6>
							<button type="button" class="manual-link-button" data-bs-toggle="modal" data-bs-target="#manualBookingTemplateModal">Edit</button>
						</div>
						<ul class="manual-summary-list">
							<li data-summary-row="pickup" class="is-visible"><span>Pickup</span><strong id="summary-pickup-label">{{ $ride->pickup_location ?: 'Waiting for pickup' }}</strong></li>
							<li data-summary-row="destination" class="is-visible"><span>Destination</span><strong id="summary-drop-label">{{ $ride->drop_location ?: 'Waiting for destination' }}</strong></li>
							<li data-summary-row="pickup_time" class="is-visible"><span>Pickup Time</span><strong id="summary-pickup-time-label">{{ $ride->schedule_date ? $ride->schedule_date.' '.$ride->schedule_time : 'Select pickup time' }}</strong></li>
							<li data-summary-row="recurring"><span>Recurring</span><strong id="summary-recurring-label">Daily at 08:30 AM<br>Starts 20 Nov, 2023 • No End Date</strong></li>
							<li data-summary-row="transfer_type"><span>Transfer Type</span><strong id="summary-transfer-type-label">Airport Pickup</strong></li>
							<li data-summary-row="airport_from"><span>From (Airport)</span><strong id="summary-airport-from-label">Jomo Kenyatta Intl. Airport (NBO)</strong></li>
							<li data-summary-row="flight"><span>Flight</span><strong id="summary-flight-label">KQ 201</strong></li>
							<li data-summary-row="buffer"><span>Pickup Buffer</span><strong id="summary-buffer-label">60 min after landing</strong></li>
							<li data-summary-row="vehicle"><span>Vehicle Type</span><strong>@{{(vehicle_types | filter:{id: vehicle_type_value})[0].car_name || 'Any'}}</strong></li>
							<li data-summary-row="payment" class="is-visible"><span>Payment Method</span><strong id="summary-payment-label">{{ data_get($manual_booking_payload, 'payment_method', 'Cash') }}</strong></li>
						</ul>
						<div class="manual-summary-total">
							<span>Total Fare</span>
							<strong ng-cloak>{{$currency_symbol}}@{{vehicle_detail_total_fare | number: 0}}</strong>
						</div>
					</div>
				</div>
			</div>
		</div>
		{!! Form::close() !!}
	</div>

	<div class="modal fade workspace-modal" id="manualBookingQuickActionsModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Quick Actions</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="quick-action-grid">
						<button type="button" class="quick-action-tile" data-quick-action="focus-pickup"><i class="bi bi-geo-alt"></i><span>Find Address</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="estimate-route"><i class="bi bi-signpost-2"></i><span>Estimate Route & Fare</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="return-trip"><i class="bi bi-arrow-repeat"></i><span>Add Return Trip</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="via-stop"><i class="bi bi-plus-circle"></i><span>Add Via Stop</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="clear-form"><i class="bi bi-eraser"></i><span>Clear Form</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="save-template"><i class="bi bi-journal-plus"></i><span>Save as Template</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="load-template"><i class="bi bi-folder2-open"></i><span>Load Template</span></button>
						<button type="button" class="quick-action-tile" data-quick-action="customer-history"><i class="bi bi-clock-history"></i><span>Customer History</span></button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade workspace-modal" id="manualBookingDraftModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Save Draft</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<label class="form-label" for="draft_name">Draft Name</label>
					<input type="text" class="form-control" id="draft_name" value="{{ old('draft_name') }}" placeholder="Hotel transfer - John Doe">
					<div class="form-check mt-3">
						<input class="form-check-input" type="checkbox" id="draft_notify_later">
						<label class="form-check-label" for="draft_notify_later">Notify me later</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="save-workspace-draft">Save Draft</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade workspace-modal" id="manualBookingTemplateModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Save as Template</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label" for="template_name">Template Name</label>
						<input type="text" class="form-control" id="template_name" placeholder="Corporate - Hotel Transfer">
					</div>
					<div class="mb-3">
						<label class="form-label" for="template_description">Description</label>
						<textarea class="form-control" id="template_description" placeholder="Default template for hotel transfers"></textarea>
					</div>
					<div>
						<label class="form-label" for="template_scope">Share Template</label>
						<select class="form-select" id="template_scope">
							<option value="private">Private (Only me)</option>
							<option value="company">Company</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="save-workspace-template">Save Template</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade workspace-modal" id="manualBookingLoadTemplateModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Load Template</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="template-list" id="manual-booking-template-list">
						@foreach(($manual_booking_templates ?? []) as $template)
							<div class="template-item">
								<div class="d-flex justify-content-between align-items-start gap-3">
									<div>
										<div class="fw-bold">{{ $template->name }}</div>
										<div class="text-muted small">{{ $template->description ?: 'Saved booking template' }}</div>
									</div>
									<button type="button" class="btn btn-light btn-sm apply-template-button" data-template='@json($template->payload)' data-template-id="{{ $template->id }}">Use</button>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade workspace-modal" id="manualBookingHistoryModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Customer History</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="history-list" id="manual-booking-history-list">
						<div class="history-item text-muted">Customer history will appear here after lookup.</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade workspace-modal" id="manualBookingReturnTripModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Add Return Trip</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="form-check form-switch mb-3">
						<input class="form-check-input" type="checkbox" id="return_trip_enabled_switch" {{ data_get($manual_booking_payload, 'return_trip.enabled') ? 'checked' : '' }}>
						<label class="form-check-label" for="return_trip_enabled_switch">Add Return Trip</label>
					</div>
					<div class="dispatch-form-grid">
						<div>
							<label class="form-label" for="return_date_input">Return Date</label>
							<input type="date" class="form-control" id="return_date_input" value="{{ data_get($manual_booking_payload, 'return_trip.date') }}">
						</div>
						<div>
							<label class="form-label" for="return_time_input">Return Time</label>
							<input type="time" class="form-control" id="return_time_input" value="{{ data_get($manual_booking_payload, 'return_trip.time') }}">
						</div>
						<div>
							<label class="form-label" for="return_pickup_location_input">Return From</label>
							<input type="text" class="form-control" id="return_pickup_location_input" value="{{ data_get($manual_booking_payload, 'return_trip.pickup_location') }}">
						</div>
						<div>
							<label class="form-label" for="return_drop_location_input">Return To</label>
							<input type="text" class="form-control" id="return_drop_location_input" value="{{ data_get($manual_booking_payload, 'return_trip.drop_location') }}">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="apply-return-trip-config" data-bs-dismiss="modal">Add Return Trip</button>
				</div>
			</div>
		</div>
	</div>

	@if($manual_booking_success)
	<div class="modal fade workspace-modal show" id="manualBookingSuccessModal" tabindex="-1" aria-hidden="false" style="display:block;background:rgba(15,23,42,.55);">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-body">
					<div class="success-state">
						<div class="badge-shell"><i class="bi bi-check-lg"></i></div>
						<h5 class="mb-2">Booking has been created successfully!</h5>
						<div class="text-muted mb-3">Booking ID <strong>#BK-{{ $manual_booking_success['schedule_id'] }}</strong></div>
						<div class="d-flex gap-2 justify-content-center">
							<a href="{{ url(LOGIN_USER_TYPE.'/later_booking') }}" class="btn btn-light">View Booking</a>
							<a href="{{ url(LOGIN_USER_TYPE.'/manual_booking') }}" class="btn btn-primary">Create Another</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endif
</div>
@endsection

@push('scripts')
<script type="text/javascript">
	var REQUEST_URL = "{{url('/'.LOGIN_USER_TYPE)}}"; 
	var old_edit_date = "{{isset($schedule_ride->id)?$ride->schedule_date.' '.$ride->schedule_time:''}}";
	var page = "{{isset($schedule_ride->id)?'edit':'new'}}";
	var MANUAL_DISPATCH_SETTINGS = @json($dispatch_settings);

	(function () {
		function getConsoleApi() {
			return window.manualBookingConsole || null;
		}

		function focusIfExists(id) {
			var element = document.getElementById(id);
			if (element && !element.disabled) {
				element.focus();
				if (typeof element.select === 'function') {
					element.select();
				}
			}
		}

		function swapPickupDrop() {
			var pickup = document.getElementById('input_pickup_location');
			var drop = document.getElementById('input_drop_location');
			var pickupLat = document.getElementById('pickup_latitude');
			var pickupLng = document.getElementById('pickup_longitude');
			var dropLat = document.getElementById('drop_latitude');
			var dropLng = document.getElementById('drop_longitude');

			if (!pickup || !drop) {
				return;
			}

			var pickupValue = pickup.value;
			var dropValue = drop.value;
			pickup.value = dropValue;
			drop.value = pickupValue;

			if (pickupLat && dropLat) {
				var tempLat = pickupLat.value;
				pickupLat.value = dropLat.value;
				dropLat.value = tempLat;
			}

			if (pickupLng && dropLng) {
				var tempLng = pickupLng.value;
				pickupLng.value = dropLng.value;
				dropLng.value = tempLng;
			}

			if (window.jQuery) {
				window.jQuery(pickup).trigger('change');
				window.jQuery(drop).trigger('change');
			}
		}

		function updateModeButtons(mode) {
			document.querySelectorAll('[data-map-mode]').forEach(function (button) {
				button.classList.toggle('active', button.getAttribute('data-map-mode') === mode);
			});
		}

		function populateFormAlert() {
			var alertBox = document.getElementById('dispatch-form-alert');
			if (!alertBox) {
				return;
			}

			var hasErrors = Array.from(document.querySelectorAll('#manual_booking span.text-danger, #manual_booking span.error')).some(function (node) {
				return node.textContent && node.textContent.trim() !== '';
			});
			alertBox.classList.toggle('d-none', !hasErrors);
		}

			function updateChecklist() {
				var pickup = document.getElementById('input_pickup_location');
				var drop = document.getElementById('input_drop_location');
			var via = document.getElementById('input_via_location');
			var vehicle = document.getElementById('input_vehicle_type');
			var manualBookingId = document.getElementById('manual_booking_id');
			var passenger = document.getElementById('input_first_name');
			var autoAssign = document.getElementById('input_auto_assign_status');
			var fareNode = document.querySelector('.dispatch-total-fare strong');

			var checks = {
				'dispatch-check-pickup': pickup && pickup.value.trim() !== '',
				'dispatch-check-drop': drop && drop.value.trim() !== '',
				'dispatch-check-via': !via || via.value.trim() !== '',
				'dispatch-check-vehicle': vehicle && vehicle.value !== '',
				'dispatch-check-fare': fareNode && fareNode.textContent.replace(/\s/g, '') !== '' && !/0\.00$/.test(fareNode.textContent.trim()),
				'dispatch-check-assignment': (autoAssign && autoAssign.checked) || (document.getElementById('auto_assign_id') && document.getElementById('auto_assign_id').value !== '' && document.getElementById('auto_assign_id').value !== '0'),
			};

			var passengerNode = passenger ? passenger.closest('.dispatch-booking-panel') : null;
			if (passengerNode) {
				var passengerCheck = passenger.value.trim() !== '' || (manualBookingId && manualBookingId.value !== '');
				var passengerItem = document.querySelector('.dispatch-check-item');
				if (passengerItem) {
					passengerItem.classList.toggle('complete', passengerCheck);
					passengerItem.classList.toggle('pending', !passengerCheck);
				}
			}

			Object.keys(checks).forEach(function (id) {
				var node = document.getElementById(id);
				if (!node) {
					return;
				}
				node.classList.toggle('complete', checks[id]);
				node.classList.toggle('pending', !checks[id]);
			});

			var routeHeader = document.getElementById('dispatch-header-route-value');
			if (routeHeader) {
				routeHeader.textContent = checks['dispatch-check-pickup'] && checks['dispatch-check-drop'] ? 'Calculated / Ready' : 'Pending';
			}

			var summaryPickup = document.getElementById('summary-pickup-label');
			if (summaryPickup && pickup) {
				summaryPickup.textContent = pickup.value.trim() || 'Waiting for pickup';
			}

			var summaryDrop = document.getElementById('summary-drop-label');
			if (summaryDrop && drop) {
				summaryDrop.textContent = drop.value.trim() || 'Waiting for destination';
			}

			var summaryPickupTime = document.getElementById('summary-pickup-time-label');
			var dateTimeNode = document.getElementById('input_date_time');
			if (summaryPickupTime && dateTimeNode) {
				summaryPickupTime.textContent = dateTimeNode.value.trim() || 'Select pickup time';
			}

			var paymentSummary = document.getElementById('summary-payment-label');
			var paymentMethod = document.getElementById('payment_method');
			if (paymentSummary && paymentMethod) {
				paymentSummary.textContent = paymentMethod.value || 'Cash';
			}

			var fareHeader = document.getElementById('dispatch-header-fare-value');
			if (fareHeader && fareNode) {
				fareHeader.textContent = fareNode.textContent.trim() || fareHeader.textContent;
			}

			var driverCountHeader = document.getElementById('dispatch-header-nearby-drivers-value');
			if (driverCountHeader && window.angular) {
				var scope = window.angular.element(document.querySelector('[ng-controller=\"manual_booking\"]')).scope();
				if (scope && typeof scope.getFilteredDrivers === 'function') {
					driverCountHeader.textContent = scope.getFilteredDrivers().length;
				}
			}

			var routeBadge = document.getElementById('dispatch-status-route');
			var fareBadge = document.getElementById('dispatch-status-fare');
			var driverBadge = document.getElementById('dispatch-status-driver');
			if (routeBadge) {
				routeBadge.textContent = checks['dispatch-check-pickup'] && checks['dispatch-check-drop'] ? 'Route Ready' : 'Route Incomplete';
				routeBadge.classList.toggle('success', checks['dispatch-check-pickup'] && checks['dispatch-check-drop']);
				routeBadge.classList.toggle('warning', !(checks['dispatch-check-pickup'] && checks['dispatch-check-drop']));
			}
			if (fareBadge) {
				fareBadge.textContent = checks['dispatch-check-fare'] ? 'Fare Estimated' : 'Fare Pending';
				fareBadge.classList.toggle('success', checks['dispatch-check-fare']);
				fareBadge.classList.toggle('warning', !checks['dispatch-check-fare']);
			}
			if (driverBadge) {
				driverBadge.textContent = checks['dispatch-check-assignment'] ? 'Driver Ready' : 'No Driver Yet';
				driverBadge.classList.toggle('success', checks['dispatch-check-assignment']);
				driverBadge.classList.toggle('warning', !checks['dispatch-check-assignment']);
			}
		}

		function syncDispatchModeUI(mode) {
			document.querySelectorAll('[data-dispatch-mode]').forEach(function (button) {
				button.classList.toggle('active', button.getAttribute('data-dispatch-mode') === mode);
			});

			document.getElementById('dispatch_mode').value = mode;
			var autoAssignToggle = document.getElementById('input_auto_assign_status');
			if (!autoAssignToggle) {
				return;
			}

			var shouldAutoAssign = ['auto', 'broadcast', 'sequential'].indexOf(mode) !== -1;
			autoAssignToggle.checked = shouldAutoAssign;
			if (window.jQuery) {
				window.jQuery(autoAssignToggle).trigger('change');
			}
		}

		function syncBookingModeUI(mode) {
			document.querySelectorAll('[data-booking-mode]').forEach(function (button) {
				button.classList.toggle('active', button.getAttribute('data-booking-mode') === mode);
			});

			document.getElementById('booking_mode').value = mode;

			var assignNowToggle = document.getElementById('dispatch-assign-now-toggle');
			if (assignNowToggle && mode === 'asap') {
				assignNowToggle.checked = true;
				document.getElementById('assign_now').value = '1';
			}
		}

		document.addEventListener('DOMContentLoaded', function () {
			document.body.classList.add('manual-booking-screen');

			document.querySelectorAll('.sync-proxy').forEach(function (node) {
				node.addEventListener('input', function () {
					var target = document.getElementById(node.getAttribute('data-sync-target'));
					if (target) {
						target.value = node.value;
						if (window.jQuery) {
							window.jQuery(target).trigger('change');
						}
					}
					updateChecklist();
				});
			});

			document.querySelectorAll('[data-airport-direction]').forEach(function (button) {
				button.addEventListener('click', function () {
					document.querySelectorAll('[data-airport-direction]').forEach(function (peer) {
						peer.classList.toggle('active', peer === button);
					});
					var airportDirection = document.getElementById('airport_direction');
					if (airportDirection) {
						airportDirection.value = button.getAttribute('data-airport-direction');
					}
					var transferLabel = document.getElementById('summary-transfer-type-label');
					if (transferLabel) {
						transferLabel.textContent = button.getAttribute('data-airport-direction') === 'drop_to_airport' ? 'Airport Drop-off' : 'Airport Pickup';
					}
				});
			});

			document.querySelectorAll('[data-booking-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					syncBookingModeUI(button.getAttribute('data-booking-mode'));
					updateChecklist();
				});
			});

			document.querySelectorAll('[data-dispatch-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					syncDispatchModeUI(button.getAttribute('data-dispatch-mode'));
					updateChecklist();
				});
			});

			var overrideToggle = document.getElementById('dispatch-override-toggle');
			if (overrideToggle) {
				overrideToggle.addEventListener('change', function () {
					document.getElementById('is_dispatch_override').value = overrideToggle.checked ? '1' : '0';
				});
			}

			var assignNowToggle = document.getElementById('dispatch-assign-now-toggle');
			if (assignNowToggle) {
				assignNowToggle.addEventListener('change', function () {
					document.getElementById('assign_now').value = assignNowToggle.checked ? '1' : '0';
				});
			}

			var swapButton = document.getElementById('dispatch-swap-route');
			if (swapButton) {
				swapButton.addEventListener('click', swapPickupDrop);
			}

			var addViaButton = document.getElementById('dispatch-add-via');
			var removeViaButton = document.getElementById('dispatch-remove-via');
			var viaPanel = document.getElementById('dispatch-via-panel');
			var viaInput = document.getElementById('input_via_location');
			var viaLat = document.getElementById('via_latitude');
			var viaLng = document.getElementById('via_longitude');
			if (addViaButton && viaPanel) {
				addViaButton.addEventListener('click', function () {
					viaPanel.classList.add('active');
					if (viaInput) {
						viaInput.focus();
					}
					updateChecklist();
				});
			}
			if (removeViaButton && viaPanel) {
				removeViaButton.addEventListener('click', function () {
					viaPanel.classList.remove('active');
					if (viaInput) {
						viaInput.value = '';
						if (window.jQuery) {
							window.jQuery(viaInput).trigger('change');
						}
					}
					if (viaLat) {
						viaLat.value = '';
					}
					if (viaLng) {
						viaLng.value = '';
					}
					var api = getConsoleApi();
					if (api && typeof api.clearViaStop === 'function') {
						api.clearViaStop();
					}
					updateChecklist();
				});
			}

			var refreshDrivers = document.getElementById('dispatch-refresh-drivers');
			if (refreshDrivers) {
				refreshDrivers.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.refreshDrivers();
					}
				});
			}

			var fitRoute = document.getElementById('dispatch-fit-route');
			if (fitRoute) {
				fitRoute.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.fitRoute();
					}
					updateModeButtons('route');
				});
			}

			var recenterMap = document.getElementById('dispatch-recenter-map');
			if (recenterMap) {
				recenterMap.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.recenter();
					}
				});
			}

			var clearRoute = document.getElementById('dispatch-clear-route');
			if (clearRoute) {
				clearRoute.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.clearRoute();
					}
					updateModeButtons('map');
					updateChecklist();
				});
			}

			var focusPickup = document.getElementById('dispatch-map-focus-pickup');
			if (focusPickup) {
				focusPickup.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.focusPickup();
					}
				});
			}

			var focusDrop = document.getElementById('dispatch-map-focus-drop');
			if (focusDrop) {
				focusDrop.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.focusDrop();
					}
				});
			}

			document.querySelectorAll('[data-map-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					var mode = button.getAttribute('data-map-mode');
					var api = getConsoleApi();
					if (api) {
						api.setMode(mode);
					}
					updateModeButtons(mode);
				});
			});

			var driverAvailabilityClone = document.getElementById('input_driver_availability_clone');
			var driverAvailabilityPrimary = document.getElementById('input_driver_availability');
			if (driverAvailabilityClone && driverAvailabilityPrimary && window.jQuery) {
				driverAvailabilityClone.addEventListener('change', function () {
					if (window.jQuery(driverAvailabilityPrimary)[0].selectize) {
						window.jQuery(driverAvailabilityPrimary)[0].selectize.setValue(driverAvailabilityClone.value, true);
					}
				});

				window.jQuery(driverAvailabilityPrimary).on('change', function () {
					driverAvailabilityClone.value = driverAvailabilityPrimary.value;
				});
			}

			var mapZoomClone = document.getElementById('input_map_zoom_clone');
			var mapZoomPrimary = document.getElementById('input_map_zoom');
			if (mapZoomClone && mapZoomPrimary) {
				mapZoomClone.addEventListener('change', function () {
					mapZoomPrimary.value = mapZoomClone.value;
					if (window.jQuery) {
						window.jQuery(mapZoomPrimary).trigger('change');
					}
				});

				mapZoomPrimary.addEventListener('change', function () {
					mapZoomClone.value = mapZoomPrimary.value;
				});
			}

			updateModeButtons('map');
			syncBookingModeUI(document.getElementById('booking_mode').value || 'asap');
			syncDispatchModeUI(document.getElementById('dispatch_mode').value || 'auto');
			document.addEventListener('keydown', function (event) {
				if (event.altKey || event.ctrlKey || event.metaKey) {
					return;
				}

				switch (event.key) {
					case 'F2':
						event.preventDefault();
						focusIfExists('input_pickup_location');
						break;
					case 'F3':
						event.preventDefault();
						focusIfExists('input_drop_location');
						break;
					case 'F4':
						event.preventDefault();
						focusIfExists('input_first_name');
						break;
					case 'F6':
						event.preventDefault();
						focusIfExists('input_search_driver');
						break;
					case 'F9':
						event.preventDefault();
						var submitButton = document.querySelector('#manual-footer-submit');
						if (submitButton && !submitButton.disabled) {
							submitButton.click();
						}
						break;
				}
			});

			['input_first_name', 'input_pickup_location', 'input_drop_location', 'input_via_location', 'input_vehicle_type', 'input_auto_assign_status', 'input_date_time', 'payment_method'].forEach(function (id) {
				var node = document.getElementById(id);
				if (node) {
					node.addEventListener('change', updateChecklist);
					node.addEventListener('keyup', updateChecklist);
				}
			});

			['recurring_type', 'recurring_start_date', 'recurring_start_time', 'recurring_end_option'].forEach(function (id) {
				var node = document.getElementById(id);
				if (node) {
					node.addEventListener('change', updateRecurringSummary);
				}
			});

			['airport_name', 'airport_meeting_point', 'flight_number', 'airport_flight_date', 'airport_flight_time', 'pickup_buffer_time'].forEach(function (id) {
				var node = document.getElementById(id);
				if (node) {
					node.addEventListener('change', updateAirportSummary);
					node.addEventListener('keyup', updateAirportSummary);
				}
			});

			['later_pickup_date', 'later_pickup_time'].forEach(function (id) {
				var node = document.getElementById(id);
				if (node) {
					node.addEventListener('change', function () {
						syncLaterScheduleFields(true);
						updateChecklist();
					});
				}
			});

			syncLaterLeadWindowOptions();
			syncLaterScheduleFields(false);

			populateFormAlert();
			updateChecklist();

			function formPayload() {
				return {
					booking_experience_tab: document.getElementById('booking_experience_tab') ? document.getElementById('booking_experience_tab').value : 'asap',
					booking_mode: document.getElementById('booking_mode') ? document.getElementById('booking_mode').value : 'asap',
					dispatch_mode: document.getElementById('dispatch_mode') ? document.getElementById('dispatch_mode').value : 'auto',
					pickup_location: document.getElementById('input_pickup_location') ? document.getElementById('input_pickup_location').value : '',
					pickup_latitude: document.getElementById('pickup_latitude') ? document.getElementById('pickup_latitude').value : '',
					pickup_longitude: document.getElementById('pickup_longitude') ? document.getElementById('pickup_longitude').value : '',
					drop_location: document.getElementById('input_drop_location') ? document.getElementById('input_drop_location').value : '',
					drop_latitude: document.getElementById('drop_latitude') ? document.getElementById('drop_latitude').value : '',
					drop_longitude: document.getElementById('drop_longitude') ? document.getElementById('drop_longitude').value : '',
					via_location: document.getElementById('input_via_location') ? document.getElementById('input_via_location').value : '',
					via_latitude: document.getElementById('via_latitude') ? document.getElementById('via_latitude').value : '',
					via_longitude: document.getElementById('via_longitude') ? document.getElementById('via_longitude').value : '',
					date_time: document.getElementById('input_date_time') ? document.getElementById('input_date_time').value : '',
					vehicle_type: document.getElementById('input_vehicle_type') ? document.getElementById('input_vehicle_type').value : '',
					priority: document.getElementById('dispatch-priority-selector') ? document.getElementById('dispatch-priority-selector').value : 'normal',
					first_name: document.getElementById('input_first_name') ? document.getElementById('input_first_name').value : '',
					last_name: document.getElementById('input_last_name') ? document.getElementById('input_last_name').value : '',
					email: document.getElementById('input_email') ? document.getElementById('input_email').value : '',
					mobile_number: document.getElementById('input_mobile_number') ? document.getElementById('input_mobile_number').value : '',
					country_code: document.getElementById('input_country_code') ? document.getElementById('input_country_code').value : '',
					country_id: document.getElementById('country_id') ? document.getElementById('country_id').value : '',
					gender: document.getElementById('input_gender') ? document.getElementById('input_gender').value : '',
					filter: window.jQuery ? (window.jQuery('#input_filter').val() || []) : [],
					dispatcher_note: document.getElementById('dispatch-driver-note') ? document.getElementById('dispatch-driver-note').value : '',
					driver_note: document.getElementById('dispatch-driver-note-field') ? document.getElementById('dispatch-driver-note-field').value : '',
					payment_method: document.getElementById('payment_method') ? document.getElementById('payment_method').value : 'Cash',
					customer_type: document.getElementById('booking_for_type') ? document.getElementById('booking_for_type').value : '',
					company_name: document.getElementById('company_name') ? document.getElementById('company_name').value : '',
					company_phone: document.getElementById('company_phone') ? document.getElementById('company_phone').value : '',
					customer_reference: document.getElementById('customer_reference') ? document.getElementById('customer_reference').value : '',
					flight_number: document.getElementById('flight_number') ? document.getElementById('flight_number').value : '',
					airline: document.getElementById('airline') ? document.getElementById('airline').value : '',
					terminal: document.getElementById('terminal') ? document.getElementById('terminal').value : '',
					airport_direction: document.getElementById('airport_direction') ? document.getElementById('airport_direction').value : '',
					pickup_sign: document.getElementById('pickup_sign') ? document.getElementById('pickup_sign').value : '',
					return_trip: {
						enabled: document.getElementById('return_trip_enabled') ? document.getElementById('return_trip_enabled').value === '1' : false,
						date: document.getElementById('return_date') ? document.getElementById('return_date').value : '',
						time: document.getElementById('return_time') ? document.getElementById('return_time').value : '',
						pickup_location: document.getElementById('return_pickup_location') ? document.getElementById('return_pickup_location').value : '',
						drop_location: document.getElementById('return_drop_location') ? document.getElementById('return_drop_location').value : '',
					},
					recurring: {
						enabled: document.getElementById('recurring_enabled') ? document.getElementById('recurring_enabled').value === '1' : false,
						pattern: document.getElementById('recurring_pattern') ? document.getElementById('recurring_pattern').value : 'weekly',
						occurrences: document.getElementById('recurring_occurrences') ? document.getElementById('recurring_occurrences').value : 4,
						days: Array.from(document.querySelectorAll('input[name="recurring_days[]"]:checked')).map(function (item) { return item.value; }),
					}
				};
			}

			function applyBookingTab(tab) {
				var shell = document.querySelector('.manual-shell-reference');
				if (shell) {
					shell.setAttribute('data-active-tab', tab);
				}
				document.getElementById('booking_experience_tab').value = tab;
				document.querySelectorAll('.workspace-tab').forEach(function (button) {
					button.classList.toggle('active', button.getAttribute('data-booking-tab') === tab);
				});

				document.querySelectorAll('[data-workspace-panel]').forEach(function (panel) {
					var tabs = String(panel.getAttribute('data-workspace-panel') || '').split(/\s+/);
					panel.style.display = tabs.indexOf(tab) !== -1 ? '' : 'none';
				});

				if (tab === 'asap') {
					syncBookingModeUI('asap');
				}
				if (tab === 'later' || tab === 'advanced' || tab === 'recurring' || tab === 'airport_transfer') {
					syncBookingModeUI('scheduled');
				}
				if (tab === 'recurring') {
					document.getElementById('recurring_enabled').value = '1';
					var recurringToggle = document.getElementById('workspace-recurring-toggle');
					if (recurringToggle) {
						recurringToggle.checked = true;
					}
				}
				if (tab === 'later') {
					syncLaterLeadWindowOptions();
					syncLaterScheduleFields(true);
				}
				applyTabPresentation(tab);
			}

			function setSubmitLabels(label) {
				['manual-header-submit', 'manual-footer-submit'].forEach(function (id) {
					var button = document.getElementById(id);
					if (button) {
						button.innerHTML = label + ' <i class="bi bi-arrow-right-short"></i>';
					}
				});
			}

			function setSummaryVisibility(rows) {
				document.querySelectorAll('[data-summary-row]').forEach(function (row) {
					row.classList.toggle('is-visible', rows.indexOf(row.getAttribute('data-summary-row')) !== -1);
				});
			}

			function getScheduledDispatchLeadMinutes() {
				return Math.max(0, parseInt((window.MANUAL_DISPATCH_SETTINGS || {}).scheduled_dispatch_lead_minutes || 5, 10) || 5);
			}

			function getMinimumScheduledLeadMinutes() {
				return Math.max(0, parseInt((window.MANUAL_DISPATCH_SETTINGS || {}).minimum_scheduled_lead_minutes || 15, 10) || 15);
			}

			function getEffectiveScheduledLeadMinutes() {
				return Math.max(getScheduledDispatchLeadMinutes(), getMinimumScheduledLeadMinutes());
			}

			function padNumber(value) {
				return String(value).padStart(2, '0');
			}

			function toDateInputValue(date) {
				return [
					date.getFullYear(),
					padNumber(date.getMonth() + 1),
					padNumber(date.getDate())
				].join('-');
			}

			function toTimeInputValue(date) {
				return [padNumber(date.getHours()), padNumber(date.getMinutes())].join(':');
			}

			function combineScheduleValue(dateValue, timeValue) {
				if (!dateValue && !timeValue) {
					return '';
				}
				return [dateValue || '', timeValue || '00:00'].join(' ').trim();
			}

			function parseScheduleValue(dateValue, timeValue) {
				if (!dateValue) {
					return null;
				}
				var time = timeValue || '00:00';
				var parsed = new Date(dateValue + 'T' + time + ':00');
				return Number.isNaN(parsed.getTime()) ? null : parsed;
			}

			function syncLaterLeadWindowOptions() {
				var select = document.getElementById('later_prebooking_window');
				if (!select) {
					return;
				}
				var configuredLead = String(getScheduledDispatchLeadMinutes());
				var existing = Array.from(select.options).some(function (option) {
					return option.value === configuredLead;
				});
				if (!existing) {
					var option = document.createElement('option');
					option.value = configuredLead;
					option.textContent = configuredLead + ' min before pickup';
					select.appendChild(option);
				}
				select.value = configuredLead;
			}

			function syncLaterScheduleFields(forceClamp) {
				var laterDate = document.getElementById('later_pickup_date');
				var laterTime = document.getElementById('later_pickup_time');
				var dateTimeNode = document.getElementById('input_date_time');
				if (!laterDate || !laterTime || !dateTimeNode) {
					return;
				}

				var effectiveLead = getEffectiveScheduledLeadMinutes();
				var minLead = getMinimumScheduledLeadMinutes();
				var now = new Date();
				var minAllowed = new Date(now.getTime() + (effectiveLead * 60000));
				var scheduled = parseScheduleValue(laterDate.value, laterTime.value);

				if (!scheduled && dateTimeNode.value) {
					var normalized = dateTimeNode.value.trim().replace(' ', 'T');
					var fromHidden = new Date(normalized.length === 16 ? normalized + ':00' : normalized);
					if (!Number.isNaN(fromHidden.getTime())) {
						scheduled = fromHidden;
					}
				}

				if (!scheduled) {
					scheduled = minAllowed;
				}

				if (forceClamp !== false && scheduled.getTime() < minAllowed.getTime()) {
					scheduled = minAllowed;
				}

				laterDate.value = toDateInputValue(scheduled);
				laterTime.value = toTimeInputValue(scheduled);
				dateTimeNode.value = combineScheduleValue(laterDate.value, laterTime.value);

				var pickupSummary = document.getElementById('summary-pickup-time-label');
				if (pickupSummary) {
					pickupSummary.textContent = dateTimeNode.value || 'Select pickup time';
				}

				var footerSubtitle = document.getElementById('manual-footer-subtitle');
				var activeTab = document.getElementById('booking_experience_tab') ? document.getElementById('booking_experience_tab').value : 'asap';
				if (footerSubtitle && activeTab === 'later') {
					footerSubtitle.textContent = 'The driver will be assigned and notified ' + getScheduledDispatchLeadMinutes() + ' min before pickup.';
				}

				var helpLead = document.getElementById('dispatch-form-alert');
				if (helpLead && activeTab === 'later') {
					helpLead.setAttribute('data-minimum-lead-minutes', String(minLead));
				}
			}

			function applyTabPresentation(tab) {
				var config = {
					asap: {
						submit: 'Book Now',
						footerTitle: 'Ready to create booking?',
						footerSubtitle: 'Click "Book Now" to assign the vehicle and notify the driver.',
						pickupTitle: '<span class="manual-step-number">1</span>Pickup Details',
						destinationTitle: '<span class="manual-step-number">2</span>Destination Details',
						passengerTitle: '<span class="manual-step-number">3</span>Passenger Details',
						driverTitle: '<span class="manual-step-number">4</span>Select Vehicle & Driver <small>Recommended</small>',
						summary: ['pickup', 'destination', 'pickup_time', 'vehicle', 'payment']
					},
					later: {
						submit: 'Schedule Booking',
						footerTitle: 'Ready to schedule this booking?',
						footerSubtitle: 'The driver will be assigned and notified ' + getScheduledDispatchLeadMinutes() + ' min before pickup.',
						pickupTitle: '<span class="manual-step-number">2</span>Pickup Details',
						destinationTitle: '<span class="manual-step-number">3</span>Destination Details',
						passengerTitle: '<span class="manual-step-number">4</span>Passenger Details',
						driverTitle: '<span class="manual-step-number">5</span>Select Vehicle & Driver <small>Recommended</small>',
						summary: ['pickup', 'destination', 'pickup_time', 'vehicle', 'payment']
					},
					advanced: {
						submit: 'Book Now',
						footerTitle: 'Ready to create booking?',
						footerSubtitle: 'Click "Book Now" to assign the vehicle and notify the driver.',
						pickupTitle: '<span class="manual-step-number">2</span>Pickup Details',
						destinationTitle: '<span class="manual-step-number">4</span>Destination Details',
						passengerTitle: '<span class="manual-step-number">3</span>Passenger Details',
						driverTitle: '<span class="manual-step-number">5</span>Select Vehicle & Driver <small>Recommended</small>',
						summary: ['pickup', 'destination', 'pickup_time', 'vehicle', 'payment']
					},
					recurring: {
						submit: 'Create Recurring Booking',
						footerTitle: 'Ready to create recurring booking?',
						footerSubtitle: 'A new booking will be created for each occurrence and assigned automatically.',
						pickupTitle: '<span class="manual-step-number">2</span>Pickup Details',
						destinationTitle: '<span class="manual-step-number">4</span>Destination Details',
						passengerTitle: '<span class="manual-step-number">3</span>Passenger Details',
						driverTitle: '<span class="manual-step-number">4</span>Select Vehicle & Driver <small>Recommended</small>',
						summary: ['pickup', 'destination', 'recurring', 'vehicle', 'payment']
					},
					airport_transfer: {
						submit: 'Book Now',
						footerTitle: 'Ready to create airport transfer booking?',
						footerSubtitle: 'Driver will be assigned after the buffer time and notified with flight details.',
						pickupTitle: '<span class="manual-step-number">2</span>Pickup Details <small>(Airport)</small>',
						destinationTitle: '<span class="manual-step-number">3</span>Destination Details',
						passengerTitle: '<span class="manual-step-number">4</span>Passenger Details',
						driverTitle: '<span class="manual-step-number">5</span>Select Vehicle & Driver <small>Recommended</small>',
						summary: ['transfer_type', 'airport_from', 'destination', 'flight', 'buffer', 'vehicle', 'payment']
					}
				}[tab] || {};

				setSubmitLabels(config.submit || 'Book Now');

				var footerTitle = document.getElementById('manual-footer-title');
				if (footerTitle) {
					footerTitle.textContent = config.footerTitle || 'Ready to create booking?';
				}

				var footerSubtitle = document.getElementById('manual-footer-subtitle');
				if (footerSubtitle) {
					footerSubtitle.textContent = config.footerSubtitle || 'Click "Book Now" to assign the vehicle and notify the driver.';
				}

				var pickupTitle = document.getElementById('manual-pickup-card-title');
				if (pickupTitle) {
					pickupTitle.innerHTML = config.pickupTitle || '<span class="manual-step-number">1</span>Pickup Details';
				}

				var destinationTitle = document.getElementById('manual-destination-card-title');
				if (destinationTitle) {
					destinationTitle.innerHTML = config.destinationTitle || '<span class="manual-step-number">2</span>Destination Details';
				}

				var passengerTitle = document.getElementById('manual-passenger-card-title');
				if (passengerTitle) {
					passengerTitle.innerHTML = config.passengerTitle || '<span class="manual-step-number">3</span>Passenger Details';
				}

				var driverTitle = document.getElementById('manual-driver-card-title');
				if (driverTitle) {
					driverTitle.innerHTML = config.driverTitle || '<span class="manual-step-number">4</span>Select Vehicle & Driver <small>Recommended</small>';
				}

				setSummaryVisibility(config.summary || ['pickup', 'destination', 'pickup_time', 'vehicle', 'payment']);

				updateRecurringSummary();
				updateAirportSummary();
			}

			function updateRecurringSummary() {
				var summary = document.getElementById('summary-recurring-label');
				if (!summary) {
					return;
				}
				var type = document.getElementById('recurring_type');
				var startDate = document.getElementById('recurring_start_date');
				var startTime = document.getElementById('recurring_start_time');
				var end = document.getElementById('recurring_end_option');
				var typeLabel = type ? (type.options[type.selectedIndex] ? type.options[type.selectedIndex].text : 'Daily') : 'Daily';
				var timeLabel = startTime && startTime.value ? startTime.value : '08:30';
				var dateLabel = startDate && startDate.value ? startDate.value : '20 Nov, 2023';
				var endLabel = end ? (end.options[end.selectedIndex] ? end.options[end.selectedIndex].text : 'No End Date') : 'No End Date';
				summary.innerHTML = 'Occurs every ' + typeLabel.toLowerCase().replace(/ly$/, '') + ' at ' + timeLabel + '<br>Starts ' + dateLabel + ' • ' + endLabel.replace(/_/g, ' ');
				var preview = document.getElementById('recurring-summary-preview');
				if (preview) {
					preview.innerHTML = 'Occurs every ' + typeLabel.toLowerCase().replace(/ly$/, '') + ' at ' + timeLabel + '<br>Starting ' + dateLabel;
				}
			}

			function updateAirportSummary() {
				var airportFrom = document.getElementById('summary-airport-from-label');
				var airportName = document.getElementById('airport_name');
				var meetingPoint = document.getElementById('airport_meeting_point');
				if (airportFrom) {
					var fromText = airportName && airportName.value ? airportName.value : 'Jomo Kenyatta Intl. Airport (NBO)';
					var meetingText = meetingPoint && meetingPoint.value ? meetingPoint.value : '';
					airportFrom.innerHTML = fromText + (meetingText ? '<br>' + meetingText : '');
				}
				var flightSummary = document.getElementById('summary-flight-label');
				var flightNo = document.getElementById('flight_number');
				var flightDate = document.getElementById('airport_flight_date');
				var flightTime = document.getElementById('airport_flight_time');
				if (flightSummary) {
					var flightText = flightNo && flightNo.value ? flightNo.value : 'KQ 201';
					var dateText = flightDate && flightDate.value ? flightDate.value : '';
					var timeText = flightTime && flightTime.value ? flightTime.value : '';
					flightSummary.innerHTML = flightText + ((dateText || timeText) ? '<br>' + [dateText, timeText].filter(Boolean).join(' ') : '');
				}
				var buffer = document.getElementById('summary-buffer-label');
				var pickupBuffer = document.getElementById('pickup_buffer_time');
				if (buffer && pickupBuffer) {
					buffer.textContent = pickupBuffer.options[pickupBuffer.selectedIndex] ? pickupBuffer.options[pickupBuffer.selectedIndex].text : '60 min after landing';
				}
			}

			function hideModal(id) {
				var element = document.getElementById(id);
				if (!element) {
					return;
				}
				if (window.bootstrap && window.bootstrap.Modal) {
					window.bootstrap.Modal.getOrCreateInstance(element).hide();
					return;
				}
				element.style.display = 'none';
			}

			function showModal(id) {
				var element = document.getElementById(id);
				if (!element) {
					return;
				}
				if (window.bootstrap && window.bootstrap.Modal) {
					window.bootstrap.Modal.getOrCreateInstance(element).show();
					return;
				}
				element.style.display = 'block';
			}

			function postWorkspace(url, payload, onSuccess) {
				window.jQuery.ajax({
					url: url,
					type: 'POST',
					data: window.jQuery.extend({
						_token: document.querySelector('#manual_booking input[name="_token"]').value
					}, payload || {}),
					success: function (response) {
						if (typeof onSuccess === 'function') {
							onSuccess(response);
						}
					}
				});
			}

			document.querySelectorAll('.workspace-tab').forEach(function (button) {
				button.addEventListener('click', function () {
					applyBookingTab(button.getAttribute('data-booking-tab'));
				});
			});

			var workspaceViaToggle = document.getElementById('workspace-add-via-toggle');
			if (workspaceViaToggle) {
				workspaceViaToggle.addEventListener('change', function () {
					var api = getConsoleApi();
					if (api && typeof api.toggleViaPanel === 'function') {
						api.toggleViaPanel(workspaceViaToggle.checked);
					}
				});
			}

			var returnTripToggle = document.getElementById('workspace-return-trip-toggle');
			if (returnTripToggle) {
				returnTripToggle.addEventListener('change', function () {
					document.getElementById('return_trip_enabled').value = returnTripToggle.checked ? '1' : '0';
				});
			}

			var recurringToggle = document.getElementById('workspace-recurring-toggle');
			if (recurringToggle) {
				recurringToggle.addEventListener('change', function () {
					document.getElementById('recurring_enabled').value = recurringToggle.checked ? '1' : '0';
					if (recurringToggle.checked) {
						applyBookingTab('recurring');
					}
				});
			}

			var applyReturnTripButton = document.getElementById('apply-return-trip-config');
			if (applyReturnTripButton) {
				applyReturnTripButton.addEventListener('click', function () {
					document.getElementById('return_trip_enabled').value = document.getElementById('return_trip_enabled_switch').checked ? '1' : '0';
					document.getElementById('return_date').value = document.getElementById('return_date_input').value;
					document.getElementById('return_time').value = document.getElementById('return_time_input').value;
					document.getElementById('return_pickup_location').value = document.getElementById('return_pickup_location_input').value;
					document.getElementById('return_drop_location').value = document.getElementById('return_drop_location_input').value;
					if (returnTripToggle) {
						returnTripToggle.checked = document.getElementById('return_trip_enabled').value === '1';
					}
				});
			}

			var saveDraftButton = document.getElementById('save-workspace-draft');
			if (saveDraftButton) {
				saveDraftButton.addEventListener('click', function () {
					postWorkspace(REQUEST_URL + '/manual_booking/drafts', {
						draft_id: document.getElementById('draft_id').value,
						name: document.getElementById('draft_name').value,
						payload: formPayload()
					}, function (response) {
						if (response && response.draft) {
							document.getElementById('draft_id').value = response.draft.id;
						}
						hideModal('manualBookingDraftModal');
					});
				});
			}

			var saveTemplateButton = document.getElementById('save-workspace-template');
			if (saveTemplateButton) {
				saveTemplateButton.addEventListener('click', function () {
					postWorkspace(REQUEST_URL + '/manual_booking/templates', {
						template_id: document.getElementById('template_id').value,
						name: document.getElementById('template_name').value,
						description: document.getElementById('template_description').value,
						sharing_scope: document.getElementById('template_scope').value,
						payload: formPayload()
					}, function () {
						hideModal('manualBookingTemplateModal');
					});
				});
			}

			function fillFormFromPayload(payload) {
				if (!payload) {
					return;
				}
				var map = {
					pickup_location: 'input_pickup_location',
					pickup_latitude: 'pickup_latitude',
					pickup_longitude: 'pickup_longitude',
					drop_location: 'input_drop_location',
					drop_latitude: 'drop_latitude',
					drop_longitude: 'drop_longitude',
					via_location: 'input_via_location',
					via_latitude: 'via_latitude',
					via_longitude: 'via_longitude',
					date_time: 'input_date_time',
					vehicle_type: 'input_vehicle_type',
					first_name: 'input_first_name',
					last_name: 'input_last_name',
					email: 'input_email',
					mobile_number: 'input_mobile_number',
					country_code: 'input_country_code',
					country_id: 'country_id',
					gender: 'input_gender',
					dispatcher_note: 'dispatch-driver-note',
					driver_note: 'dispatch-driver-note-field',
					payment_method: 'payment_method',
					customer_type: 'booking_for_type',
					company_name: 'company_name',
					company_phone: 'company_phone',
					customer_reference: 'customer_reference',
					flight_number: 'flight_number',
					airline: 'airline',
					terminal: 'terminal',
					airport_direction: 'airport_direction',
					pickup_sign: 'pickup_sign'
				};

				Object.keys(map).forEach(function (key) {
					var node = document.getElementById(map[key]);
					if (node && typeof payload[key] !== 'undefined') {
						node.value = payload[key] || '';
						if (window.jQuery) {
							window.jQuery(node).trigger('change');
						}
					}
				});

				if (payload.booking_mode) {
					syncBookingModeUI(payload.booking_mode);
				}
				if (payload.dispatch_mode) {
					syncDispatchModeUI(payload.dispatch_mode);
				}
				applyBookingTab(payload.booking_experience_tab || 'asap');

				if (payload.return_trip) {
					document.getElementById('return_trip_enabled').value = payload.return_trip.enabled ? '1' : '0';
					document.getElementById('return_date').value = payload.return_trip.date || '';
					document.getElementById('return_time').value = payload.return_trip.time || '';
					document.getElementById('return_pickup_location').value = payload.return_trip.pickup_location || '';
					document.getElementById('return_drop_location').value = payload.return_trip.drop_location || '';
				}
			}

			document.querySelectorAll('.apply-template-button').forEach(function (button) {
				button.addEventListener('click', function () {
					var payload = button.getAttribute('data-template');
					document.getElementById('template_id').value = button.getAttribute('data-template-id') || '';
					fillFormFromPayload(payload ? JSON.parse(payload) : {});
					hideModal('manualBookingLoadTemplateModal');
				});
			});

			var historyButtons = document.querySelectorAll('[data-quick-action="customer-history"]');
			historyButtons.forEach(function (button) {
				button.addEventListener('click', function () {
					postWorkspace(REQUEST_URL + '/manual_booking/customer-history', {
						mobile_number: document.getElementById('input_mobile_number').value,
						user_id: document.getElementById('user_id').value
					}, function (response) {
						var target = document.getElementById('manual-booking-history-list');
						target.innerHTML = '';
						if (!response.history || !response.history.length) {
							target.innerHTML = '<div class="history-item text-muted">No previous manual bookings found for this passenger.</div>';
							return;
						}

						response.history.forEach(function (item) {
							var div = document.createElement('div');
							div.className = 'history-item';
							div.innerHTML = '<div class="fw-bold">#' + item.id + ' <span class="text-muted small">' + item.status + '</span></div>'
								+ '<div class="small text-muted">' + item.pickup + ' to ' + item.drop + '</div>'
								+ '<div class="small text-muted">' + item.date_time + '</div>';
							target.appendChild(div);
						});
					});
					showModal('manualBookingHistoryModal');
				});
			});

			document.querySelectorAll('[data-quick-action]').forEach(function (button) {
				button.addEventListener('click', function () {
					switch (button.getAttribute('data-quick-action')) {
						case 'focus-pickup':
							focusIfExists('input_pickup_location');
							break;
						case 'estimate-route':
							var api = getConsoleApi();
							if (api) {
								api.refreshDrivers();
								api.fitRoute();
							}
							break;
						case 'return-trip':
							showModal('manualBookingReturnTripModal');
							break;
						case 'via-stop':
							if (workspaceViaToggle) {
								workspaceViaToggle.checked = true;
							}
							if (window.manualBookingConsole && window.manualBookingConsole.toggleViaPanel) {
								window.manualBookingConsole.toggleViaPanel(true);
							}
							break;
						case 'clear-form':
							window.location.href = "{{ url(LOGIN_USER_TYPE.'/manual_booking') }}";
							break;
						case 'save-template':
							showModal('manualBookingTemplateModal');
							break;
						case 'load-template':
							showModal('manualBookingLoadTemplateModal');
							break;
					}
				});
			});

			if (window.MANUAL_BOOKING_PAYLOAD && Object.keys(window.MANUAL_BOOKING_PAYLOAD).length) {
				applyBookingTab(window.MANUAL_BOOKING_PAYLOAD.booking_experience_tab || 'asap');
			}
			else {
				applyBookingTab(document.getElementById('booking_experience_tab').value || 'asap');
			}

			if ((document.getElementById('booking_experience_tab').value || 'asap') === 'later') {
				syncLaterScheduleFields(true);
			}

			var successModal = document.getElementById('manualBookingSuccessModal');
			if (successModal) {
				successModal.addEventListener('click', function (event) {
					if (event.target === successModal) {
						successModal.remove();
					}
				});
			}

			window.setInterval(updateChecklist, 1500);
		});
	})();
</script>
@endpush
