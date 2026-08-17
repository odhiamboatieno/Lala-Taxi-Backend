<header class="app-header" id="appHeader">
    <div class="container-fluid w-100">
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

            $headerLogoUrl = $logo_url ?? (defined('LOGO_URL') ? url(LOGO_URL) : url('images/logos/logo.png'));
            $headerFavicon = $favicon ?? $headerLogoUrl;
            $manualBookingEnabledForCompany = !$company_user || manual_booking_module_enabled();
            $manualBookingMenuVisible = ($company_user && @$user->status == 'Active' && $manualBookingEnabledForCompany) || (!$company_user && @$user->can('manage_manual_booking'));
            $laterBookingMenuVisible = ($company_user && $manualBookingEnabledForCompany) || (!$company_user && @$user->can('manage_manual_booking'));
            $companySaasEnabled = $company_user && function_exists('company_saas_enabled') && company_saas_enabled();
            $defaultLanguageItem = $default_language[0] ?? null;
            $languageOptions = $language ?? [];
            $currentLocale = App::getLocale() ?: 'en';
            $headerLanguageCode = $defaultLanguageItem->value ?? $currentLocale;
            $headerLanguageName = $defaultLanguageItem->name ?? strtoupper($headerLanguageCode);
            $headerFlagCode = file_exists(public_path('assets/images/flag/' . $headerLanguageCode . '.svg')) ? $headerLanguageCode : 'en';
        @endphp

        <div class="d-flex align-items-center">
            <div class="me-auto">
                <div class="d-inline-flex align-items-center gap-5">
                    <a href="{{ url($first_segment . '/dashboard') }}" class="fs-18 fw-semibold">
                        <img height="30" class="header-sidebar-logo-default d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $headerLogoUrl }}">
                        <img height="30" class="header-sidebar-logo-light d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $headerLogoUrl }}">
                        <img height="30" width="30" class="header-sidebar-logo-small d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $headerFavicon }}">
                        <img height="30" width="30" class="header-sidebar-logo-small-light d-none" alt="{{ __('messages.blade_static.logo_83fce832') }}" src="{{ $headerFavicon }}">
                    </a>
                    <button type="button" class="vertical-toggle btn btn-light-light text-muted icon-btn fs-5 rounded-pill" id="toggleSidebar">
                        <i class="bi bi-arrow-bar-left header-icon"></i>
                    </button>
                    <button type="button" class="horizontal-toggle btn btn-light-light text-muted icon-btn fs-5 rounded-pill d-none" id="toggleHorizontal">
                        <i class="ri-menu-2-line header-icon"></i>
                    </button>

                    <div class="header-dropdown d-flex align-items-center">
                        @if(!$company_user)
                            <div class="dropdown pe-dropdown-mega pe-dropdown-hover">
                                <button class="btn pe-dropdown-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ __('messages.admin.manage_setting') }}
                                </button>
                                <div class="dropdown-menu dropdown-mega-xl p-0">
                                    <div class="p-4 border-bottom d-flex align-items-center gap-4">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fs-15">{{ __('messages.blade_static.ready_to_begin_your_journey_15c7a2d6') }}</h6>
                                            <p class="mb-0 text-muted">{{ __('messages.blade_static.explore_your_core_platform_controls_and_operational_sett_081c4840') }}</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <a href="{{ url($first_segment . '/dashboard') }}" class="btn btn-sm btn-primary">{{ __('messages.blade_static.open_dashboard_b9e922eb') }}</a>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <p class="mb-1 text-uppercase text-muted fs-12">{{ __('messages.blade_static.settings_c7f73bb5') }}</p>
                                                <ul class="list-unstyled mb-0">
                                                    @if(@$user->can('manage_mobile_app_version'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/mobile_app_version') }}">{{ __('messages.admin.manage_mobile_app_version.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_api_credentials'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/api_credentials') }}">{{ __('messages.admin.api_credentials.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_payment_gateway'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/payment_gateway') }}">{{ __('messages.admin.payment_gateway.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_fees'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/fees') }}">{{ __('messages.admin.mange_fees.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_referral_settings'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/referral_settings') }}">{{ __('messages.admin.manage_referral_settings.title') }}</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="col-lg-4">
                                                <p class="mb-1 text-uppercase text-muted fs-12">{{ __('messages.blade_static.content_4f9be057') }}</p>
                                                <ul class="list-unstyled mb-0">
                                                    @if(@$user->can('manage_metas'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/metas') }}">{{ __('messages.admin.manage_metas.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_country'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/country') }}">{{ __('messages.admin.manage_country.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_currency'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/currency') }}">{{ __('messages.admin.manage_currency.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_language'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/language') }}">{{ __('messages.admin.manage_language.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_static_pages'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/pages') }}">{{ __('messages.admin.manage_static_pages.title') }}</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="col-lg-4">
                                                <p class="mb-1 text-uppercase text-muted fs-12">{{ __('messages.blade_static.system_bc0792d8') }}</p>
                                                <ul class="list-unstyled mb-0">
                                                    @if(@$user->can('manage_join_us'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/join_us') }}">{{ __('messages.admin.join_us_links.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_support'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/support') }}">{{ __('messages.admin.manage_support.title') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_banner'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/banner') }}">{{ __('messages.blade_static.manage_banners_ec8d2e20') }}</a></li>
                                                    @endif
                                                    @if(@$user->can('manage_site_settings'))
                                                        <li><a class="dropdown-item" href="{{ url('admin/site_setting') }}">{{ __('messages.blade_static.system_configuration_2e7092b2') }}</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="dropdown pe-dropdown-mega pe-dropdown-hover">
                            <button class="btn pe-dropdown-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('messages.blade_static.quick_access_menu_6ff56d27') }}
                            </button>
                            <div class="dropdown-menu dropdown-mega-lg p-0">
                                <div class="p-4 d-flex align-items-center gap-4 bg-primary">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fs-15 text-bg-primary">{{ __('messages.blade_static.quick_access_menu_6ff56d27') }}</h6>
                                        <p class="mb-0 text-white text-opacity-75">{{ __('messages.blade_static.frequently_used_actions_and_operational_shortcuts_e70b87a3') }}</p>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <p class="mb-1 text-uppercase text-muted fs-12">{{ __('messages.blade_static.users_57f2b181') }}</p>
                                            <ul class="list-unstyled mb-0">
                                                @if(@$user->can('view_company'))
                                                    <li><a class="dropdown-item" href="{{ url('admin/company') }}">{{ __('messages.admin.manage_company') }}</a></li>
                                                @endif
                                                @if($company_user || @$user->can('view_driver'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/driver') }}">{{ __('messages.admin.manage_drivers') }}</a></li>
                                                @endif
                                                @if(($companySaasEnabled && operation_team_can('manage_driver_levels')) || (!$company_user && (@$user->can('manage_driver_levels') || @$user->can('manage_admin'))))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/driver-levels') }}">Driver Levels</a></li>
                                                @endif
                                                @if(($company_user && (!current_operation_team_member() || operation_team_can('view_rider'))) || @$user->can('view_rider'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/rider') }}">{{ __('messages.admin.manage_riders') }}</a></li>
                                                @endif
                                                @if(($companySaasEnabled && operation_team_can('manage_rider_levels')) || (!$company_user && (@$user->can('manage_rider_levels') || @$user->can('manage_admin'))))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/customer-levels') }}">Customer Levels</a></li>
                                                @endif
                                                @if($company_user && operation_team_can('manage_operation_team_roles'))
                                                    <li><a class="dropdown-item" href="{{ url('company/operation-team/roles') }}">Role Management</a></li>
                                                @endif
                                                @if($company_user && operation_team_can('manage_operation_team_members'))
                                                    <li><a class="dropdown-item" href="{{ url('company/operation-team/members') }}">Operation Team</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="col-lg-4">
                                            <p class="mb-1 text-uppercase text-muted fs-12">{{ __('messages.blade_static.trips_d82b7e45') }}</p>
                                            <ul class="list-unstyled mb-0">
                                                @if($manualBookingMenuVisible)
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/manual_booking') }}">Dispatch Console / Manual Booking</a></li>
                                                @endif
                                                @if($laterBookingMenuVisible)
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/later_booking') }}">Scheduled / Later Bookings</a></li>
                                                @endif
                                                @if($company_user || @$user->can('manage_trips'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/trips') }}">Trip Operations Center</a></li>
                                                @endif
                                                @if($company_user || @$user->can('manage_requests'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/request') }}">Booking Requests</a></li>
                                                @endif
                                                @if($company_user || @$user->can('manage_statements'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/statements/overall') }}">Trip Reports</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="col-lg-4">
                                            <p class="mb-1 text-uppercase text-muted fs-12">Operations</p>
                                            <ul class="list-unstyled mb-0">
                                                @if($company_user || @$user->can('manage_payments'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/payments') }}">Payments & Collections</a></li>
                                                @endif
                                                @if($company_user || @$user->can('manage_rating'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/rating') }}">Ratings & Feedback</a></li>
                                                @endif
                                                @if(@$user->can('manage_peak_based_fare'))
                                                    <li><a class="dropdown-item" href="{{ url('admin/manage_fare') }}">{{ __('messages.admin.manage_fares.title') }}</a></li>
                                                @endif
                                                @if(@$user->can('manage_locations'))
                                                    <li><a class="dropdown-item" href="{{ url('admin/locations') }}">{{ __('messages.admin.manage_locations.title') }}</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_service_areas'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/manage-zones') }}">Manage Zones</a></li>
                                                @endif
                                                @if(($companySaasEnabled && operation_team_can('manage_company_vehicle_types')) || (!$company_user && @$user->can('manage_vehicle_type')))
                                                    <li><a class="dropdown-item" href="{{ url($company_user ? $first_segment . '/saas-dispatch/vehicle-types' : 'admin/vehicle_type') }}">Vehicle Types</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_documents') && saas_feature_module_enabled('company_documents'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/documents') }}">Documents Management</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_payment_gateways'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/payment-gateways') }}">Payment Gateways</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_fees'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/fee-settings') }}">Manage Fees</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_dispatch_settings'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/dispatch-settings') }}">Manage Settings</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_networking') && saas_feature_module_enabled('networking'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/networking') }}">Networking</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_accounts_invoicing') && saas_feature_module_enabled('accounts_invoicing'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/accounts') }}">Accounts / Invoicing</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_data_suite') && saas_feature_module_enabled('data_suite'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/data-suite') }}">Data Suite</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_messages') && saas_feature_module_enabled('messages'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/messages') }}">Messages</a></li>
                                                @endif
                                                @if($companySaasEnabled && operation_team_can('manage_company_support_tickets') && saas_feature_module_enabled('support_system_status'))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/saas-dispatch/support-system-status') }}">Support & System Status</a></li>
                                                @endif
                                                @if(($companySaasEnabled && operation_team_can('manage_company_surge_pricing')) || (!$company_user && (@$user->can('manage_surge_pricing') || @$user->can('manage_admin'))))
                                                    <li><a class="dropdown-item" href="{{ url($first_segment . '/surge-pricing') }}">Surge Pricing</a></li>
                                                @endif
                                                @if(@$user->can('view_documents'))
                                                    <li><a class="dropdown-item" href="{{ url('admin/documents') }}">{{ __('messages.admin.manage_documents.title') }}</a></li>
                                                @endif
                                                @if(! $company_user && @$user->can('manage_parcel_trips'))
                                                    <li><a class="dropdown-item" href="{{ url('admin/parcel_trips') }}">Parcel Trips</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(@$user->can('manage_help'))
                            <div class="dropdown pe-dropdown-mega pe-dropdown-hover">
                                <button class="btn pe-dropdown-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ __('messages.blade_static.help_c47ae153') }}
                                </button>
                                <div class="dropdown-menu p-4">
                                    <p class="mb-1 text-uppercase text-muted fs-12">{{ __('messages.blade_static.help_support_2746ea55') }}</p>
                                    <ul class="list-unstyled mb-0">
                                        <li><a class="dropdown-item" href="{{ url('admin/help') }}">{{ __('messages.admin.manage_help.help') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ url('admin/help_category') }}">{{ __('messages.admin.manage_help.category') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ url('admin/help_subcategory') }}">{{ __('messages.admin.manage_help.subcategory') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if($manualBookingMenuVisible)
                            <a href="{{ url($first_segment . '/manual_booking') }}" class="btn btn-primary">
                                Add Booking
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 d-flex align-items-center gap-1">
                <div class="dark-mode-btn" id="toggleMode">
                    <button class="btn header-btn active" id="lightModeBtn">
                        <i class="bi bi-brightness-high"></i>
                    </button>
                    <button class="btn header-btn" id="darkModeBtn">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </div>

                <div class="dropdown pe-dropdown-mega d-none d-md-block">
                    <button class="btn btn-icon header-btn p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('assets/images/flag/' . $headerFlagCode . '.svg') }}"
                             alt="{{ $headerLanguageName }} Flag"
                             height="16" width="16"
                             class="object-fit-cover rounded">
                    </button>
                    <ul class="dropdown-menu header-dropdown-menu">
                        @foreach ($languageOptions as $key => $lang)
                            @if ($key != App::getLocale())
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('switch.locale', $key) }}">
                                        <img src="{{ asset('assets/images/flag/' . (file_exists(public_path('assets/images/flag/' . $key . '.svg')) ? $key : 'en') . '.svg') }}"
                                             alt="{{ $lang }} Flag"
                                             height="16" width="16"
                                             class="object-fit-cover rounded">
                                        {{ $lang }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div class="dropdown pe-dropdown-mega d-none d-md-block">
                    <button class="header-profile-btn btn gap-1 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="header-btn btn position-relative">
                            @if (!$company_user || $user->profile == null)
                                <img src="{{ url('assets/images/avatar/avatar-10.jpg') }}" alt="{{ __('messages.blade_static.avatar_image_34bab014') }}" class="img-fluid rounded-circle">
                            @else
                                <img src="{{ $user->profile }}" alt="{{ __('messages.blade_static.avatar_image_34bab014') }}" class="img-fluid rounded-circle">
                            @endif
                            <span class="position-absolute translate-middle badge border border-light rounded-circle bg-success"><span class="visually-hidden">{{ __('messages.blade_static.account_status_281b2457') }}</span></span>
                        </span>
                        <div class="d-none d-lg-block pe-2">
                            <span class="d-block mb-0 fs-13 fw-semibold">{{ !$company_user ? $user->username : $user->name }}</span>
                            <span class="d-block mb-0 fs-12 text-muted">{{ $company_user ? 'Company Account' : 'Administrator' }}</span>
                        </div>
                    </button>
                    <div class="dropdown-menu dropdown-mega-sm header-dropdown-menu p-3">
                        <div class="border-bottom pb-2 mb-2 d-flex align-items-center gap-2">
                            @if (!$company_user || $user->profile == null)
                                <img src="{{ url('assets/images/avatar/avatar-10.jpg') }}" alt="{{ __('messages.blade_static.avatar_image_34bab014') }}" class="avatar-md">
                            @else
                                <img src="{{ $user->profile }}" alt="{{ __('messages.blade_static.avatar_image_34bab014') }}" class="avatar-md">
                            @endif
                            <div>
                                <h6 class="mb-0 lh-base">{{ !$company_user ? $user->username : $user->name }}</h6>
                                <p class="mb-0 fs-13 text-muted">{{ $user->email }}</p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-1 border-bottom pb-1">
                            @foreach(($headerProfileMenu ?? []) as $item)
                                <li>
                                    <a class="dropdown-item" href="{{ $item['url'] }}">
                                        <i class="bi bi-{{ $item['icon'] }} me-1"></i> {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <ul class="list-unstyled mb-0">
                            <li><a class="dropdown-item" href="{{ url(LOGIN_USER_TYPE . '/logout') }}"><i class="bi bi-box-arrow-right me-1"></i> {{ __('messages.blade_static.sign_out_61fd08ff') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
