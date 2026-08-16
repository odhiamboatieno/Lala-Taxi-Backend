<aside class="main-sidebar">
	<section class="sidebar">
		<a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}" class="logo">
	      <span class="logo-mini"><b>{{ $site_name }}</b></span>
	      <span class="logo-lg"><b>{{ $site_name }}</b></span>
	    </a>
		<!-- <div class="user-panel">
			<div class="pull-left image">
				@php
					if(LOGIN_USER_TYPE=='company'){
						$user = Auth::guard('company')->user();
						$company_user = true;
						$first_segment = 'company';
					}
					else{
						$user = Auth::guard('admin')->user();
						$company_user = false;
						$first_segment = 'admin';
					}
					$currentUri = Route::current()->uri();
					$manualBookingEnabledForCompany = !$company_user || manual_booking_module_enabled();
					$manualBookingMenuVisible = ($company_user && @$user->status == 'Active' && $manualBookingEnabledForCompany) || (!$company_user && @$user->can('manage_manual_booking'));
					$laterBookingMenuVisible = ($company_user && $manualBookingEnabledForCompany) || (!$company_user && @$user->can('manage_manual_booking'));
					$companySaasEnabled = $company_user && function_exists('company_saas_enabled') && company_saas_enabled();
				@endphp
				@if(!$company_user || $user->profile ==null)
					<img src="{{ url('admin_assets/dist/img/avatar04.png') }}"  class="img-circle" alt="{{ __('messages.blade_static.user_image_f1658ae3') }}">
				@else
					<img src="{{ $user->profile }}"  class="img-circle" alt="{{ __('messages.blade_static.user_image_f1658ae3') }}">
				@endif
			</div>
			<div class="pull-left info">
				<p>{{ (!$company_user)?$user->username:$user->name }}</p>
				<a href="#"><i class="fa fa-circle text-success"></i> {{ __('messages.blade_static.online_c3e839df') }}</a>
			</div>
		</div> -->
		<ul class="sidebar-menu">
			<!-- <li class="header">{{ __('messages.blade_static.main_navigation_9b32c3c4') }}</li> -->
			<li class="{{ (Route::current()->uri() == $first_segment.'/dashboard') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/dashboard') }}"><i class="fa fa-dashboard"></i><span>{{ __('messages.blade_static.dashboard_d87f47b4') }}</span></a></li>

			@if(@$user->can('manage_admin'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/admin_user' || Route::current()->uri() == 'admin/roles') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-users"></i> <span>{{ __('messages.blade_static.admins_management_7c52def2') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="{{ (Route::current()->uri() == 'admin/admin_user') ? 'active' : ''  }}"><a href="{{ url('admin/admin_user') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.admin_users_7ff2a7ec') }}</span></a></li>
					<li class="{{ (Route::current()->uri() == 'admin/roles') ? 'active' : ''  }}"><a href="{{ url('admin/roles') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.roles_permissions_78f58c62') }}</span></a></li>
				</ul>
			</li>
			@endif
			@if($company_user && $user->id != 1)
			<li class="{{ (Route::current()->uri() == $first_segment.'/payout_preferences') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/payout_preferences') }}"><i class="fa fa-paypal"></i><span>{{ __('messages.blade_static.payout_preferences_2ad80281') }}</span></a></li>
			@endif

			@if(@$user->can('view_company'))
			<li class="{{ (Route::current()->uri() == 'admin/company') ? 'active' : ''  }}"><a href="{{ url('admin/company') }}"><i class="fa fa-building"></i><span>{{ __('messages.blade_static.manage_company_4c6d0fdb') }}</span></a></li>
			<li class="{{ request()->is('admin/company-onboarding*') ? 'active' : ''  }}"><a href="{{ url('admin/company-onboarding') }}"><i class="fa fa-shield"></i><span>Company Onboarding</span></a></li>
			@endif
			@if($company_user || @$user->can('view_driver'))
			<li class="{{ (Route::current()->uri() == $first_segment.'/driver') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/driver') }}"><i class="fa fa-cog"></i><span>{{ __('messages.blade_static.manage_drivers_0fc47b60') }}</span></a></li>
			@endif
			@if(($companySaasEnabled && operation_team_can('manage_driver_levels')) || (!$company_user && (@$user->can('manage_driver_levels') || @$user->can('manage_admin'))))
			<li class="{{ (Route::current()->uri() == $first_segment.'/driver-levels') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/driver-levels') }}"><i class="fa fa-trophy"></i><span>Driver Levels</span></a></li>
			@endif
			@if(($company_user && (!current_operation_team_member() || operation_team_can('view_rider'))) || @$user->can('view_rider'))
			<li class="{{ (Route::current()->uri() == $first_segment.'/rider') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/rider') }}"><i class="fa fa-users"></i><span>{{ __('messages.blade_static.manage_riders_95a1427f') }}</span></a></li>
			@endif
			@if(($companySaasEnabled && operation_team_can('manage_rider_levels')) || (!$company_user && (@$user->can('manage_rider_levels') || @$user->can('manage_admin'))))
			<li class="{{ (Route::current()->uri() == $first_segment.'/customer-levels') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/customer-levels') }}"><i class="fa fa-star"></i><span>Customer Levels</span></a></li>
			@endif
			@if($company_user && operation_team_can('manage_operation_team_roles'))
			<li class="{{ request()->is('company/operation-team/roles*') ? 'active' : ''  }}"><a href="{{ url('company/operation-team/roles') }}"><i class="fa fa-shield"></i><span>Role Management</span></a></li>
			@endif
			@if($company_user && operation_team_can('manage_operation_team_members'))
			<li class="{{ request()->is('company/operation-team/members*') ? 'active' : ''  }}"><a href="{{ url('company/operation-team/members') }}"><i class="fa fa-users"></i><span>Operation Team</span></a></li>
			@endif

			@if(@$user->can('view_documents'))
			<li class="{{ (Route::current()->uri() == 'admin/documents') ? 'active' : ''  }}"><a href="{{ url('admin/documents') }}"><i class="fa fa-folder"></i><span>{{ __('messages.blade_static.manage_documents_6a3b8033') }}</span></a></li>
			@endif
			
			@if($company_user || @$user->can('manage_send_message'))
			<li class="{{ (Route::current()->uri() == $first_segment.'/notifications') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/notifications') }}"><i class="fa fa-bell"></i><span>Notification Management</span></a></li>
			<li class="{{ (Route::current()->uri() == $first_segment.'/send_message') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/send_message') }}"><i class="fa fa-bullhorn"></i><span>{{ __('messages.blade_static.send_messages_7ad94832') }}</span></a></li>
			@endif
			@if(@$user->can('manage_email_settings') || @$user->can('manage_send_email'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/email_settings' || Route::current()->uri() == 'admin/send_email') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-envelope-o"></i>
					<span>{{ __('messages.blade_static.manage_emails_afc1f53d') }}</span><i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if(@$user->can('manage_send_email'))
					<li class="{{ (Route::current()->uri() == 'admin/send_email') ? 'active' : ''  }}">
						<a href="{{ url('admin/send_email') }}"><i class="fa fa-circle-o"></i>
							<span>{{ __('messages.blade_static.send_email_be4e8e2d') }}</span>
						</a>
					</li>
					@endif
					@if(@$user->can('manage_email_settings'))
					<li class="{{ (Route::current()->uri() == 'admin/email_settings') ? 'active' : ''  }}">
						<a href="{{ url('admin/email_settings') }}"><i class="fa fa-circle-o"></i>
							<span>{{ __('messages.blade_static.email_settings_9bdedc71') }}</span>
						</a>
					</li>
					@endif
				</ul>
			</li>
			@endif

			@if($manualBookingMenuVisible || $laterBookingMenuVisible)
			<li class="treeview {{ ($currentUri == $first_segment.'/manual_booking/{id?}' || $currentUri == $first_segment.'/later_booking') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-taxi"></i>
					<span> {{ __('messages.blade_static.manage_manual_booking_60f91f77') }} </span><i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if($manualBookingMenuVisible)
					<li class="{{ ($currentUri == $first_segment.'/manual_booking/{id?}') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/manual_booking') }}"><i class="fa fa-address-book" aria-hidden="true"></i><span>{{ __('messages.blade_static.manual_booking_0111a634') }}</span></a></li>
					@endif
					@if($laterBookingMenuVisible)
					<li class="{{ ($currentUri == $first_segment.'/later_booking') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/later_booking') }}"><i class="fa fa-list-alt"></i><span>{{ __('messages.blade_static.view_manual_schedule_booking_2b79c456') }}</span></a></li>
					@endif
				</ul>
			</li>
			@endif

			@if(!$company_user && (@$user->can('manage_parcel_categories') || @$user->can('manage_parcel_package_sizes') || @$user->can('manage_parcel_tasks') || @$user->can('manage_parcel_pricing') || @$user->can('manage_parcel_settings') || @$user->can('manage_parcel_trips')))
			<li class="treeview {{ (in_array($currentUri, ['admin/parcel_categories', 'admin/add_parcel_category', 'admin/edit_parcel_category/{id}', 'admin/parcel_package_sizes', 'admin/add_parcel_package_size', 'admin/edit_parcel_package_size/{id}', 'admin/parcel_tasks', 'admin/add_parcel_task', 'admin/edit_parcel_task/{id}', 'admin/parcel_pricing', 'admin/add_parcel_pricing', 'admin/edit_parcel_pricing/{id}', 'admin/parcel_settings', 'admin/parcel_trips', 'admin/view_parcel_trip/{id}', 'admin/cancel_parcel_trip/{id}'])) ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-cube"></i>
					<span>Parcel</span><i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if(@$user->can('manage_parcel_categories'))
					<li class="{{ in_array($currentUri, ['admin/parcel_categories', 'admin/add_parcel_category', 'admin/edit_parcel_category/{id}']) ? 'active' : ''  }}"><a href="{{ url('admin/parcel_categories') }}"><i class="fa fa-circle-o"></i><span>Categories</span></a></li>
					@endif
					@if(@$user->can('manage_parcel_package_sizes'))
					<li class="{{ in_array($currentUri, ['admin/parcel_package_sizes', 'admin/add_parcel_package_size', 'admin/edit_parcel_package_size/{id}']) ? 'active' : ''  }}"><a href="{{ url('admin/parcel_package_sizes') }}"><i class="fa fa-circle-o"></i><span>Package Sizes</span></a></li>
					@endif
					@if(@$user->can('manage_parcel_tasks'))
					<li class="{{ in_array($currentUri, ['admin/parcel_tasks', 'admin/add_parcel_task', 'admin/edit_parcel_task/{id}']) ? 'active' : ''  }}"><a href="{{ url('admin/parcel_tasks') }}"><i class="fa fa-circle-o"></i><span>Tasks</span></a></li>
					@endif
					@if(@$user->can('manage_parcel_pricing'))
					<li class="{{ in_array($currentUri, ['admin/parcel_pricing', 'admin/add_parcel_pricing', 'admin/edit_parcel_pricing/{id}']) ? 'active' : ''  }}"><a href="{{ url('admin/parcel_pricing') }}"><i class="fa fa-circle-o"></i><span>Pricing</span></a></li>
					@endif
					@if(@$user->can('manage_parcel_settings'))
					<li class="{{ $currentUri == 'admin/parcel_settings' ? 'active' : ''  }}"><a href="{{ url('admin/parcel_settings') }}"><i class="fa fa-circle-o"></i><span>Settings</span></a></li>
					@endif
					@if(@$user->can('manage_parcel_trips'))
					<li class="{{ in_array($currentUri, ['admin/parcel_trips', 'admin/view_parcel_trip/{id}', 'admin/cancel_parcel_trip/{id}']) ? 'active' : ''  }}"><a href="{{ url('admin/parcel_trips') }}"><i class="fa fa-circle-o"></i><span>Trips</span></a></li>
					@endif
				</ul>
			</li>
			@endif
		

			@if(($company_user || @$user->can('manage_vehicle')) || $user->can('manage_vehicle_type'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/vehicle_type' || Route::current()->uri() == $first_segment.'/vehicle') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-taxi"></i>
					<span> {{ __('messages.blade_static.vehicles_management_dedeb713') }}  </span><i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if($company_user || @$user->can('manage_vehicle'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/vehicle') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/vehicle') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.all_vehicles_dd9eb324') }}</span></a></li>
					@endif
					@if(@$user->can('manage_vehicle_type'))
					<li class="{{ (Route::current()->uri() == 'admin/vehicle_type') ? 'active' : ''  }}"><a href="{{ url('admin/vehicle_type') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.manage_vehicle_types_2809038d') }}</span></a></li>
					@endif
					@if($companySaasEnabled && operation_team_can('manage_company_vehicle_types'))
					<li class="{{ str_contains(Route::current()->uri(), 'vehicle-types') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/vehicle-types') }}"><i class="fa fa-circle-o"></i><span>Vehicle Types</span></a></li>
					@endif
					@if($companySaasEnabled && operation_team_can('manage_company_documents') && saas_feature_module_enabled('company_documents'))
					<li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/documents') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/documents') }}"><i class="fa fa-circle-o"></i><span>Documents Management</span></a></li>
					@endif
					@if($companySaasEnabled && operation_team_can('manage_company_payment_gateways'))
					<li class="{{ str_contains(Route::current()->uri(), 'payment-gateways') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/payment-gateways') }}"><i class="fa fa-circle-o"></i><span>Payment Gateways</span></a></li>
					@endif
						@if(@$user->can('view_vehicle_make'))
				<li class="{{ (Route::current()->uri() == 'admin/vehicle_make' || Route::current()->uri() == 'admin/add-vehicle-make' || Route::current()->uri() == 'admin/edit-vehicle-make/{id}') ? 'active' : ''  }}"><a href="{{ url('admin/vehicle_make') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.manage_vehicle_make_91fac8f6') }}</span></a></li>
			@endif

			@if(@$user->can('view_vehicle_model'))
				<li class="{{ (Route::current()->uri() == 'admin/vehicle_model' || Route::current()->uri() == 'admin/add-vehicle_model' || Route::current()->uri() == 'admin/edit-vehicle_model/{id}') ? 'active' : ''  }}"><a href="{{ url('admin/vehicle_model') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.manage_vehicle_model_3bc42dfc') }}</span></a></li>
			@endif
				</ul>
			</li>
			@endif

			@if(@$user->can('view_additional_reason'))
			<li class="{{ (Route::current()->uri() == 'admin/additional-reasons') ? 'active' : ''  }}"><a href="{{ url('admin/additional-reasons') }}"><i class="fa fa fa-comment"></i><span> {{ __('messages.blade_static.additional_reasons_c8cdd721') }}</span></a></li>
			@endif

			@if(@$user->can('view_manage_reason'))
			<li class="{{ (Route::current()->uri() == 'admin/cancel-reason') ? 'active' : ''  }}"><a href="{{ url('admin/cancel-reason') }}"><i class="fa fa fa-ban"></i><span>{{ __('messages.blade_static.cancellation_reasons_4d9d13ba') }}</span></a></li>
			@endif

			@if(@$user->can('manage_locations'))
			<li class="{{ (Route::current()->uri() == 'admin/locations') ? 'active' : ''  }}"><a href="{{ url('admin/locations') }}"><i class="fa fa-map-o"></i><span>{{ __('messages.blade_static.manage_locations_cde3706e') }}</span></a></li>
			@endif
			@if($companySaasEnabled && operation_team_can('manage_company_service_areas'))
			<li class="{{ (str_contains(Route::current()->uri(), 'service-areas') || str_contains(Route::current()->uri(), 'manage-zones')) ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/manage-zones') }}"><i class="fa fa-map-o"></i><span>Manage Zones</span></a></li>
			@endif

			@if(@$user->can('manage_peak_based_fare'))
			<li class="{{ (Route::current()->uri() == 'admin/manage_fare') ? 'active' : ''  }}"><a href="{{ url('admin/manage_fare') }}"><i class="fa fa fa-dollar"></i><span>{{ __('messages.blade_static.fare_management_9dab7521') }}</span></a></li>
			@endif
			@if($companySaasEnabled && operation_team_can('manage_company_networking') && saas_feature_module_enabled('networking'))
			<li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/networking') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/networking') }}"><i class="fa fa-random"></i><span>Networking</span></a></li>
			@endif
			@if($companySaasEnabled && operation_team_can('manage_company_accounts_invoicing') && saas_feature_module_enabled('accounts_invoicing'))
			<li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/accounts') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/accounts') }}"><i class="fa fa-file-text-o"></i><span>Accounts / Invoicing</span></a></li>
			@endif
			@if($companySaasEnabled && operation_team_can('manage_company_data_suite') && saas_feature_module_enabled('data_suite'))
			<li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/data-suite') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/data-suite') }}"><i class="fa fa-bar-chart"></i><span>Data Suite</span></a></li>
			@endif
			@if($companySaasEnabled && operation_team_can('manage_company_messages') && saas_feature_module_enabled('messages'))
			<li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/messages') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/messages') }}"><i class="fa fa-comments"></i><span>Messages</span></a></li>
			@endif
			@if($companySaasEnabled && operation_team_can('manage_company_support_tickets') && saas_feature_module_enabled('support_system_status'))
			<li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/support-system-status') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/saas-dispatch/support-system-status') }}"><i class="fa fa-life-ring"></i><span>Support & System Status</span></a></li>
			@endif

			@if($company_user || @$user->can('manage_requests') || @$user->can('manage_trips') || @$user->can('manage_cancel_trips') || @$user->can('manage_payments') || @$user->can('manage_rating'))
			<li class="treeview {{ (Route::current()->uri() == $first_segment.'/request' || Route::current()->uri() == $first_segment.'/trips' || Route::current()->uri() == $first_segment.'/cancel_trips' || Route::current()->uri() == $first_segment.'/payments' || Route::current()->uri() == $first_segment.'/rating') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-taxi"></i>
					<span> {{ __('messages.blade_static.trips_management_12c1aca6') }} </span><i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if($company_user || @$user->can('manage_requests'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/request') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/request') }}"><i class="fa fa-paper-plane-o"></i><span>Booking Requests</span></a></li>
					@endif

					@if($company_user || @$user->can('manage_trips'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/trips') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/trips') }}"><i class="fa fa-taxi"></i><span>Trip Operations Center</span></a></li>
					@endif

					@if($company_user || @$user->can('manage_cancel_trips'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/cancel_trips') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/cancel_trips') }}"><i class="fa fa-chain-broken"></i><span>{{ __('messages.blade_static.manage_canceled_trips_729c76e9') }}</span></a></li>
					@endif
					
					@if($company_user || @$user->can('manage_payments'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/payments') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/payments') }}"><i class="fa fa-usd"></i><span>Payments & Collections</span></a></li>
					@endif
					
					@if($company_user || @$user->can('manage_rating'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/rating') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/rating') }}"><i class="fa fa-star"></i><span>Ratings & Feedback</span></a></li>
					@endif

					@if($company_user || @$user->can('manage_statements'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/statements/overall') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/statements/overall') }}"><i class="fa fa-bar-chart"></i><span>Trip Reports</span></a></li>
					@endif
				</ul>
			</li>
			@endif

			@if($company_user || @$user->can('manage_driver_payments') || @$user->can('manage_company_payments'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/payout/overall' || Route::current()->uri() == 'admin/payout/company/overall' || Route::current()->uri() == 'company/payout/overall') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-dollar" aria-hidden="true"></i> <span>{{ __('messages.blade_static.manage_payouts_b258682f') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if(@$user->can('manage_company_payment'))
					<li class="{{ (Route::current()->uri() == 'admin/payout/company/overall') ? 'active' : ''  }}"><a href="{{ url('admin/payout/company/overall') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.company_payouts_7dd7593a') }}</span></a></li>
					@endif
					@if($company_user || @$user->can('manage_driver_payments'))
					<li class="{{ (Route::current()->uri() == $first_segment.'/payout/overall') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/payout/overall') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.driver_payouts_2a8bc996') }}</span></a></li>
					@endif
				</ul>
			</li>
			@endif
			@if($company_user || @$user->can('manage_owe_amount'))
			<li class="{{ (Route::current()->uri() == $first_segment.'/owe') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/owe') }}"><i class="fa fa-money"></i><span>{{ __('messages.blade_static.manage_owe_amount_7e601b1b') }}</span></a></li>
			@endif

			@if($company_user ||  @$user->can('manage_statements'))
			<li class="treeview {{ (Route::current()->uri() == $first_segment.'/statements/{type}') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-area-chart"></i> <span>{{ __('messages.blade_static.manage_statements_ed906665') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="{{ (request()->type == 'overall') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/statements/overall') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.overall_statments_875b6968') }}</span></a></li>
					<li class="{{ (request()->type == 'driver') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/statements/driver') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.drivers_statments_28add811') }}</span></a></li>
				</ul>
			</li>
			@endif
			@if(@$user->can('manage_wallet') || @$user->can('manage_promo_code'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/wallet/{user_type}' || Route::current()->uri() == 'admin/promo_code') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-google-wallet"></i> <span>{{ __('messages.blade_static.manage_wallet_promo_ac2c5563') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if($company_user || @$user->can('manage_wallet'))
					<li class="treeview {{ (@$navigation == 'manage_wallet') ? 'active' : ''  }}">
						<a href="{{ route('wallet',['user_type' => 'Rider']) }}"><i class="fa fa-circle-o"></i>
							<span> {{ __('messages.blade_static.manage_wallet_amount_be911fe5') }} </span>
						</a>
					</li>
					@endif
					@if(@$user->can('manage_promo_code'))
					<li class="{{ (Route::current()->uri() == 'admin/promo_code') ? 'active' : ''  }}"><a href="{{ url('admin/promo_code') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.manage_promo_code_8b3835f9') }}</span></a></li>
					@endif
				</ul>
			</li>
			@endif

			@if(@$user->can('manage_rider_referrals') || @$user->can('manage_driver_referrals'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/referrals/rider' || Route::current()->uri() == 'admin/referrals/driver') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-users"></i>
					<span>{{ __('messages.blade_static.referrals_2b0e3a3c') }}</span><i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if(@$user->can('manage_rider_referrals'))
					<li class="{{ (Route::current()->uri() == 'admin/referrals/rider') ? 'active' : ''  }}">
						<a href="{{ url('admin/referrals/rider') }}"><i class="fa fa-circle-o"></i>
							<span> {{ __('messages.blade_static.riders_ffce963c') }} </span>
						</a>
					</li>
					@endif
					@if(@$user->can('manage_driver_referrals'))
					<li class="{{ (Route::current()->uri() == 'admin/referrals/driver') ? 'active' : ''  }}">
						<a href="{{ url('admin/referrals/driver') }}"><i class="fa fa-circle-o"></i>
							<span> {{ __('messages.blade_static.drivers_a40ad45b') }} </span>
						</a>
					</li>
					@endif
				</ul>
			</li>
			@endif
			@if($company_user || $user->can('manage_map') || $user->can('manage_heat_map') || $user->can('manage_admin'))
			<li class="treeview {{ (request()->is($first_segment.'/map*') || Route::current()->uri() == $first_segment.'/heat-map') ? 'active' : ''  }}">
				
				<a href="#">
					<i class="fa fa-map-marker" aria-hidden="true"></i> <span>{{ __('messages.blade_static.manage_map_471f8960') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					@if($user->can('manage_map') || $company_user )
						<li class="{{ request()->is($first_segment.'/map*') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/map') }}"><i class="fa fa-circle-o"></i><span>Fleet View</span></a></li>
					@endif

					@if($user->can('manage_heat_map') || $user->can('manage_admin') || $company_user )
					<li class="{{ request()->is($first_segment.'/heat-map*') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/heat-map') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.heatmap_0d57f9fb') }}</span></a></li>
					@endif
				</ul>
			</li>
			@endif
			@if(($companySaasEnabled && operation_team_can('manage_company_surge_pricing')) || (!$company_user && (@$user->can('manage_surge_pricing') || @$user->can('manage_admin'))))
			<li class="{{ request()->is($first_segment.'/surge-pricing*') ? 'active' : ''  }}"><a href="{{ url($first_segment.'/surge-pricing') }}"><i class="fa fa-bolt"></i><span>Surge Pricing</span></a></li>
			@endif
			<li class="treeview">
				
				<a href="#">
					<i class="fa fa-cog" aria-hidden="true"></i> <span>{{ __('messages.blade_static.manage_settings_25aa4f4f') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
			@if(@$user->can('manage_mobile_app_version'))
			<li class="{{ (Route::current()->uri() == 'admin/mobile_app_version') ? 'active' : ''  }}"><a href="{{ url('admin/mobile_app_version') }}"><i class="fa fa-level-up"></i><span>{{ __('messages.blade_static.manage_mobile_app_version_eeca6b73') }}</span></a></li>
			@endif

			@if(@$user->can('manage_api_credentials'))
			<li class="{{ (Route::current()->uri() == 'admin/api_credentials') ? 'active' : ''  }}"><a href="{{ url('admin/api_credentials') }}"><i class="fa fa-gear"></i><span>{{ __('messages.blade_static.api_credentials_b26b1074') }}</span></a></li>
			@endif
			@if(@$user->can('manage_payment_gateway'))
			<li class="{{ (Route::current()->uri() == 'admin/payment_gateway') ? 'active' : ''  }}"><a href="{{ url('admin/payment_gateway') }}"><i class="fa fa-paypal"></i><span>{{ __('messages.blade_static.payment_gateway_ec48c5e8') }}</span></a></li>
			@endif
			@if(@$user->can('manage_fees'))
			<li class="{{ (Route::current()->uri() == 'admin/fees') ? 'active' : ''  }}"><a href="{{ url('admin/fees') }}"><i class="fa fa-dollar"></i><span>{{ __('messages.blade_static.fees_management_c080dbf0') }}</span></a></li>
			@endif
			@if(@$user->can('manage_referral_settings'))
			<li class="{{ (Route::current()->uri() == 'admin/referral_settings') ? 'active' : ''  }}"><a href="{{ url('admin/referral_settings') }}"><i class="fa fa-users"></i><span>{{ __('messages.blade_static.manage_referral_settings_6d0a83e3') }}</span></a></li>
			@endif
			@if(@$user->can('manage_metas'))
			<li class="{{ (Route::current()->uri() == 'admin/metas') ? 'active' : ''  }}"><a href="{{ url('admin/metas') }}"><i class="fa fa-bar-chart"></i><span>{{ __('messages.blade_static.metasmanagement_b041193c') }}</span></a></li>
			@endif
			@if(@$user->can('manage_country'))
			<li class="{{ (Route::current()->uri() == 'admin/country') ? 'active' : ''  }}"><a href="{{ url('admin/country') }}"><i class="fa fa-globe"></i><span>{{ __('messages.blade_static.manage_country_71c5cd4c') }}</span></a></li>
			@endif
			@if(@$user->can('manage_currency'))
			<li class="{{ (Route::current()->uri() == 'admin/currency') ? 'active' : ''  }}"><a href="{{ url('admin/currency') }}"><i class="fa fa-eur"></i><span>{{ __('messages.blade_static.manage_currency_73a6acb4') }}</span></a></li>
			@endif
			@if(@$user->can('manage_language'))
			<li class="{{ (Route::current()->uri() == 'admin/language') ? 'active' : ''  }}"><a href="{{ url('admin/language') }}"><i class="fa fa-language"></i><span>{{ __('messages.blade_static.manage_language_e914a966') }}</span></a></li>
			@endif
			@if(@$user->can('manage_static_pages'))
			<li class="{{ (Route::current()->uri() == 'admin/pages') ? 'active' : ''  }}"><a href="{{ url('admin/pages') }}"><i class="fa fa-newspaper-o"></i><span>{{ __('messages.blade_static.manage_static_pages_a368ff25') }}</span></a></li>
			@endif
			@if(@$user->can('manage_join_us'))
			<li class="{{ (Route::current()->uri() == 'admin/join_us') ? 'active' : ''  }}"><a href="{{ url('admin/join_us') }}"><i class="fa fa-share-alt"></i><span>{{ __('messages.blade_static.social_app_links_e7eb09ee') }}</span></a></li>
			@endif
			@if(@$user->can('manage_support'))
			<li class="{{ (Route::current()->uri() == 'admin/support') ? 'active' : ''  }}"><a href="{{ url('admin/support') }}"><i class="fa fa-globe"></i><span>{{ __('messages.blade_static.manage_support_a6168f80') }}</span></a></li>
			@endif

			@if(@$user->can('manage_banner'))
			<li class="{{ (Route::current()->uri() == 'admin/banner') ? 'active' : ''  }}"><a href="{{ url('admin/banner') }}"><i class="fa fa-cog"></i><span>{{ __('messages.blade_static.manage_banners_ec8d2e20') }}</span></a></li>
			@endif
			@if(@$user->can('manage_site_settings'))
			<li class="{{ (Route::current()->uri() == 'admin/site_setting') ? 'active' : ''  }}"><a href="{{ url('admin/site_setting') }}"><i class="fa fa-cogs"></i><span>{{ __('messages.blade_static.system_configuration_2e7092b2') }}</span></a></li>
			@endif
				</ul>
			</li>
			@if(@$user->can('manage_help'))
			<li class="treeview {{ (Route::current()->uri() == 'admin/help' || Route::current()->uri() == 'admin/help_category' || Route::current()->uri() == 'admin/help_subcategory') ? 'active' : ''  }}">
				<a href="#">
					<i class="fa fa-support"></i> <span>{{ __('messages.blade_static.manage_help_a15da917') }}</span> <i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="{{ (Route::current()->uri() == 'admin/help') ? 'active' : ''  }}"><a href="{{ url('admin/help') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.help_c47ae153') }}</span></a></li>
					<li class="{{ (Route::current()->uri() == 'admin/help_category') ? 'active' : ''  }}"><a href="{{ url('admin/help_category') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.category_a3c686e7') }}</span></a></li>
					<li class="{{ (Route::current()->uri() == 'admin/help_subcategory') ? 'active' : ''  }}"><a href="{{ url('admin/help_subcategory') }}"><i class="fa fa-circle-o"></i><span>{{ __('messages.blade_static.subcategory_ae1899ec') }}</span></a></li>
				</ul>
			</li>
			@endif
			
		</ul>
	</section>
</aside>
