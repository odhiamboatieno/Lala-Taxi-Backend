<aside class="pe-app-sidebar horizontal-sidebar" id="horizontal-aside">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        @php
            if (LOGIN_USER_TYPE == 'company') {
                $user = Auth::guard('company')->user();
                $company_user = true;
                $first_segment = 'company';
            } else {
                $user = Auth::guard('admin')->user();
                $company_user = false;
                $first_segment = 'admin';
            }

            $horizontalLogoUrl = $logo_url ?? (defined('LOGO_URL') ? url(LOGO_URL) : url('images/logos/logo.png'));
            $horizontalFavicon = $favicon ?? $horizontalLogoUrl;
            $currentUri = Route::current()->uri();
            $manualBookingEnabledForCompany = !$company_user || manual_booking_module_enabled();
            $manualBookingMenuVisible = ($company_user && @$user->status == 'Active' && $manualBookingEnabledForCompany) || (!$company_user && @$user->can('manage_manual_booking'));
            $laterBookingMenuVisible = ($company_user && $manualBookingEnabledForCompany) || (!$company_user && @$user->can('manage_manual_booking'));
            $companySaasEnabled = $company_user && function_exists('company_saas_enabled') && company_saas_enabled();
            $communicationMenuActive = in_array($currentUri, [$first_segment . '/send_message', $first_segment . '/notifications', 'admin/send_email', 'admin/email_settings', $first_segment . '/support']);
        @endphp
        <a href="{{ url(LOGIN_USER_TYPE . '/dashboard') }}" class="fs-18 fw-semibold">
            <img height="30" class="pe-app-sidebar-logo-default d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $horizontalLogoUrl }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $horizontalLogoUrl }}">
            <img height="30" width="30" class="pe-app-sidebar-logo-minimize d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $horizontalFavicon }}">
            <img height="30" width="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $horizontalFavicon }}">
        </a>
    </div>

    <nav class="pe-app-sidebar-menu nav nav-pills">
        <ul class="pe-horizontal-menu list-unstyled" id="horizontal-menu">
            <li class="{{ Route::current()->uri() == $first_segment . '/dashboard' ? 'active' : '' }} pe-slide">
                <a href="{{ url($first_segment . '/dashboard') }}" class="pe-nav-link">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">{{ __('messages.admin.dashboard') }}</span>
                </a>
            </li>

            <li class="pe-menu-title">{{ __('messages.blade_static.user_management_24b2079b') }}</li>

            @if($company_user || @$user->can('manage_admin') || @$user->can('view_company') || @$user->can('view_driver') || @$user->can('view_rider') || @$user->can('manage_driver_levels') || @$user->can('manage_rider_levels'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseUsers" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseUsers">
                        <i class="bi bi-people pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.user_management') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseUsers">
                        @if(@$user->can('manage_admin'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/admin_user' ? 'active' : '' }}">
                                <a href="{{ url('admin/admin_user') }}" class="pe-nav-link">{{ __('messages.admin.manage_admin.admin_users') }}</a>
                            </li>
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/roles' ? 'active' : '' }}">
                                <a href="{{ url('admin/roles') }}" class="pe-nav-link">{{ __('messages.admin.manage_admin.role_and_permissions') }}</a>
                            </li>
                        @endif
                        @if(@$user->can('view_company'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/company' ? 'active' : '' }}">
                                <a href="{{ url('admin/company') }}" class="pe-nav-link">{{ __('messages.admin.manage_company') }}</a>
                            </li>
                            <li class="pe-slide-item {{ request()->is('admin/company-onboarding*') ? 'active' : '' }}">
                                <a href="{{ url('admin/company-onboarding') }}" class="pe-nav-link">Company Onboarding</a>
                            </li>
                            <li class="pe-slide-item {{ request()->is($first_segment.'/document-reviews/company-documents') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/document-reviews/company-documents') }}" class="pe-nav-link">Company Documents Review</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('view_driver'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/driver' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/driver') }}" class="pe-nav-link">{{ __('messages.admin.manage_drivers') }}</a>
                            </li>
                            <li class="pe-slide-item {{ request()->is($first_segment.'/document-reviews/driver-documents') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/document-reviews/driver-documents') }}" class="pe-nav-link">Driver Documents Review</a>
                            </li>
                            <li class="pe-slide-item {{ request()->is($first_segment.'/document-reviews/profile-images') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/document-reviews/profile-images') }}" class="pe-nav-link">Driver Profile Images</a>
                            </li>
                        @endif
                        @if(($companySaasEnabled && operation_team_can('manage_driver_levels')) || (!$company_user && (@$user->can('manage_driver_levels') || @$user->can('manage_admin'))))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/driver-levels' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/driver-levels') }}" class="pe-nav-link">Driver Levels</a>
                            </li>
                        @endif
                        @if(($company_user && (!current_operation_team_member() || operation_team_can('view_rider'))) || @$user->can('view_rider'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/rider' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/rider') }}" class="pe-nav-link">{{ __('messages.admin.manage_riders') }}</a>
                            </li>
                        @endif
                        @if($company_user && operation_team_can('manage_operation_team_roles'))
                            <li class="pe-slide-item {{ request()->is('company/operation-team/roles*') ? 'active' : '' }}">
                                <a href="{{ url('company/operation-team/roles') }}" class="pe-nav-link">Role Management</a>
                            </li>
                        @endif
                        @if($company_user && operation_team_can('manage_operation_team_members'))
                            <li class="pe-slide-item {{ request()->is('company/operation-team/members*') ? 'active' : '' }}">
                                <a href="{{ url('company/operation-team/members') }}" class="pe-nav-link">Operation Team</a>
                            </li>
                        @endif
                        @if(($companySaasEnabled && operation_team_can('manage_rider_levels')) || (!$company_user && (@$user->can('manage_rider_levels') || @$user->can('manage_admin'))))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/customer-levels' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/customer-levels') }}" class="pe-nav-link">Customer Levels</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <li class="pe-menu-title">{{ __('messages.blade_static.vehicles_operations_ba5577fb') }}</li>

            @if($company_user || @$user->can('manage_vehicle') || @$user->can('manage_vehicle_type') || @$user->can('view_vehicle_make') || @$user->can('view_vehicle_model'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseVehicles" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseVehicles">
                        <i class="bi bi-truck-flatbed pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.vehicles_management') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseVehicles">
                        @if($company_user || @$user->can('manage_vehicle'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/vehicle' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/vehicle') }}" class="pe-nav-link">{{ __('messages.admin.manage_vehicle_type.vehicles') }}</a>
                            </li>
                            <li class="pe-slide-item {{ request()->is($first_segment.'/document-reviews/vehicle-documents') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/document-reviews/vehicle-documents') }}" class="pe-nav-link">Vehicle Documents Review</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_vehicle_type'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/vehicle_type' ? 'active' : '' }}">
                                <a href="{{ url('admin/vehicle_type') }}" class="pe-nav-link">{{ __('messages.admin.manage_vehicle_type.vehicle_types') }}</a>
                            </li>
                        @endif
                        @if($companySaasEnabled && operation_team_can('manage_company_vehicle_types'))
                            <li class="pe-slide-item {{ str_contains(Route::current()->uri(), 'vehicle-types') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/saas-dispatch/vehicle-types') }}" class="pe-nav-link">Vehicle Types</a>
                            </li>
                        @endif
                        @if($companySaasEnabled && operation_team_can('manage_company_documents') && saas_feature_module_enabled('company_documents'))
                            <li class="pe-slide-item {{ str_contains(Route::current()->uri(), 'saas-dispatch/documents') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/saas-dispatch/documents') }}" class="pe-nav-link">Documents Management</a>
                            </li>
                        @endif
                        @if($companySaasEnabled && operation_team_can('manage_company_payment_gateways'))
                            <li class="pe-slide-item {{ str_contains(Route::current()->uri(), 'payment-gateways') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/saas-dispatch/payment-gateways') }}" class="pe-nav-link">Payment Gateways</a>
                            </li>
                        @endif
                        @if(@$user->can('view_vehicle_make'))
                            <li class="pe-slide-item {{ in_array(Route::current()->uri(), ['admin/vehicle_make', 'admin/add-vehicle-make', 'admin/edit-vehicle-make/{id}']) ? 'active' : '' }}">
                                <a href="{{ url('admin/vehicle_make') }}" class="pe-nav-link">{{ __('messages.admin.vehicle_make') }}</a>
                            </li>
                        @endif
                        @if(@$user->can('view_vehicle_model'))
                            <li class="pe-slide-item {{ in_array(Route::current()->uri(), ['admin/vehicle_model', 'admin/add-vehicle_model', 'admin/edit-vehicle_model/{id}']) ? 'active' : '' }}">
                                <a href="{{ url('admin/vehicle_model') }}" class="pe-nav-link">{{ __('messages.admin.vehicle_model') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if($companySaasEnabled && operation_team_can('manage_company_service_areas'))
                <li class="{{ str_contains(Route::current()->uri(), 'service-areas') || str_contains(Route::current()->uri(), 'manage-zones') ? 'active' : '' }} pe-slide">
                    <a href="{{ url($first_segment . '/saas-dispatch/manage-zones') }}" class="pe-nav-link">
                        <i class="bi bi-bounding-box-circles pe-nav-icon"></i>
                        <span class="pe-nav-content">Manage Zones</span>
                    </a>
                </li>
            @endif

            @if($companySaasEnabled && operation_team_can('manage_company_networking') && saas_feature_module_enabled('networking'))
                <li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/networking') ? 'active' : '' }} pe-slide">
                    <a href="{{ url($first_segment . '/saas-dispatch/networking') }}" class="pe-nav-link">
                        <i class="bi bi-diagram-3 pe-nav-icon"></i>
                        <span class="pe-nav-content">Networking</span>
                    </a>
                </li>
            @endif

            @if($companySaasEnabled && operation_team_can('manage_company_accounts_invoicing') && saas_feature_module_enabled('accounts_invoicing'))
                <li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/accounts') ? 'active' : '' }} pe-slide">
                    <a href="{{ url($first_segment . '/saas-dispatch/accounts') }}" class="pe-nav-link">
                        <i class="bi bi-receipt-cutoff pe-nav-icon"></i>
                        <span class="pe-nav-content">Accounts / Invoicing</span>
                    </a>
                </li>
            @endif

            @if($companySaasEnabled && operation_team_can('manage_company_data_suite') && saas_feature_module_enabled('data_suite'))
                <li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/data-suite') ? 'active' : '' }} pe-slide">
                    <a href="{{ url($first_segment . '/saas-dispatch/data-suite') }}" class="pe-nav-link">
                        <i class="bi bi-bar-chart-line pe-nav-icon"></i>
                        <span class="pe-nav-content">Data Suite</span>
                    </a>
                </li>
            @endif

            @if($companySaasEnabled && operation_team_can('manage_company_messages') && saas_feature_module_enabled('messages'))
                <li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/messages') ? 'active' : '' }} pe-slide">
                    <a href="{{ url($first_segment . '/saas-dispatch/messages') }}" class="pe-nav-link">
                        <i class="bi bi-chat-dots pe-nav-icon"></i>
                        <span class="pe-nav-content">Messages</span>
                    </a>
                </li>
            @endif

            @if($companySaasEnabled && operation_team_can('manage_company_support_tickets') && saas_feature_module_enabled('support_system_status'))
                <li class="{{ str_contains(Route::current()->uri(), 'saas-dispatch/support-system-status') ? 'active' : '' }} pe-slide">
                    <a href="{{ url($first_segment . '/saas-dispatch/support-system-status') }}" class="pe-nav-link">
                        <i class="bi bi-life-preserver pe-nav-icon"></i>
                        <span class="pe-nav-content">Support & System Status</span>
                    </a>
                </li>
            @endif

            <li class="pe-menu-title">{{ __('messages.blade_static.bookings_trips_532ce7d1') }}</li>

            @if($company_user || @$user->can('manage_requests') || @$user->can('manage_trips') || @$user->can('manage_payments') || @$user->can('manage_rating') || @$user->can('manage_manual_booking'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseBookings" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseBookings">
                        <i class="bi bi-car-front-fill pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.bookings_trips') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseBookings">
                        @if($manualBookingMenuVisible)
                            <li class="pe-slide-item {{ $currentUri == $first_segment . '/manual_booking/{id?}' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/manual_booking') }}" class="pe-nav-link">Manual Bookingoking</a>
                            </li>
                        @endif
                        @if($laterBookingMenuVisible)
                            <li class="pe-slide-item {{ $currentUri == $first_segment . '/later_booking' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/later_booking') }}" class="pe-nav-link">Scheduled / Later Bookings</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_trips'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/trips' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/trips') }}" class="pe-nav-link">Trip Operations Center</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_requests'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/request' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/request') }}" class="pe-nav-link">Booking Requests</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_payments'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/payments' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/payments') }}" class="pe-nav-link">Payments & Collections</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_rating'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/rating' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/rating') }}" class="pe-nav-link">Ratings & Feedback</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_statements'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/statements/overall' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/statements/overall') }}" class="pe-nav-link">Trip Reports</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(!$company_user && (@$user->can('manage_parcel_categories') || @$user->can('manage_parcel_package_sizes') || @$user->can('manage_parcel_tasks') || @$user->can('manage_parcel_pricing') || @$user->can('manage_parcel_settings') || @$user->can('manage_parcel_trips')))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseParcel" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseParcel">
                        <i class="bi bi-box-seam pe-nav-icon"></i>
                        <span class="pe-nav-content">Parcel</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseParcel">
                        @if(@$user->can('manage_parcel_categories'))
                            <li class="pe-slide-item {{ $currentUri == 'admin/parcel_categories' ? 'active' : '' }}">
                                <a href="{{ url('admin/parcel_categories') }}" class="pe-nav-link">Categories</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_parcel_package_sizes'))
                            <li class="pe-slide-item {{ $currentUri == 'admin/parcel_package_sizes' ? 'active' : '' }}">
                                <a href="{{ url('admin/parcel_package_sizes') }}" class="pe-nav-link">Package Sizes</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_parcel_tasks'))
                            <li class="pe-slide-item {{ $currentUri == 'admin/parcel_tasks' ? 'active' : '' }}">
                                <a href="{{ url('admin/parcel_tasks') }}" class="pe-nav-link">Tasks</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_parcel_pricing'))
                            <li class="pe-slide-item {{ in_array($currentUri, ['admin/parcel_pricing', 'admin/add_parcel_pricing', 'admin/edit_parcel_pricing/{id}']) ? 'active' : '' }}">
                                <a href="{{ url('admin/parcel_pricing') }}" class="pe-nav-link">Pricing</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_parcel_settings'))
                            <li class="pe-slide-item {{ $currentUri == 'admin/parcel_settings' ? 'active' : '' }}">
                                <a href="{{ url('admin/parcel_settings') }}" class="pe-nav-link">Settings</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_parcel_trips'))
                            <li class="pe-slide-item {{ in_array($currentUri, ['admin/parcel_trips', 'admin/view_parcel_trip/{id}', 'admin/cancel_parcel_trip/{id}']) ? 'active' : '' }}">
                                <a href="{{ url('admin/parcel_trips') }}" class="pe-nav-link">Trips</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <li class="pe-menu-title">{{ __('messages.blade_static.finance_payouts_cc082e71') }}</li>

            @if($company_user || @$user->can('manage_driver_payments') || @$user->can('manage_company_payment'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapsePayouts" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapsePayouts">
                        <i class="bi bi-currency-dollar pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_payouts.title') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapsePayouts">
                        @if(@$user->can('manage_company_payment'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/payout/company/overall' ? 'active' : '' }}">
                                <a href="{{ url('admin/payout/company/overall') }}" class="pe-nav-link">{{ __('messages.admin.manage_payouts.company_payouts') }}</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_driver_payments'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/payout/overall' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/payout/overall') }}" class="pe-nav-link">{{ __('messages.admin.manage_payouts.driver_payouts') }}</a>
                            </li>
                        @endif
                        @if($company_user && $user->id != 1)
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/payout_preferences' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/payout_preferences') }}" class="pe-nav-link">{{ __('messages.admin.payout_preference') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <li class="pe-menu-title">{{ __('messages.blade_static.promotions_referrals_f821d3cb') }}</li>

            @if($company_user || @$user->can('manage_wallet') || @$user->can('manage_promo_code'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseWalletPromo" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseWalletPromo">
                        <i class="bi bi-wallet2 pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_wallet_and_promo.title') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseWalletPromo">
                        @if($company_user || @$user->can('manage_wallet'))
                            <li class="pe-slide-item {{ @$navigation == 'manage_wallet' ? 'active' : '' }}">
                                <a href="{{ route('wallet', ['user_type' => 'Rider']) }}" class="pe-nav-link">{{ __('messages.admin.manage_wallet_and_promo.manage_wallet_amount') }}</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_promo_code'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/promo_code' ? 'active' : '' }}">
                                <a href="{{ url('admin/promo_code') }}" class="pe-nav-link">{{ __('messages.admin.manage_wallet_and_promo.manage_promo_code') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(@$user->can('manage_rider_referrals') || @$user->can('manage_driver_referrals'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseReferrals" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseReferrals">
                        <i class="bi bi-person-plus pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.referrals.title') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseReferrals">
                        @if(@$user->can('manage_rider_referrals'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/referrals/rider' ? 'active' : '' }}">
                                <a href="{{ url('admin/referrals/rider') }}" class="pe-nav-link">{{ __('messages.admin.referrals.riders') }}</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_driver_referrals'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/referrals/driver' ? 'active' : '' }}">
                                <a href="{{ url('admin/referrals/driver') }}" class="pe-nav-link">{{ __('messages.admin.referrals.drivers') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <li class="pe-menu-title">{{ __('messages.blade_static.reports_analytics_b4504ec7') }}</li>

            @if($company_user || @$user->can('manage_statements'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseStatements" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseStatements">
                        <i class="bi bi-graph-up pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_statements.title') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse" id="collapseStatements">
                        <li class="pe-slide-item {{ request()->type == 'overall' ? 'active' : '' }}">
                            <a href="{{ url($first_segment . '/statements/overall') }}" class="pe-nav-link">{{ __('messages.admin.manage_statements.overall_statements') }}</a>
                        </li>
                        <li class="pe-slide-item {{ request()->type == 'driver' ? 'active' : '' }}">
                            <a href="{{ url($first_segment . '/statements/driver') }}" class="pe-nav-link">{{ __('messages.admin.manage_statements.drivers_statments') }}</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if($company_user || @$user->can('manage_map') || @$user->can('manage_heat_map') || @$user->can('manage_admin'))
                @php $mapMenuActive = request()->is($first_segment . '/map*') || request()->is($first_segment . '/heat-map*'); @endphp
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseMap" class="pe-nav-link {{ $mapMenuActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ $mapMenuActive ? 'true' : 'false' }}" aria-controls="collapseMap">
                        <i class="bi bi-map pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_map.title') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse {{ $mapMenuActive ? 'show' : '' }}" id="collapseMap">
                        @if($company_user || @$user->can('manage_map'))
                            <li class="pe-slide-item {{ request()->is($first_segment . '/map*') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/map') }}" class="pe-nav-link">Fleet View</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_heat_map') || @$user->can('manage_admin'))
                            <li class="pe-slide-item {{ request()->is($first_segment . '/heat-map*') ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/heat-map') }}" class="pe-nav-link">{{ __('messages.admin.manage_map.heat_map') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(($companySaasEnabled && operation_team_can('manage_company_surge_pricing')) || (!$company_user && (@$user->can('manage_surge_pricing') || @$user->can('manage_admin'))))
                <li class="pe-slide {{ request()->is($first_segment . '/surge-pricing*') ? 'active' : '' }}">
                    <a href="{{ url($first_segment . '/surge-pricing') }}" class="pe-nav-link">
                        <i class="bi bi-lightning-charge pe-nav-icon"></i>
                        <span class="pe-nav-content">Surge Pricing</span>
                    </a>
                </li>
            @endif

            <li class="pe-menu-title">{{ __('messages.blade_static.communication_ade0d50c') }}</li>

            @if($company_user || @$user->can('manage_send_message') || @$user->can('manage_send_email') || @$user->can('manage_email_settings') || @$user->can('manage_support'))
                <li class="pe-slide pe-has-sub">
                    <a href="#collapseCommunication" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="{{ $communicationMenuActive ? 'true' : 'false' }}" aria-controls="collapseCommunication">
                        <i class="bi bi-envelope pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.communication') }}</span>
                        <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                    </a>
                    <ul class="pe-slide-menu collapse {{ $communicationMenuActive ? 'show' : '' }}" id="collapseCommunication">
                        @if($company_user || @$user->can('manage_send_message'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/notifications' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/notifications') }}" class="pe-nav-link">Notification Management</a>
                            </li>
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/send_message' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/send_message') }}" class="pe-nav-link">{{ __('messages.admin.send_messages') }}</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_send_email'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/send_email' ? 'active' : '' }}">
                                <a href="{{ url('admin/send_email') }}" class="pe-nav-link">{{ __('messages.admin.manage_emails.send_mail') }}</a>
                            </li>
                        @endif
                        @if(@$user->can('manage_email_settings'))
                            <li class="pe-slide-item {{ Route::current()->uri() == 'admin/email_settings' ? 'active' : '' }}">
                                <a href="{{ url('admin/email_settings') }}" class="pe-nav-link">{{ __('messages.admin.manage_emails.email_settings') }}</a>
                            </li>
                        @endif
                        @if($company_user || @$user->can('manage_support'))
                            <li class="pe-slide-item {{ Route::current()->uri() == $first_segment . '/support' ? 'active' : '' }}">
                                <a href="{{ url($first_segment . '/support') }}" class="pe-nav-link">{{ __('messages.admin.manage_support.title') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(@$user->can('view_documents'))
                <li class="{{ Route::current()->uri() == 'admin/documents' ? 'active' : '' }} pe-slide">
                    <a href="{{ url('admin/documents') }}" class="pe-nav-link">
                        <i class="bi bi-folder pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_documents.title') }}</span>
                    </a>
                </li>
            @endif

            @if(@$user->can('manage_peak_based_fare'))
                <li class="{{ Route::current()->uri() == 'admin/manage_fare' ? 'active' : '' }} pe-slide">
                    <a href="{{ url('admin/manage_fare') }}" class="pe-nav-link">
                        <i class="bi bi-cash-coin pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_fares.title') }}</span>
                    </a>
                </li>
            @endif

            @if(@$user->can('view_manage_reason'))
                <li class="{{ Route::current()->uri() == 'admin/cancel-reason' ? 'active' : '' }} pe-slide">
                    <a href="{{ url('admin/cancel-reason') }}" class="pe-nav-link">
                        <i class="bi bi-x-circle pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_cancel_reason.title') }}</span>
                    </a>
                </li>
            @endif

            @if(@$user->can('view_additional_reason'))
                <li class="{{ Route::current()->uri() == 'admin/additional-reasons' ? 'active' : '' }} pe-slide">
                    <a href="{{ url('admin/additional-reasons') }}" class="pe-nav-link">
                        <i class="bi bi-signpost-split pe-nav-icon"></i>
                        <span class="pe-nav-content">Additional Reasons</span>
                    </a>
                </li>
            @endif

            @if(@$user->can('manage_locations'))
                <li class="{{ Route::current()->uri() == 'admin/locations' ? 'active' : '' }} pe-slide">
                    <a href="{{ url('admin/locations') }}" class="pe-nav-link">
                        <i class="bi bi-geo-alt pe-nav-icon"></i>
                        <span class="pe-nav-content">{{ __('messages.admin.manage_locations.title') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
</aside>
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
