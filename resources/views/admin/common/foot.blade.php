<!-- Add the sidebar's background. This div must be placed Immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>
</div>
@php
	$assetVersion = $version ?? config('app.version') ?? '1';
	$googleMapKey = $map_key ?? (defined('MAP_KEY') ? MAP_KEY : '');
	$commonJsPath = public_path('admin_assets/dist/js/common.js');
	$commonJsVersion = file_exists($commonJsPath) ? filemtime($commonJsPath) : $assetVersion;
@endphp

<!-- jQuery 2.1.4 -->
<script src="{{ url('admin_assets/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
<script src="{{ url('admin_assets/plugins/jQueryUI/jquery-ui.min.js') }}"></script>

<!-- Latest compiled and minified JavaScript -->
<script src="{{ url('js/angular.js') }}"></script>
<script src="{{ url('js/angular-sanitize.js') }}"></script>

<script> 
	window.APP_URL = {!! json_encode(url('/')) !!};
	window.COMPANY_ADMIN_URL = {!! json_encode(url('/'.LOGIN_USER_TYPE)) !!};
	window.LOGIN_USER_TYPE = '{!! LOGIN_USER_TYPE !!}';
	window.popup_code  = {!! session('error_code') ? session('error_code') : 0  !!};
	window.STRIPE_PUBLISH_KEY = "{{ payment_gateway('publish','Stripe') }}";

	var APP_URL = window.APP_URL;
	var COMPANY_ADMIN_URL = window.COMPANY_ADMIN_URL;
	var LOGIN_USER_TYPE = window.LOGIN_USER_TYPE;
	var popup_code = window.popup_code;
	var STRIPE_PUBLISH_KEY = window.STRIPE_PUBLISH_KEY;
	var app = window.app || angular.module('App', ['ngSanitize']);
	window.app = app;
</script>

<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
	$.widget.bridge('uibutton', $.ui.button);
</script>

