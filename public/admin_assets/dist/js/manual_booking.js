app.controller('manual_booking', ['$scope', '$http', '$compile', '$filter','$q', function($scope, $http, $compile, $filter,$q) {

	function hasGoogleMaps() {
		return typeof window.google !== 'undefined'
			&& typeof window.google.maps !== 'undefined'
			&& typeof window.google.maps.places !== 'undefined';
	}

	function getDispatchSettings() {
		return window.MANUAL_DISPATCH_SETTINGS || {};
	}

	function getManualBookingDefaults() {
		return window.MANUAL_BOOKING_DEFAULTS || {};
	}

	function getBookingMode() {
		return ($('#booking_mode').val() || 'asap').toLowerCase();
	}

	function isDispatchOverrideEnabled() {
		return $('#is_dispatch_override').val() == '1';
	}

	function getMinimumLeadMinutes() {
		if (getBookingMode() === 'asap' || isDispatchOverrideEnabled()) {
			return 0;
		}

		return parseInt(getDispatchSettings().minimum_scheduled_lead_minutes || 15, 10);
	}

	function applyCountryDefaults() {
		var defaults = getManualBookingDefaults();
		var countryCodeInput = $('#input_country_code');
		var countryIdInput = $('#country_id');
		var phoneCode = String(countryCodeInput.val() || defaults.phone_code || '').replace(/^\+/, '');
		var selectedCountryId = countryCodeInput.is('select') ? countryCodeInput.find(':selected').attr('data-id') : null;
		var countryId = countryIdInput.val() || selectedCountryId || defaults.country_id || '';

		if (countryCodeInput.length) {
			countryCodeInput.val(phoneCode);
		}
		if (countryIdInput.length) {
			countryIdInput.val(countryId);
		}

		var phonePrefixView = $('#country_code_view');
		if (phonePrefixView.length) {
			phonePrefixView.val(phoneCode ? '+' + phoneCode : '');
		}

		var countryName = $('#dispatch-country-name');
		if (countryName.length && defaults.country_name) {
			countryName.text(defaults.country_name);
		}
	}

	function toggleViaPanel(forceOpen) {
		var viaPanel = $('#dispatch-via-panel');
		if (!viaPanel.length) {
			return;
		}
		if (forceOpen === true) {
			viaPanel.addClass('active');
		}
		else if (forceOpen === false) {
			viaPanel.removeClass('active');
		}
		else {
			viaPanel.toggleClass('active');
		}
	}

	function clearViaStop() {
		$('#input_via_location').val('');
		$('#via_latitude').val('');
		$('#via_longitude').val('');
		if (typeof $scope.via_marker !== 'undefined' && $scope.via_marker) {
			$scope.via_marker.setMap(null);
			$scope.via_marker = null;
		}
		calculateAndDisplayRoute();
	}

	$('#input_filter').multiselect({
		placeholder: 'Filter',
		optionAttributes: ['disabled'],
		onOptionClick : function( element, option ){
			getVehicleTypes();	
		}
	});

	$(document).on('click', '#dispatch-add-via', function(){
		toggleViaPanel(true);
		setTimeout(function(){
			$('#input_via_location').focus();
		}, 20);
	});

	$(document).on('click', '#dispatch-remove-via', function(){
		clearViaStop();
		toggleViaPanel(false);
	});

	$scope.gender = '';
	$scope.driverFilterMode = 'all';
	$scope.setDriverFilterMode = function(mode) {
		$scope.driverFilterMode = mode || 'all';
	};
	$scope.driverEtaLabel = function(driver) {
		if (!driver) {
			return 'Pending';
		}
		var distance = parseFloat(driver.distance_km || 0);
		return Math.max(1, Math.ceil(distance * 3)) + ' min';
	};
	$scope.driverLastSeenLabel = function(driver) {
		if (!driver) {
			return 'Unknown';
		}
		if (driver.driver_current_status === 'Offline') {
			return 'Offline';
		}
		if (driver.driver_current_status === 'Trip' || driver.driver_current_status === 'Pool Trip') {
			return 'On trip';
		}
		return 'Live now';
	};
	$scope.getFilteredDrivers = function() {
		var drivers = Array.isArray($scope.drivers) ? $scope.drivers.slice() : [];
		var availability = ($scope.driver_availability || '').toLowerCase();
		var search = ($scope.search_driver || '').toLowerCase();
		var mode = ($scope.driverFilterMode || 'all').toLowerCase();
		var defaults = getManualBookingDefaults();

		return drivers.filter(function(driver) {
			var firstName = String(driver.first_name || '').toLowerCase();
			var lastName = String(driver.last_name || '').toLowerCase();
			var phone = String(driver.mobile_number || '').toLowerCase();
			var status = String(driver.driver_current_status || 'Available').toLowerCase();
			var companyId = String(driver.company_id || '');
			var isAvailable = status === '' || status === 'available' || status === 'online';
			var isNearby = parseFloat(driver.distance_km || 0) <= 5;
			var isExternal = defaults.company_id ? (companyId !== '' && companyId !== String(defaults.company_id)) : false;

			if (search && firstName.indexOf(search) === -1 && lastName.indexOf(search) === -1 && phone.indexOf(search) === -1) {
				return false;
			}

			if (availability && status !== availability.toLowerCase()) {
				return false;
			}

			if (mode === 'available' && !isAvailable) {
				return false;
			}
			if (mode === 'nearby' && !isNearby) {
				return false;
			}
			if (mode === 'company' && isExternal) {
				return false;
			}
			if (mode === 'external' && !isExternal) {
				return false;
			}

			return true;
		}).sort(function(a, b) {
			return parseFloat(a.distance_km || 0) - parseFloat(b.distance_km || 0);
		});
	};
	$scope.getRecommendedDriver = function() {
		var drivers = $scope.getFilteredDrivers();
		return drivers.length ? drivers[0] : null;
	};
	$scope.$watch('gender',function(v) {
		if(v=='1')  {
			$("#input_filter option[value='4']").prop('selected',false);
			$("#input_filter option[value='4']").prop('disabled',true);
		}
		else
			$("#input_filter option[value='4']").prop('disabled',false);
		$("#input_filter").multiselect("reload");
		getVehicleTypes();
	});

	$(document).ready(function(){
		if ($('#manual_booking_id').val() == '') {
			$('#input_first_name').val('');
			$('#input_last_name').val('');
			$('#input_email').val('');
			$('#input_mobile_number').val('')
			$('#input_date_time').val('')
			$('#input_gender').val('');
			$('#user_id').val('');
		}
		if ($('#input_first_name').val()!='') {
			$('#input_first_name').prop('readonly', true);
			$('#input_last_name').prop('readonly', true);
			$('#input_email').prop('readonly', true);
			$('#input_gender').attr('disabled', true);
		}
		applyCountryDefaults();
		var phone_select = 0;
		var user_list = []
		//Auto complete to mobile number
		$( "#input_mobile_number" ).autocomplete({
			source: function( request, response ) {
				$.ajax({
					type: 'POST',
					url: REQUEST_URL+'/search_phone',
					data: {
						type: 'rider',
						text: $( "#input_mobile_number" ).val(),
						country_code: $( "#input_country_code" ).val()
					},
					dataType: "json",
					success: function(data) {
						var users = [];
						user_list = [];
						for (var i = 0; i < data.length; i++) {
							user_list[data[i].mobile_number] = users[i] = {
								value 		: data[i].mobile_number,
								first_name 	: data[i].first_name,
								last_name 	: data[i].last_name,
								email 		: data[i].email,
								gender 		: data[i].gender,
								id 			: data[i].id,
								filter 		: data[i].filter
							}
						}
						response(users);
					}
				});
			},
			select: function(event, ui) {

				var filter = ui.item.filter;
				filter_size = filter.length;
				for(i=0; i<filter_size; i++) {
				    $("#input_filter option[value='" + filter[i] + "']").prop("selected", "selected");
				}
				$("#input_filter").multiselect("reload");

				$('#input_first_name').val(ui.item.first_name);
				$('#input_last_name').val(ui.item.last_name);
				$('#input_email').val(ui.item.email);
				$('#input_gender').val(ui.item.gender);
				$('#user_id').val(ui.item.id);

				$scope.gender = ui.item.gender; 
				$scope.$apply();

				$('#input_first_name').prop('readonly', true);
				$('#input_last_name').prop('readonly', true);
				$('#input_email').prop('readonly', true);
				$('#input_gender').attr('disabled', true);
				phone_select = 1;

				getVehicleTypes();
			}
		});

		$( "#input_mobile_number" ).keyup(function(){
			if (typeof user_list[$(this).val()] !== 'undefined') {
				$('#input_first_name').val(user_list[$(this).val()].first_name);
				$('#input_last_name').val(user_list[$(this).val()].last_name);
				$('#input_email').val(user_list[$(this).val()].email);
				$('#user_id').val(user_list[$(this).val()].id);
				$('#input_gender').val(user_list[$(this).val()].gender);
				$('#input_first_name').prop('readonly', true);
				$('#input_last_name').prop('readonly', true);
				$('#input_email').prop('readonly', true);
				$('#input_gender').attr('disabled', true);
				getVehicleTypes();
			}else {
				$('#input_first_name').prop('readonly', false);
				$('#input_last_name').prop('readonly', false);
				$('#input_email').prop('readonly', false);
				$('#input_gender').removeAttr('disabled');
				$('#input_first_name').val('');
				$('#input_last_name').val('');
				$('#input_email').val('');
				$('#input_gender').val('');
				$('#user_id').val('');
			}
		});
	});

	$('#input_country_code').change(function(){
		applyCountryDefaults();
	});

    $scope.Driverfilter = function( driver ) {
	  return function( item ) {
	  	if(typeof item==='undefined' || typeof driver==='undefined') {
	  		return true;
	  	}
	    return ((item.first_name).toLowerCase()).includes(driver.toLowerCase()) || (item.mobile_number).includes(driver) ;
	  };
	};

	var autocomplete;
	$scope.from_marker;
	$scope.to_marker;
	$scope.via_marker;
	$scope.vehicle_detail_km = 0
    $scope.vehicle_detail_minutes = 0
    $scope.vehicle_detail_km_fare = 0
    $scope.vehicle_detail_min_fare = 0
    $scope.vehicle_detail_total_fare = 0
	$scope.vehicle_detail_minimum_fare = 0
	$scope.vehicle_detail_base_fare = 0
	$scope.vehicle_detail_peak_price = 0
	$scope.ignore_assigned = []
	$scope.mapRadius = 13
	$scope.mapMode = 'map'
	$scope.from_pin= APP_URL+'/images/PinFrom.png'
	$scope.to_pin= APP_URL+'/images/PinTo.png'
	$scope.vehicle_detail_peak_fare = 0
	$scope.consoleLayers = {
		heatmap: null,
		zones: [],
	}
	if (hasGoogleMaps()) {
		initAutocomplete();
		initMap();
	}
	else {
		console.warn('Google Maps failed to load for manual booking.');
	}

	window.manualBookingConsole = window.manualBookingConsole || {};
	window.manualBookingConsole.clearViaStop = clearViaStop;
	window.manualBookingConsole.toggleViaPanel = toggleViaPanel;

	var autoCompleteOptions = {
		fields: ['place_id', 'name', 'types','formatted_address','address_components','geometry','utc_offset']
	};

	//Auto complete to pickup & drop location
	function initAutocomplete()
	{
  		pickup_location_autocomplete = new google.maps.places.Autocomplete(document.getElementById('input_pickup_location'),autoCompleteOptions);
  	  	pickup_location_autocomplete.addListener('place_changed', pickup_location_address);

  	  	drop_location_autocomplete = new google.maps.places.Autocomplete(document.getElementById('input_drop_location'),autoCompleteOptions);
  	  	drop_location_autocomplete.addListener('place_changed', drop_location_Address);

		var viaInput = document.getElementById('input_via_location');
		if (viaInput) {
			via_location_autocomplete = new google.maps.places.Autocomplete(viaInput,autoCompleteOptions);
			via_location_autocomplete.addListener('place_changed', via_location_address);
		}
   
	}

	function pickup_location_address() 
	{
	    pickup_place = pickup_location_autocomplete.getPlace();
	    $('#input_pickup_location').val(pickup_place.formatted_address);
		$scope.utc_offset  = pickup_place.utc_offset;

		$scope.pickup_latitude  = pickup_place.geometry.location.lat();
		$scope.pickup_longitude = pickup_place.geometry.location.lng();
		$('#pickup_latitude').val($scope.pickup_latitude)
		$('#pickup_longitude').val($scope.pickup_longitude)
		$('#utc_offset').val($scope.utc_offset)
		if (typeof $scope.from_marker !== 'undefined') {
			$scope.from_marker.setMap(null);
		}
	   	
		$scope.from_marker = new google.maps.Marker({
          	map: $scope.map,
          	draggable: true,
          	icon: $scope.from_pin,
          	animation: google.maps.Animation.DROP,
          	position: {lat: $scope.pickup_latitude, lng: $scope.pickup_longitude}
        });
        $scope.map.setZoom($scope.mapRadius);
		$scope.map.panTo($scope.from_marker.position);
        $scope.from_marker.addListener('dragend', fromMarkerDrag);
		calculateAndDisplayRoute();
		getVehicleTypes();
		if (typeof $scope.pickup_latitude === "undefined"||typeof $scope.pickup_longitude === "undefined") {
        	$('#input_map_zoom').attr("disabled", true); 
      	}else{
        	$('#input_map_zoom').attr("disabled", false); 
      	}
	}
	
	function drop_location_Address() 
	{
	    drop_place = drop_location_autocomplete.getPlace();
	    $('#input_drop_location').val(drop_place.formatted_address);
		$scope.drop_latitude  = drop_place.geometry.location.lat();
		$scope.drop_longitude = drop_place.geometry.location.lng();
		$('#drop_latitude').val($scope.drop_latitude)
		$('#drop_longitude').val($scope.drop_longitude)
		if (typeof $scope.to_marker !== 'undefined') {
			$scope.to_marker.setMap(null);
		}
		$scope.to_marker = new google.maps.Marker({
          	map: $scope.map,
          	draggable: true,
          	icon: $scope.to_pin,
          	animation: google.maps.Animation.DROP,
          	position: {lat: $scope.drop_latitude, lng: $scope.drop_longitude}
        });
        $scope.to_marker.addListener('dragend', toMarkerDrag);
        $scope.map.setZoom($scope.mapRadius);
		$scope.map.panTo($scope.to_marker.position);
		calculateAndDisplayRoute();
		getVehicleTypes();
	}

	function via_location_address()
	{
		via_place = via_location_autocomplete.getPlace();
		if (!via_place || !via_place.geometry) {
			return;
		}
		$('#input_via_location').val(via_place.formatted_address);
		$('#via_latitude').val(via_place.geometry.location.lat());
		$('#via_longitude').val(via_place.geometry.location.lng());
		if (typeof $scope.via_marker !== 'undefined' && $scope.via_marker) {
			$scope.via_marker.setMap(null);
		}
		$scope.via_marker = new google.maps.Marker({
          	map: $scope.map,
          	draggable: true,
          	icon: $scope.to_pin,
          	animation: google.maps.Animation.DROP,
          	position: {lat: via_place.geometry.location.lat(), lng: via_place.geometry.location.lng()}
        });
		$scope.via_marker.addListener('dragend', viaMarkerDrag);
		calculateAndDisplayRoute();
	}

	function viaMarkerDrag(evt){
		$('#via_latitude').val(evt.latLng.lat());
		$('#via_longitude').val(evt.latLng.lng());
		var latlng = {lat: evt.latLng.lat(), lng: evt.latLng.lng()};
		calculateAndDisplayRoute();
		getLocation(latlng,'input_via_location');
	}


	$('#input_pickup_location,#input_drop_location,#input_via_location').change(function(){
		if($('#input_pickup_location').val() == '' || $('#input_drop_location').val() == ''){
			$('#input_date_time').attr('disabled',true)
		}else{ 
			$('#input_date_time').attr('disabled',false)
		}
	});

	//get car types list
	function getVehicleTypes(id=0) {
		date_time=$('#input_date_time').val()
		
		if (date_time=='' || date_time==' ' ) {
			var today = new Date();
			var leadMinutes = getMinimumLeadMinutes();
			if ($scope.utc_offset == '') {
        		var currenct_date = new Date(today.getTime() + (leadMinutes * 60 * 1000));
        	}else{
				var currenct_date = new Date( today.getTime() + ((today.getTimezoneOffset()+$scope.utc_offset+leadMinutes) * 60000));
        	}
			$('#input_date_time').val($filter('date')(new Date(currenct_date),'yyyy-MM-dd HH:mm'))
		}
      	if (typeof $scope.pickup_latitude === "undefined"||typeof $scope.pickup_longitude === "undefined"||typeof $scope.drop_latitude === "undefined"||typeof $scope.drop_longitude === "undefined") {
        	return false;
      	}
	    if(!$('#input_auto_assign_status').is(":checked")){
			$('.loader').show()
			$('.driver_list').hide()
			$('.submit_button').attr('disabled',true)
		}
		$scope.from_marker.setDraggable(false);
		$scope.to_marker.setDraggable(false);
		$('.change_field').attr('disabled', true)
		if (typeof $('#input_driver_availability')[0].selectize !== 'undefined') {
			$('#input_driver_availability')[0].selectize.disable(); 
		}

		date_time = $('#input_date_time').val();
		user_id = $('#user_id').val();
		pickup_location = $('#input_pickup_location').val();
		drop_location = $('#input_drop_location').val();
		gender = $('#input_gender').val();
		filter = $('#input_filter').val();



		var canceller,
        isSending = false;

        if(isSending) {
            canceller.resolve()
        }
        isSending = true;
        canceller = $q.defer();


		$http.post(REQUEST_URL+'/search_cars', {
			type: 'online',
			pickup_latitude: $scope.pickup_latitude,
			pickup_longitude: $scope.pickup_longitude,
			drop_latitude: $scope.drop_latitude,
			drop_longitude: $scope.drop_longitude,
			pickup_location: pickup_location,
			drop_location: drop_location,
			date_time: date_time,
			user_id: user_id,
			gender: gender,
			filter: filter
		},{timeout: canceller.promise}).then(function(response) {
			isSending = false;
			response = response.data
			if (response.status_code==0) {
				$('.error_msg').hide();
				$('.error_msg').html('');
				if (response.status_message == 'location_unavailable') {
					$('.error_pickup_location').show();
					$('.error_pickup_location').html(response.trans_message);
				}else if(response.status_message == 'location_country'){
					$('.error_drop_location').show();
					$('.error_drop_location').html(response.trans_message);
				}else if(response.status_message == 'no_cars_found'){
					$('.error_vehicle_type').show();
					$('.error_vehicle_type').html(response.trans_message);
				}
				$('#input_vehicle_type').attr("disabled", true); 

				$scope.vehicle_type_value = ''
				$scope.vehicle_types = []
				$('.driver_list').hide();
				$('.submit_button').attr('disabled',true)
				$('.loader').hide();
				$('.change_field').attr('disabled', false)
				$('#input_driver_availability')[0].selectize.enable(); 
				$scope.from_marker.setDraggable(true);
				$scope.to_marker.setDraggable(true);
				$('#auto_assign_id').val(0)
				$('.assigned_driver').hide();

			}else{
				$('.error_msg').hide();
				$('#input_vehicle_type').attr("disabled", false);
				$.each(response.vehicle_type, function( index, value ) {
					if (typeof $scope.vehicle_type_value !== "undefined" && $scope.vehicle_type_value != '') {
						index = $scope.vehicle_type_value
					}
					$scope.default_car_index = index
					$scope.vehicle_types = response.vehicle_type
					$scope.vehicle_type_value = index
					if (id!=0) {
						$scope.auto_assign(id);
					}
					$scope.list_driver()
					return false
				});
			}
		});
    }

	//get drivers list
    $scope.list_driver = function(){
    	if(!$('#input_auto_assign_status').is(":checked")){
			$('.loader').show()
			$('.submit_button').attr('disabled',true)
			$('.driver_list').hide()
		}
		if (typeof $('#input_driver_availability')[0].selectize !== 'undefined') {
			$('#input_driver_availability')[0].selectize.disable(); 
		}
		$scope.from_marker.setDraggable(false);
		$scope.to_marker.setDraggable(false);
		$('.change_field').attr('disabled', true)
    	if (typeof $scope.pickup_latitude === "undefined"||typeof $scope.pickup_longitude === "undefined"||typeof $scope.drop_latitude === "undefined"||typeof $scope.drop_longitude === "undefined") {
        	return false;
      	}
      	$('.assigned_driver').hide();
		$('#auto_assign_id').val(0)
      	date_time=$('#input_date_time').val()
		if (date_time=='') {
			var today = new Date();
			$('#input_date_time').val($filter('date')(new Date(today),'yyyy-MM-dd HH:mm'))
		}
    	var element = $('#input_vehicle_type').find('option:selected'); 
    	var key = element.attr("key"); 
    	if (typeof key === "undefined" || key==0) {
	    	key = $scope.default_car_index
	    }

		user_id = $('#user_id').val();
		gender = $('#input_gender').val();
		filter = $('#input_filter').val();



		var canceller,
        isSending = false;

        if(isSending) {
            canceller.resolve()
        }
        isSending = true;
        canceller = $q.defer();


		$http.post(REQUEST_URL+'/driver_list', {
			pickup_latitude: $scope.pickup_latitude,
			pickup_longitude: $scope.pickup_longitude,
			drop_latitude: $scope.drop_latitude,
			drop_longitude: $scope.drop_longitude,
			date_time: date_time,
			car_id: key,
			schedule_id: ($('#manual_booking_id').val()=='')?0:$('#manual_booking_id').val(),
			user_id: user_id,
			gender: gender,
			filter: filter
		},{timeout: canceller.promise}).then(function(response) {
			isSending = false;
			response = response.data
			$scope.drivers = response
			$scope.driverFilterMode = $scope.driverFilterMode || 'all';
			if(!$('#input_auto_assign_status').is(":checked")){
				$('.driver_list').show();
			}
			$('.submit_button').removeAttr('disabled')
			$('.loader').hide();
			$('.change_field').attr('disabled', false)
			$('#input_driver_availability')[0].selectize.enable(); 
			$scope.from_marker.setDraggable(true);
			$scope.to_marker.setDraggable(true);
			$scope.vehicle_detail = $scope.vehicle_types[key]
			fare_detail()
			$scope.setMapMode($scope.mapMode || 'map')
		});
	    
	}

	//show fare details
	function fare_detail()
	{
    	$scope.vehicle_detail_km = $scope.vehicle_detail.km
    	$scope.vehicle_detail_minutes = $scope.vehicle_detail.minutes
    	$scope.vehicle_detail_km_fare = parseFloat($scope.vehicle_detail_km) * parseFloat($scope.vehicle_detail.per_km)
    	$scope.vehicle_detail_min_fare = parseFloat($scope.vehicle_detail_minutes) * parseFloat($scope.vehicle_detail.per_min)
    	$scope.vehicle_detail_total_fare = $scope.vehicle_detail.fare_estimation
    	$scope.vehicle_detail_minimum_fare = $scope.vehicle_detail.min_fare
    	$scope.vehicle_detail_base_fare = $scope.vehicle_detail.base_fare
    	peak_price = Number($scope.vehicle_detail.peak_price)
    	$scope.vehicle_detail_peak_price = peak_price.toFixed(0)
    	$scope.vehicle_detail_peak_fare = $scope.vehicle_detail.peak_fare
    	$('#location_id').val($scope.vehicle_detail.location_id)
    	$('#peak_id').val($scope.vehicle_detail.peak_id)
	}

	//init map
	function initMap() {
		$scope.directionsService = new google.maps.DirectionsService;
        $scope.directionsDisplay = new google.maps.DirectionsRenderer({preserveViewport: true});
        $scope.geocoder = new google.maps.Geocoder;
		var mapCanvas = document.getElementById('map');
		if(!mapCanvas){
		  	return false;
		}
		var mapOptions = {
				zoom: 2,
				minZoom: 1,
				zoomControl: true,
				center:{lat: 0, lng: 0},
				mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		$scope.map = new google.maps.Map(mapCanvas, mapOptions);
		map = $scope.map;

        $scope.directionsDisplay.setMap($scope.map);
        
	    if($('#pickup_latitude').val() != '' && $('#pickup_longitude').val() != '' && $('#drop_latitude').val() != '' && $('#drop_longitude').val() != ''){
	    	$scope.pickup_latitude = parseFloat($('#pickup_latitude').val())
		    $scope.pickup_longitude = parseFloat($('#pickup_longitude').val())
		    $scope.drop_latitude = parseFloat($('#drop_latitude').val())
		    $scope.drop_longitude = parseFloat($('#drop_longitude').val())
		    $scope.from_marker = new google.maps.Marker({
		      	map: $scope.map,
		      	draggable: true,
          		icon: $scope.from_pin,
		      	animation: google.maps.Animation.DROP,
		      	position: {lat: $scope.pickup_latitude, lng: $scope.pickup_longitude}
		    });
			$scope.to_marker = new google.maps.Marker({
		      	map: $scope.map,
          		icon: $scope.to_pin,
		      	draggable: true,
		      	animation: google.maps.Animation.DROP,
		      	position: {lat: $scope.drop_latitude, lng: $scope.drop_longitude}
		    });
		    $scope.map.setZoom($scope.mapRadius);
			$scope.map.panTo($scope.to_marker.position);
			$scope.from_marker.addListener('dragend', fromMarkerDrag);
			$scope.to_marker.addListener('dragend', toMarkerDrag);
			if($('#via_latitude').val() !== '' && $('#via_longitude').val() !== ''){
				$scope.via_marker = new google.maps.Marker({
			      	map: $scope.map,
	          		icon: $scope.to_pin,
			      	draggable: true,
			      	animation: google.maps.Animation.DROP,
			      	position: {lat: parseFloat($('#via_latitude').val()), lng: parseFloat($('#via_longitude').val())}
			    });
				$scope.via_marker.addListener('dragend', viaMarkerDrag);
			}
    		calculateAndDisplayRoute();
			getVehicleTypes($('#auto_assign_id').val());
		}
    }

	//Show route on map
	function calculateAndDisplayRoute() {
      	if (typeof $scope.pickup_latitude === "undefined"||typeof $scope.pickup_longitude === "undefined"||typeof $scope.drop_latitude === "undefined"||typeof $scope.drop_longitude === "undefined") {
        	return false;
      	}
		clearHeatMap();
		var waypoints = [];
		if ($('#input_via_location').length && $('#input_via_location').val() !== '' && $('#via_latitude').val() !== '' && $('#via_longitude').val() !== '') {
			waypoints.push({
				location: {
					lat: parseFloat($('#via_latitude').val()),
					lng: parseFloat($('#via_longitude').val())
				},
				stopover: true
			});
		}
        $scope.directionsService.route({
          	origin:  {
                    	lat: parseFloat($scope.pickup_latitude),
                        lng: parseFloat($scope.pickup_longitude)
                    },
          	destination: {
                        lat: parseFloat($scope.drop_latitude),
                        lng: parseFloat($scope.drop_longitude)
                    },
			waypoints: waypoints,
          travelMode: 'DRIVING'
        }, function(response, status) {
			if (status === 'OK') {
				$scope.directionsDisplay.setDirections(response);
				$scope.directionsDisplay.setOptions( { suppressMarkers: true } );
				if ($scope.mapMode == 'route') {
					$scope.fitRouteBounds();
				}
			} else {
console.log(status);
				// if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
				// 	calculateAndDisplayRoute();
				// 	return;
				// }
         
				// window.alert('Directions request failed due to ' + status);
			}
        });
    }

	function fromMarkerDrag(evt){
    	$scope.pickup_latitude = evt.latLng.lat()
    	$scope.pickup_longitude = evt.latLng.lng()
    	$('#pickup_latitude').val($scope.pickup_latitude)
    	$('#pickup_longitude').val($scope.pickup_longitude)
		var latlng = {lat: $scope.pickup_latitude, lng: $scope.pickup_longitude};
		calculateAndDisplayRoute();
		getLocation(latlng,'input_pickup_location')
		// $scope.utc_offset  = pickup_place.utc_offset;
		// $('#utc_offset').val($scope.utc_offset)
	}

	function toMarkerDrag(evt){
    	$scope.drop_latitude = evt.latLng.lat()
    	$scope.drop_longitude = evt.latLng.lng()
    	$('#drop_latitude').val($scope.drop_latitude)
		$('#drop_longitude').val($scope.drop_longitude)
		var latlng = {lat: $scope.drop_latitude, lng: $scope.drop_longitude};
		calculateAndDisplayRoute();
		getLocation(latlng,'input_drop_location')
	}

	//find location from latlang
	function getLocation(latlng,field) {
    	$scope.geocoder.geocode({'location': latlng}, function(results, status) {
		    if (status === 'OK') {
		      	if (results[0]) {
	    			$('#'+field).val(results[0].formatted_address);
					getVehicleTypes();
		      	} else {
		        	window.alert('No results found');
		      	}
		    } else {
		      window.alert('Please choose the valid location');
		    }
		});
	}

	function clearHeatMap() {
		if ($scope.consoleLayers.heatmap) {
			$scope.consoleLayers.heatmap.setMap(null);
			$scope.consoleLayers.heatmap = null;
		}
	}

	function clearZonePolygons() {
		angular.forEach($scope.consoleLayers.zones, function(zone) {
			zone.setMap(null);
		});
		$scope.consoleLayers.zones = [];
	}

	function getRouteBounds() {
		var bounds = new google.maps.LatLngBounds();
		var hasBounds = false;
		var directions = $scope.directionsDisplay ? $scope.directionsDisplay.getDirections() : null;

		if ($scope.from_marker && $scope.from_marker.getPosition()) {
			bounds.extend($scope.from_marker.getPosition());
			hasBounds = true;
		}

		if ($scope.to_marker && $scope.to_marker.getPosition()) {
			bounds.extend($scope.to_marker.getPosition());
			hasBounds = true;
		}

		if (directions && directions.routes && directions.routes.length) {
			bounds = directions.routes[0].bounds;
			hasBounds = true;
		}

		return hasBounds ? bounds : null;
	}

	function setRouteVisibility(visible) {
		if (!$scope.directionsDisplay) {
			return;
		}

		$scope.directionsDisplay.setOptions({
			suppressMarkers: true,
			polylineOptions: {
				strokeColor: '#2563eb',
				strokeOpacity: visible ? 0.95 : 0,
				strokeWeight: 5,
			}
		});
	}

	//Map zoom by filter
	$scope.map_zoom = function(radius) {
		if (radius == 0) {
			$scope.mapRadius = 13
			if ($scope.pickup_latitude != '' && $scope.pickup_longitude != '') {
	    		$scope.map.setZoom($scope.mapRadius);
	    	}
		}else{
		    var newRadius = Math.round(24 - Math.log(radius) / Math.LN2);
		    $scope.mapRadius = newRadius - 9;
		    if ($scope.pickup_latitude != '' && $scope.pickup_longitude != '') {
				var pt = new google.maps.LatLng($scope.pickup_latitude, $scope.pickup_longitude);
			    $scope.map.setCenter(pt);
		    	$scope.map.setZoom($scope.mapRadius);
			}
		}
	}
	$scope.utc_offset=''

	$scope.fitRouteBounds = function() {
		var bounds = getRouteBounds();
		if (bounds) {
			$scope.map.fitBounds(bounds, 80);
		}
	};

	$scope.recenterMap = function() {
		if ($scope.from_marker && $scope.from_marker.getPosition()) {
			$scope.map.panTo($scope.from_marker.getPosition());
			$scope.map.setZoom($scope.mapRadius);
			return;
		}

		$scope.fitRouteBounds();
	};

	$scope.focusMarker = function(type) {
		var marker = type == 'pickup' ? $scope.from_marker : $scope.to_marker;
		if (marker && marker.getPosition()) {
			$scope.map.panTo(marker.getPosition());
			$scope.map.setZoom(Math.max($scope.mapRadius, 15));
		}
	};

	$scope.refreshDispatchData = function() {
		if (typeof $scope.pickup_latitude === "undefined" || typeof $scope.drop_latitude === "undefined") {
			return;
		}

		if ($('#input_vehicle_type').val()) {
			$scope.list_driver();
			return;
		}

		getVehicleTypes();
	};

	$scope.clearRouteConsole = function() {
		$('#input_pickup_location, #input_drop_location, #pickup_latitude, #pickup_longitude, #drop_latitude, #drop_longitude').val('');
		$('#location_id, #peak_id, #auto_assign_id').val('');
		$scope.pickup_latitude = undefined;
		$scope.pickup_longitude = undefined;
		$scope.drop_latitude = undefined;
		$scope.drop_longitude = undefined;
		$scope.vehicle_type_value = '';
		$scope.vehicle_types = [];
		$scope.drivers = [];
		$scope.assigned_driver = {};
		$scope.vehicle_detail_km = 0;
		$scope.vehicle_detail_minutes = 0;
		$scope.vehicle_detail_km_fare = 0;
		$scope.vehicle_detail_min_fare = 0;
		$scope.vehicle_detail_total_fare = 0;
		$scope.vehicle_detail_minimum_fare = 0;
		$scope.vehicle_detail_base_fare = 0;
		$scope.vehicle_detail_peak_price = 0;
		$scope.vehicle_detail_peak_fare = 0;
		$scope.directionsDisplay.set('directions', null);
		clearOverlays();
		clearHeatMap();
		clearZonePolygons();
		$('.driver_list').hide();
		$('.assigned_driver').hide();
		if ($scope.from_marker) {
			$scope.from_marker.setMap(null);
			$scope.from_marker = undefined;
		}
		if ($scope.to_marker) {
			$scope.to_marker.setMap(null);
			$scope.to_marker = undefined;
		}
	};

	$scope.renderHeatMapLayer = function() {
		clearOverlays();
		clearZonePolygons();
		setRouteVisibility(false);

		$.getJSON(REQUEST_URL + '/manual_booking_heat_map', function(data) {
			var heatMapData = [];
			angular.forEach(data, function(item) {
				if (item.weight > 1) {
					heatMapData.push({
						location: new google.maps.LatLng(item.pickup_latitude, item.pickup_longitude),
						weight: item.weight
					});
				}
				else {
					heatMapData.push(new google.maps.LatLng(item.pickup_latitude, item.pickup_longitude));
				}
			});

			clearHeatMap();
			$scope.consoleLayers.heatmap = new google.maps.visualization.HeatmapLayer({
				data: heatMapData,
				radius: 28,
				opacity: 0.85,
			});
			$scope.consoleLayers.heatmap.setMap($scope.map);
		});
	};

	$scope.renderZonesLayer = function() {
		clearZonePolygons();
		clearHeatMap();
		setRouteVisibility(true);

		$.getJSON(REQUEST_URL + '/manual_booking_zones', function(zones) {
			var bounds = new google.maps.LatLngBounds();
			var hasBounds = false;

			angular.forEach(zones, function(zone) {
				if (!zone.coordinates || !zone.coordinates.length) {
					return;
				}

				var path = zone.coordinates.map(function(point) {
					var latLng = { lat: parseFloat(point.lat), lng: parseFloat(point.lng) };
					bounds.extend(latLng);
					hasBounds = true;
					return latLng;
				});

				var polygon = new google.maps.Polygon({
					paths: path,
					strokeColor: '#2563eb',
					strokeOpacity: 0.8,
					strokeWeight: 2,
					fillColor: '#60a5fa',
					fillOpacity: 0.12,
					map: $scope.map,
				});

				polygon.addListener('click', function(event) {
					infoWindow.setContent('<div><strong>' + zone.name + '</strong></div>');
					infoWindow.setPosition(event.latLng);
					infoWindow.open($scope.map);
				});

				$scope.consoleLayers.zones.push(polygon);
			});

			if (hasBounds && (!$scope.from_marker || !$scope.to_marker)) {
				$scope.map.fitBounds(bounds, 60);
			}
		});
	};

	$scope.setMapMode = function(mode) {
		$scope.mapMode = mode || 'map';

		if ($scope.mapMode == 'heatmap') {
			$scope.renderHeatMapLayer();
			return;
		}

		clearHeatMap();

		if ($scope.mapMode == 'zones') {
			$scope.renderZonesLayer();
		}
		else {
			clearZonePolygons();
		}

		if ($scope.mapMode == 'route') {
			setRouteVisibility(true);
			$scope.fitRouteBounds();
			return;
		}

		if ($scope.mapMode == 'parking') {
			setRouteVisibility(false);
			$scope.MapData(true);
			$scope.recenterMap();
			return;
		}

		setRouteVisibility(true);
		$scope.MapData(false);
	};
	$( function() {
		if ($scope.date_time == '') {
			$scope.date_time = moment()
		}
		var today = new Date();
	    $('#input_date_time').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            // minDate:  moment(),
            ignoreReadonly: true,
            sideBySide: true,
        }).on('dp.hide', function (e) {
            $('#input_date_time').data("DateTimePicker").minDate(formatDate(today))
             // $(this).data('DateTimePicker').show();
			getVehicleTypes();
        });
	} );

	//datetime picker
	function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
    $('.reset').click(function(e) {
    	e.preventDefault();
	    location.reload();
	});

	$('#input_auto_assign_status').change(function(){
		if($('#input_auto_assign_status').is(":checked")){
			$('.assigned_driver').hide();
			$('.driver_list').hide();
			$('#auto_assign_id').val(0)
			$scope.auto_assign(0);
		}
		else{ 
			if ($('#auto_assign_id').val()!=0) {
				$('.assigned_driver').show();
			}
			$('.driver_list').show();
			$('.submit_button').attr('disabled',true)
		}
	})

	//Driver auto assign functionality
	$scope.auto_assign = function(driver_id) {
		$('.submit_button').attr('disabled',true)
		date_time=$('#input_date_time').val()
		if (date_time=='') {
			var today = new Date();
			$('#input_date_time').val($filter('date')(new Date(today),'yyyy-MM-dd HH:mm'))
		}
		date_time=$('#input_date_time').val()
		$http.post(REQUEST_URL+'/get_driver', {
      			driver_id: driver_id,
      			date_time: date_time,
      			utc_offset: $scope.utc_offset,
      			booking_mode: getBookingMode(),
      			is_dispatch_override: isDispatchOverrideEnabled() ? 1 : 0,
      		}).then(function(response) {
      		response = response.data
        	if (response.status_code == 0) {
        		if (response.status_message == 'select_ahead_time') {
        			alert(response.trans_message);
        		}
        		$('#input_vehicle_type').attr("disabled", true); 
        	}
        	else if (driver_id == 0) {
        		 $('.submit_button').attr('disabled',false);
        	}
        	else {
        		$scope.assigned_driver = response.driver;
				$('.assigned_driver').show();
				$scope.auto_assign_status = false;
				$('#auto_assign_id').val($scope.assigned_driver.id);
				$('.submit_button').attr('disabled',false);
        	}
       	});
    };
    var mapIcons = {
        Online: APP_URL+'/images/car_green.png',
        Scheduled: APP_URL+'/images/car_red.png',
        Begin_trip: APP_URL+'/images/car_blue.png',
        End_trip: APP_URL+'/images/car_yellow.png',
        Offline: APP_URL+'/images/car_black.png',
    }
    var googleMarker = [];
    var map;
	var infoWindow = new google.maps.InfoWindow();

	//Show drivers on map
    $scope.MapData = function(availableOnly) {
	  	clearOverlays();
      	if ($scope.vehicle_types.length != 0) {
       		drivers = $scope.drivers
       		angular.forEach(drivers, function(value, key){
       			var matchesAvailability = value.driver_current_status == $scope.driver_availability || $scope.driver_availability == '';
       			var matchesParkingMode = !availableOnly || value.driver_current_status == 'Online' || value.driver_current_status == '';
       			if (matchesAvailability && matchesParkingMode) {
            		var icon_img = value.driver_current_status;
            		if (icon_img == 'Begin trip') {
            			icon_img = 'Begin_trip'
            		}else if (icon_img == 'End trip') {
            			icon_img = 'End_trip'
            		}
          			var icon = {
                  		url: mapIcons[icon_img], // url
						scaledSize: new google.maps.Size(23, 30), // scaled size
						origin: new google.maps.Point(0,0), // origin
						anchor: new google.maps.Point(0, 0) // anchor
              		};

           			marker = new google.maps.Marker({
						position: {
						  lat: parseFloat(value.latitude),
						  lng: parseFloat(value.longitude)
						},
						id: value.id,
						map: $scope.map,
						title: value.first_name + " " +value.last_name,
						icon : icon,
          			});
          			googleMarker.push(marker);
          			google.maps.event.addListener(marker, 'click', function() {
		                var html =''; 
		                html += '<div class="user_background col-md-3">';    
		                html +='<img src="'+value.src+'" class="img-circle" width="100%" height="auto"></div>';
		                html +='<div class="user_details col-md-9">';
		                html +='<h3 class="text-capitalize">'+value.first_name+'</h3> ';
		                html +='<p title="'+value.email+'"><i class="fa fa-envelope" aria-hidden="true"></i> : <span class="sety">'+value.email+'</span></p>';
		                html +='<p title="'+value.hidden_mobile_number+'"><i class="fa fa-phone" aria-hidden="true"></i> : <span class="sety">'+value.hidden_mobile_number+'</span></p>';
		                html +='</div>';
		                infoWindow.setContent(html)
		                infoWindow.open($scope.map, marker);
          				/*$('#user_details').show();
          				$('#user_details').html(html);*/
          			});
       			}
       		});
    	}
	}
	function clearOverlays(){
	  	for (var i = 0; i < googleMarker.length; i++ ) {
	   		googleMarker[i].setMap(null);
	  	}
	  	googleMarker.length = 0;
	}
	$(document).on("click",".close_user_details",function(){
	  	$('#user_details').hide();
	})

	//form validation
    var v = $("#manual_booking").validate({
      rules: {
        currency_code: { required: true },
        mobile_number: { required: true },
        first_name: { required: true },
        last_name: { required: true },
        email: { required: true,email: true },
        pickup_location: { required: true },
        drop_location: { required: true },
        vehicle_type: { required: true },
        gender: { required: true },
        auto_assign_status: { 
        	required:{ 
              	depends: function(element){
	                if($('#auto_assign_id').val()==0){
	                  	return true;
	                }else{
	                  	return false;
	                }
              	}
            }
        },
        date_time : {
        	required: true,
            min_date_time: true,
         },
      },
      messages: {
        auto_assign_status : {
          required : 'This field is required if no driver assigned.'
        },
      },
      errorElement: "span",
      errorClass: "text-danger",
       errorPlacement: function( label, element ) {
        	if(element.attr( "data-error-placement" ) === "container" ){
          		container = element.attr('data-error-container');
          		$(container).append(label);
        	} else {
          		label.insertAfter( element ); 
        	}
      	},
    });

    $.validator.addMethod("min_date_time", function(value, element, param) {
    	var leadMinutes = getMinimumLeadMinutes();
    	if (page == 'edit') {
    		var old_date_value = new Date(old_edit_date);
			var today = new Date();
	    	var alignFillDate = new Date($('#input_date_time').val());
	    	if ($scope.utc_offset == '') {
	    		var valid_date = new Date(today.getTime() + (Math.max(leadMinutes - 1, 0) * 60 * 1000));
	    		var currenct_date = new Date(today.getTime() + (leadMinutes * 60 * 1000));
	    	}else{
				var valid_date = new Date( today.getTime() + ((today.getTimezoneOffset()+$scope.utc_offset+Math.max(leadMinutes - 1, 0)) * 60000));
				var currenct_date = new Date( today.getTime() + ((today.getTimezoneOffset()+$scope.utc_offset+leadMinutes) * 60000));
	  		}
	  		if(!moment(old_date_value).isBefore(alignFillDate) && !moment(valid_date).isBefore(alignFillDate)){
	  			if(moment(old_date_value).isBefore(valid_date))
	  				$('#input_date_time').val($filter('date')(new Date(old_date_value),'yyyy-MM-dd HH:mm'))
	  			else
	  				$('#input_date_time').val($filter('date')(new Date(currenct_date),'yyyy-MM-dd HH:mm'))
	  		}
	    	var alignFillDate = new Date($('#input_date_time').val());
	  		return moment(old_date_value).isBefore(alignFillDate) || moment(valid_date).isBefore(alignFillDate) || moment(old_date_value).isSame(alignFillDate);
    	}else{
			var today = new Date();
	    	var alignFillDate = new Date($('#input_date_time').val());
	    	if ($scope.utc_offset == '') {
	    		var valid_date = new Date(today.getTime() + (Math.max(leadMinutes - 1, 0) * 60 * 1000));
	    		var currenct_date = new Date(today.getTime() + (leadMinutes * 60 * 1000));
	    	}else{
				var valid_date = new Date( today.getTime() + ((today.getTimezoneOffset()+$scope.utc_offset+Math.max(leadMinutes - 1, 0)) * 60000));
				var currenct_date = new Date( today.getTime() + ((today.getTimezoneOffset()+$scope.utc_offset+leadMinutes) * 60000));
	  		}
	  		if(!moment(valid_date).isBefore(alignFillDate)){
	  			$('#input_date_time').val($filter('date')(new Date(currenct_date),'yyyy-MM-dd HH:mm'))
	  		}
	    	var alignFillDate = new Date($('#input_date_time').val());
	  		return moment(valid_date).isBefore(alignFillDate);
	  	}
  	}, $.validator.format((page == 'edit')?"Please make sure that the booking time is ahead of current time":"Please make sure that the booking time follows your configured dispatch lead time"));

    var input1 = document.getElementById('input_pickup_location');
	var input2 = document.getElementById('input_drop_location');
	if (hasGoogleMaps() && input1 && input2) {
		google.maps.event.addDomListener(input1, 'keydown', function(event) { 
		    if (event.keyCode === 13) { 
		        event.preventDefault(); 
		    }
		});
		google.maps.event.addDomListener(input2, 'keydown', function(event) { 
		    if (event.keyCode === 13) { 
		        event.preventDefault(); 
		    }
		});
	}


	$(function() {
		$select_driver_availability = $('#input_driver_availability').selectize({
    		valueField: 'value',
    		labelField: 'name',
    		setValue:'All',
    		options: [
	    		{
	    			value: '',
	        		name: 'All',
	    		},
	    		{
	    			value: 'Online',
	        		name: 'Available Driver',
	        		imageUrl: APP_URL+'/images/green-icon.png'
	    		},
	    		{
	    			value: 'Scheduled',
	        		name: 'Enroute to Pickup',
	        		imageUrl: APP_URL+'/images/red_icon.png'
	    		},
	    		{
	    			value: 'Begin trip',
	        		name: 'Reached Pickup',
	        		imageUrl: APP_URL+'/images/blue_icon.png'
	    		},
	    		{
	    			value: 'End trip',
	        		name: 'Journey Started',
	        		imageUrl: APP_URL+'/images/yellow_icon.png'
	    		},
	    		{
	    			value: 'Offline',
	        		name: 'Offline',
	        		imageUrl: APP_URL+'/images/black_icon.png'
	    		}
	    	],
		    render: {
		        option: function (item, escape) {
		            return '<div class="option">' +
		                    '<div>' + ((typeof item.imageUrl=='undefined')?'':'<img style="margin-right: 5px" class="avatar" width="30px" height="30px" style="padding=10px;" src="' + item.imageUrl + '" />') +
		                        '<span class="name">' + escape(item.name) + '</span>' +
		                   '</div>' +
		                '</div>';
		        }
		    }
		});
		var selectize = $select_driver_availability[0].selectize;
		selectize.setValue('', false);
		$('#input_driver_availability')[0].selectize.disable(); 
	});

	$(document).on("click",'.driver_detail_view',function(){
		old_id = $('.driver_detail_popup').attr('id')
		new_id = $(this).attr('id')
		$('.driver_detail_popup').attr('id',$(this).attr('id'))
		$('.driver_name_detail').html($(this).attr('first_name'))
		$('.driver_email_detail').html($(this).attr('email'))
		$('.driver_phone_detail').html($(this).attr('phone'))
		$('.driver_company_detail').html($(this).attr('company'))
		
		if (old_id==new_id) {
			$('.driver_detail_popup').toggle("slide", { direction: "right" }, 700)
		}else{
			$('.driver_detail_popup').hide();
			$('.driver_detail_popup').show("slide", { direction: "right" }, 700);
		}
	});
	$(document).click(function(){
		if(!$(event.target).closest( ".driver_detail_view" ).length){
			$('.driver_detail_popup').hide("slide", { direction: "right" }, 700)
		}
	});
	$scope.page_loading = 1
	$(document).ready(function(){
		$scope.page_loading = 0;
		applyCountryDefaults();
		$('#input_country_code').change(function(){
			applyCountryDefaults();
		})
	})

	$scope.checkInvalidTime = function() {
		var leadMinutes = getMinimumLeadMinutes();
		if (leadMinutes === 0) {
			return false;
		}

		var today = new Date();
    	var alignFillDate = new Date($('#input_date_time').val());

    	if ($scope.utc_offset == '') {
    		var valid_date = new Date(today.getTime() + (Math.max(leadMinutes - 1, 0) * 60 * 1000));
    	}
    	else {
			var valid_date = new Date( today.getTime() + ((today.getTimezoneOffset()+$scope.utc_offset+Math.max(leadMinutes - 1, 0)) * 60000));
  		}

  		return !moment(valid_date).isBefore(alignFillDate);
	}

	$scope.submitForm = function($event) {
		if($scope.checkInvalidTime()) {
			alert("Please make sure that the booking time follows your configured dispatch lead time, or enable dispatch override.");
			return true;
		}
		
        $("form[name='myForm']").submit();
        $('.submit_button').attr('disabled',true);
    };

	window.manualBookingConsole = {
		fitRoute: function() {
			$scope.$applyAsync(function() {
				$scope.setMapMode('route');
			});
		},
		recenter: function() {
			$scope.$applyAsync(function() {
				$scope.recenterMap();
			});
		},
		refreshDrivers: function() {
			$scope.$applyAsync(function() {
				$scope.refreshDispatchData();
			});
		},
		clearRoute: function() {
			$scope.$applyAsync(function() {
				$scope.clearRouteConsole();
			});
		},
		focusPickup: function() {
			$scope.$applyAsync(function() {
				$scope.focusMarker('pickup');
			});
		},
		focusDrop: function() {
			$scope.$applyAsync(function() {
				$scope.focusMarker('drop');
			});
		},
		setMode: function(mode) {
			$scope.$applyAsync(function() {
				$scope.setMapMode(mode);
			});
		},
	};
}]);