<!-- Bootstrap 3.3.5 -->
<script src="{{ url('admin_assets/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ url('admin_assets/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{ url('admin_assets/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
@if (!isset($exception))   
	@if (Route::current()->uri() == 'admin/dashboard' || Route::current()->uri() == 'company/dashboard')
		<!-- Morris.js charts -->
		<script src="{{ url('admin_assets/plugins/morris/raphael.js') }}"></script>
		<script src="{{ url('admin_assets/plugins/morris/morris.min.js') }}"></script>
		<!-- datepicker -->

		<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
		<script src="{{ url('admin_assets/dist/js/dashboard.js') }}"></script>
	@endif

	@if (Route::current()->uri() == 'admin/add_user' || Route::current()->uri() == 'admin/edit_user/{id}')
		<script src="{{ url('admin_assets/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
	@endif

	@if (Route::current()->uri() == 'admin/add_coupon_code' || Route::current()->uri() == 'admin/edit_coupon_code/{id}')
		<script src="{{ url('admin_assets/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
	@endif

	{{-- DataTables: load on all admin pages so every DataTable view works (no route condition) --}}
	<script src="{{ url('admin_assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
	<script src="{{ url('admin_assets/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>
	<script src="{{ url('admin_assets/plugins/datatables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ url('js/dataTables.buttons.js') }}"></script>
	<script src="{{ url('js/buttons.server-side.js') }}"></script>

	@if (Route::current()->uri() == 'admin/add_room' || Route::current()->uri() == 'admin/edit_room/{id}' || Route::current()->uri() == 'admin/edit_rider/{id}' || Route::current()->uri() == 'admin/add_rider' || Route::current()->uri() == 'admin/edit_page/{id}' || Route::current()->uri() == 'admin/add_page/{id}' || Route::current()->uri() == 'admin/later_booking' || Route::current()->uri() == 'company/later_booking' || Route::current()->uri() == 'admin/add_company' || Route::current()->uri() == 'admin/edit_company/{id}' || Route::current()->uri() == 'admin/company' || Route::current()->uri() == 'company/edit_company/{id}')
		@if($googleMapKey !== '')
			<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&sensor=false&libraries=places"></script>
		@endif
		<script src="{{ url('admin_assets/plugins/jQuery/jquery.validate.js') }}"></script>
	@endif

	@if (Route::current()->uri() == 'admin/add_vehicle' || Route::current()->uri() == 'admin/edit_vehicle/{id}' || Route::current()->uri() == 'company/add_vehicle' || Route::current()->uri() == 'company/edit_vehicle/{id}' || Route::current()->uri() == 'admin/add-vehicle-make' || Route::current()->uri() == 'admin/edit-vehicle-make/{id}')
		<script src="{{ url('admin_assets/plugins/jQuery/jquery.validate.js') }}"></script>
	@endif

	@if (Route::current()->uri() == 'admin/payments')
		<script src="{{ url('admin_assets/dist/js/reports.js') }}"></script>
	@endif

	@if (Route::current()->uri() == 'admin/add_page' || Route::current()->uri() == 'admin/edit_page/{id}' || Route::current()->uri() == 'admin/send_email' || Route::current()->uri() == 'admin/add_help' || Route::current()->uri() == 'admin/edit_help/{id}')
		<script src="{{ url('admin_assets/plugins/editor/editor.js') }}"></script>
		<script type="text/javascript"> 
			$("[name='submit']").click(function(){
				$('#content').text($('#txtEditor').Editor("getText"));
				$('#message').text($('#txtEditor').Editor("getText"));
				$('#answer').text($('#txtEditor').Editor("getText"));
			});
		</script>
	@endif

	@if (request()->is('admin/map*') || request()->is('company/map*'))
		<script src="{{ url('admin_assets/dist/js/fleet_map.js?v='.$assetVersion) }}"></script>
		@if($googleMapKey !== '')
			<script async defer type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&sensor=false&libraries=geometry,places&callback=initFleetMap"></script>
		@endif
	@endif

	@if (Route::current()->uri() == 'admin/detail_request/{id}' || Route::current()->uri() == 'company/detail_request/{id}')
		@if($googleMapKey !== '')
			<script async defer type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&sensor=false&callback=initMap&libraries=geometry,places"></script>
		@endif
	@endif

	@if (request()->is('admin/heat-map*') || request()->is('company/heat-map*'))
		<script src="{{ url('admin_assets/dist/js/heat_map.js?v='.$assetVersion) }}"></script>
		@if($googleMapKey !== '')
			<script async defer type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&libraries=visualization,places,geometry&callback=initHeatMap"></script>
		@endif
	@endif

	@if (request()->is('admin/manual_booking*') || request()->is('company/manual_booking*'))
		@if($googleMapKey !== '')
			<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&sensor=false&libraries=places,geometry,visualization"></script>
		@endif
		<script src="{{ url('admin_assets/dist/js/manual_booking.js') }}"></script>
		<script src="{{ url('admin_assets/dist/js/moment.min.js') }}"></script>
		<script src="{{ url('admin_assets/dist/js/bootstrap-datetimepicker.min.js') }}"></script>
		<script src="{{ url('js/selectize.js') }}"></script>
		<script src="{{ url('admin_assets/plugins/jQuery/jquery.validate.js') }}"></script>
		<script src="{{ url('admin_assets/dist/js/jquery.multiselect.js') }}"></script>
	@endif

	@if (Route::current()->uri() == 'admin/detail_request/{id}' || Route::current()->uri() == 'company/detail_request/{id}')
		<script src="{{ url('admin_assets/dist/js/request.js?v='.$assetVersion) }}"></script>
	@endif

	@if (
		request()->is('admin/locations') ||
		request()->is('admin/add_location') ||
		request()->is('admin/edit_location/*') ||
		request()->is('admin/saas-dispatch/manage-zones*') ||
		request()->is('admin/saas-dispatch/service-areas*') ||
		request()->is('company/saas-dispatch/manage-zones*') ||
		request()->is('company/saas-dispatch/service-areas*')
	)
		@if($googleMapKey !== '')
			<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&libraries=places,geometry&callback=initLocationMap"></script>
		@endif
	@endif

	@if (request()->is('admin/trips*') || request()->is('company/trips*'))
		@if($googleMapKey !== '')
			<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}&libraries=places,geometry"></script>
		@endif
	@endif

	@if (Route::current()->uri() == 'admin/add_manage_fare' || Route::current()->uri() == 'admin/edit_manage_fare/{id}' || Route::current()->uri() == 'admin/add_company' || Route::current()->uri() == 'admin/edit_company/{id}' || Route::current()->uri() == 'company/edit_company/{id}')
		<script src="{{ url('admin_assets/dist/js/moment.min.js') }}"></script>
	@endif

	@if(Route::current()->uri() == 'admin/referral_settings')
		<script src="{{ url('admin_assets/bootstrap/js/bootstrap-toggle.min.js') }}"></script>
	@endif

@endif

<!-- AdminLTE App -->
<script src="{{ url('admin_assets/dist/js/demo.js') }}"></script>
<script src="{{ url('admin_assets/dist/js/app.js') }}"></script>
<script src="{{ url('admin_assets/dist/js/common.js?v='.$commonJsVersion) }}"></script>
@if (Route::current()->uri() == 'company/payout_preferences')
	{!! Html::script('js/common.js?v='.$assetVersion) !!}
@endif

@stack('scripts')


<script type="text/javascript">

	function preventBack()
	{
		previous_url = document.referrer.substr(document.referrer.lastIndexOf('/') + 1)
		if (previous_url == "signin" || previous_url == "" || previous_url == "signin_company") {
			window.history.forward();
		}
	}
	setTimeout("preventBack()", 0);

</script>


@if(env('Live_Chat') == "true")
<script>
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/63229ac737898912e9693b4d/1gcvip75c';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
  })();
</script>
@endif
