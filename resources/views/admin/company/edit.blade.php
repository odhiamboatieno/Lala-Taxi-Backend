@extends('admin.template')

@section('title', 'Company Profile | FabKin Admin & Dashboards Template')
@section('title-sub', 'Company Profile')
@section('pagetitle', 'Company Profile Center')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <style>
        .company-profile-shell {
            --company-primary: var(--color-as-0f766e);
            --company-primary-soft: var(--color-as-rgba-15-118-110-0-12);
            --company-accent: var(--color-info);
            --company-border: var(--color-as-rgba-148-163-184-0-22);
            --company-shadow: 0 22px 50px var(--color-as-rgba-15-23-42-0-08);
        }

        .company-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 0;
            box-shadow: var(--company-shadow);
            background: var(--color-as-0f172a);
            color: var(--color-white);
        }

        .company-hero::after {
            content: '';
            position: absolute;
            inset: auto -8% -42% auto;
            width: 280px;
            height: 280px;
            border-radius: 0;
            background: var(--color-as-rgba-255-255-255-0-08);
            pointer-events: none;
        }

        .company-hero__body {
            position: relative;
            z-index: 1;
            padding: 1.75rem;
        }

        .company-hero__identity {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .company-avatar {
            width: 92px;
            height: 92px;
            border-radius: 0;
            overflow: hidden;
            background: var(--color-as-rgba-255-255-255-0-14);
            border: 1px solid var(--color-as-rgba-255-255-255-0-18);
            box-shadow: 0 14px 30px var(--color-as-rgba-15-23-42-0-2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
        }

        .company-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .company-hero__meta h2 {
            margin: 0 0 .4rem;
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .company-hero__sub {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            color: var(--color-as-rgba-255-255-255-0-84);
            font-size: .96rem;
        }

        .company-badges {
            display: flex;
            gap: .65rem;
            flex-wrap: wrap;
            margin-top: .95rem;
        }

        .company-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .48rem .8rem;
            border-radius: 0;
            font-size: .84rem;
            font-weight: 700;
            background: var(--color-as-rgba-255-255-255-0-12);
            border: 1px solid var(--color-as-rgba-255-255-255-0-16);
        }

        .company-badge--success { background: var(--color-as-rgba-34-197-94-0-16); }
        .company-badge--warning { background: var(--color-as-rgba-245-158-11-0-16); }
        .company-badge--danger { background: var(--color-as-rgba-239-68-68-0-16); }

        .company-actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .company-actions .btn {
            border-radius: 0;
            padding-inline: 1rem;
            font-weight: 700;
        }

        .company-stats {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .company-stat {
            border-radius: 0;
            padding: 1rem 1.05rem;
            background: var(--color-as-rgba-255-255-255-0-12);
            border: 1px solid var(--color-as-rgba-255-255-255-0-12);
            backdrop-filter: blur(10px);
        }

        .company-stat__label {
            color: var(--color-as-rgba-255-255-255-0-76);
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .company-stat__value {
            margin-top: .35rem;
            font-size: 1.45rem;
            font-weight: 800;
        }

        .company-panel {
            border: 0;
            border-radius: 0;
            box-shadow: var(--company-shadow);
        }

        .company-tabs {
            border-bottom: 1px solid var(--company-border);
            gap: .75rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .3rem;
        }

        .company-tabs .nav-link {
            border: 0;
            white-space: nowrap;
            border-radius: 0;
            color: var(--color-as-475569);
            font-weight: 700;
            padding: .72rem 1rem;
            background: var(--color-as-f8fafc);
        }

        .company-tabs .nav-link.active {
            background: var(--company-primary);
            color: var(--color-white);
            box-shadow: 0 14px 24px var(--color-as-rgba-37-99-235-0-14);
        }

        .company-section-card {
            height: 100%;
            border: 1px solid var(--company-border);
            border-radius: 0;
            background: var(--color-white);
            box-shadow: 0 12px 30px var(--color-as-rgba-15-23-42-0-04);
        }

        .company-section-card__head {
            padding: 1.15rem 1.2rem .5rem;
        }

        .company-section-card__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--color-as-0f172a);
        }

        .company-section-card__subtitle {
            margin: .35rem 0 0;
            color: var(--color-as-64748b);
            font-size: .9rem;
            line-height: 1.6;
        }

        .company-section-card__body {
            padding: 1.2rem;
        }

        .company-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .summary-item {
            padding: 1rem;
            border-radius: 0;
            background: var(--color-as-f8fafc);
            border: 1px solid var(--color-as-rgba-148-163-184-0-14);
        }

        .summary-item__label {
            display: block;
            color: var(--color-as-64748b);
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .35rem;
        }

        .summary-item__value {
            color: var(--color-as-0f172a);
            font-weight: 700;
            line-height: 1.55;
        }

        .company-quick-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .quick-action-card {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            padding: 1rem;
            border-radius: 0;
            text-decoration: none;
            background: var(--color-as-f8fafc);
            border: 1px solid var(--color-as-rgba-148-163-184-0-14);
            color: inherit;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .quick-action-card:hover {
            transform: translateY(-2px);
            border-color: var(--color-as-rgba-37-99-235-0-24);
            box-shadow: 0 16px 28px var(--color-as-rgba-15-23-42-0-08);
        }

        .quick-action-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 0;
            background: var(--company-primary-soft);
            color: var(--company-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex: 0 0 auto;
        }

        .quick-action-card__title {
            margin: 0 0 .3rem;
            font-size: .95rem;
            font-weight: 800;
            color: var(--color-as-0f172a);
        }

        .quick-action-card__text {
            margin: 0;
            color: var(--color-as-64748b);
            font-size: .86rem;
            line-height: 1.55;
        }

        .company-doc-card {
            border: 1px solid var(--company-border);
            border-radius: 0;
            background: var(--color-white);
            padding: 1rem;
            box-shadow: 0 10px 24px var(--color-as-rgba-15-23-42-0-04);
        }

        .company-doc-card__preview {
            width: 100%;
            min-height: 180px;
            border-radius: 0;
            background: var(--color-as-f8fafc);
            border: 1px dashed var(--color-as-rgba-148-163-184-0-32);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-top: .9rem;
        }

        .company-doc-card__preview img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .company-doc-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .7rem;
        }

        .company-status-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 0;
            padding: .42rem .72rem;
            font-size: .8rem;
            font-weight: 700;
        }

        .company-status-chip--pending {
            background: var(--color-as-rgba-245-158-11-0-12);
            color: var(--color-as-b45309);
        }

        .company-status-chip--approved {
            background: var(--color-as-rgba-34-197-94-0-12);
            color: var(--color-as-15803d);
        }

        .company-status-chip--rejected {
            background: var(--color-as-rgba-239-68-68-0-12);
            color: var(--color-as-b91c1c);
        }

        .company-empty {
            border-radius: 0;
            padding: 1rem;
            text-align: center;
            background: var(--color-as-f8fafc);
            color: var(--color-as-64748b);
            border: 1px dashed var(--color-as-rgba-148-163-184-0-28);
        }

        .company-profile-upload {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .company-profile-upload__image {
            width: 110px;
            height: 110px;
            border-radius: 0;
            overflow: hidden;
            background: var(--color-as-f8fafc);
            border: 1px solid var(--color-as-rgba-148-163-184-0-18);
            flex: 0 0 auto;
        }

        .company-profile-upload__image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .company-placeholder-panel {
            border-radius: 0;
            padding: 1rem;
            background: var(--color-as-rgba-15-118-110-0-08);
            border: 1px solid var(--color-as-rgba-37-99-235-0-12);
        }

        .company-placeholder-panel strong {
            display: block;
            color: var(--color-as-0f172a);
            margin-bottom: .35rem;
        }

        .company-sticky-actions {
            position: sticky;
            bottom: 1rem;
            z-index: 6;
            margin-top: 1.25rem;
            border-radius: 0;
            padding: .95rem 1rem;
            background: var(--color-as-rgba-255-255-255-0-92);
            backdrop-filter: blur(12px);
            border: 1px solid var(--color-as-rgba-148-163-184-0-24);
            box-shadow: 0 16px 32px var(--color-as-rgba-15-23-42-0-1);
        }

        .company-sticky-actions__content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .company-sticky-actions__note {
            color: var(--color-as-64748b);
            font-size: .9rem;
        }

        @media (max-width: 1199px) {
            .company-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .company-hero__body,
            .company-section-card__body {
                padding: 1rem;
            }

            .company-actions {
                justify-content: flex-start;
                margin-top: 1rem;
            }

            .company-summary-grid,
            .company-quick-actions,
            .company-stats {
                grid-template-columns: 1fr;
            }

            .company-avatar {
                width: 76px;
                height: 76px;
                border-radius: 0;
            }
        }
    </style>
@endsection

@section('main')
    @php
        $isAdmin = LOGIN_USER_TYPE != 'company';
        $statusClasses = [
            'Active' => 'company-badge company-badge--success',
            'Pending' => 'company-badge company-badge--warning',
            'Inactive' => 'company-badge company-badge--danger',
        ];
        $statusClass = $statusClasses[$result->status] ?? 'company-badge';
        $countryName = optional(\App\Models\Country::find($result->country_id))->long_name ?? 'Not provided';
        $profileImage = $result->profile ?: url('images/user.jpeg');
        $profileStats = $profile_stats ?? ['total_drivers' => 0, 'active_trips' => 0, 'completed_trips' => 0, 'revenue_total' => null];
        $documentSummary = $document_summary ?? ['required' => 0, 'uploaded' => 0, 'approved' => 0, 'verified' => false];
        $fieldSupport = $company_field_support ?? ['registration_number' => false, 'business_type' => false, 'admin_notes' => false];
        $saasEnabled = function_exists('saas_dispatch_enabled') && saas_dispatch_enabled();
        $whiteLabelEnabled = function_exists('white_label_module_enabled') && white_label_module_enabled();
        $defaultCurrencySymbol = $default_currency->symbol ?? '$';
        $backUrl = $isAdmin ? url('admin/company') : url('company/dashboard');
        $driversUrl = url(LOGIN_USER_TYPE.'/driver');
        $tripsUrl = url(LOGIN_USER_TYPE.'/trips');
        $manageFareUrl = $isAdmin ? url('admin/manage_fare') : (Route::has('saasdispatch.self.fare-rules.index') ? route('saasdispatch.self.fare-rules.index') : '#');
        $saasSettingsUrl = null;
        if ($saasEnabled) {
            if ($isAdmin && Route::has('saasdispatch.companies.settings.edit')) {
                $saasSettingsUrl = route('saasdispatch.companies.settings.edit', $result->id);
            } elseif (!$isAdmin && Route::has('saasdispatch.self.settings.edit')) {
                $saasSettingsUrl = route('saasdispatch.self.settings.edit');
            }
        }
        $brandingSettingsUrl = $saasSettingsUrl;
        $saasSetting = $result->saas_setting;
    @endphp

    <div id="layout-wrapper"
         class="company-profile-shell"
         ng-controller="company_management"
         ng-init='login_user_type = "{{ LOGIN_USER_TYPE }}"; company_doc=""; errors = @json($errors->getMessages());'>

        {!! Form::open(['url' => LOGIN_USER_TYPE.'/edit_company/'.$result->id, 'class' => 'needs-validation', 'files' => true, 'id' => 'company_form']) !!}
        {!! Form::hidden('user_id', $result->id, ['id' => 'user_id']) !!}
        {!! Form::hidden('country_id', old('country_code', $result->country_id), ['id' => 'country_id']) !!}
        {!! Form::hidden('user_type', 'Company') !!}

        <div class="card company-hero mb-4">
            <div class="company-hero__body">
                <div class="row g-4 align-items-start">
                    <div class="col-xl-8">
                        <div class="company-hero__identity">
                            <div class="company-avatar">
                                @if($result->profile)
                                    <img src="{{ $profileImage }}" alt="{{ $result->name }}">
                                @else
                                    {{ strtoupper(substr($result->name ?? 'CO', 0, 2)) }}
                                @endif
                            </div>

                            <div class="company-hero__meta">
                                <h2>{{ $result->name ?: 'Company profile' }}</h2>
                                <div class="company-hero__sub">
                                    <span><i class="bi bi-envelope me-1"></i>{{ $result->email ?: 'Not provided' }}</span>
                                    <span><i class="bi bi-telephone me-1"></i>{{ $result->country_code ? '+' . $result->country_code : '' }} {{ $result->env_mobile_number ?: 'Not provided' }}</span>
                                    <span><i class="bi bi-geo-alt me-1"></i>{{ $countryName }}</span>
                                </div>

                                <div class="company-badges">
                                    <span class="{{ $statusClass }}">
                                        <i class="bi bi-record-circle"></i>
                                        {{ $result->status ?: 'Pending' }}
                                    </span>
                                    <span class="company-badge {{ $documentSummary['verified'] ? 'company-badge--success' : 'company-badge--warning' }}">
                                        <i class="bi {{ $documentSummary['verified'] ? 'bi-patch-check-fill' : 'bi-shield-exclamation' }}"></i>
                                        {{ $documentSummary['verified'] ? 'Documents Verified' : 'Verification Pending' }}
                                    </span>
                                    <span class="company-badge">
                                        <i class="bi bi-hash"></i>
                                        Company ID #{{ $result->id }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="company-actions">
                            <button type="submit" class="btn btn-light text-dark">
                                <i class="bi bi-check2-circle me-1"></i>Save Changes
                            </button>
                            <a href="{{ $backUrl }}" class="btn btn-outline-light">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                            <a href="{{ $tripsUrl }}" class="btn btn-outline-light">
                                <i class="bi bi-car-front me-1"></i>View Trips
                            </a>
                            <a href="{{ $driversUrl }}" class="btn btn-outline-light">
                                <i class="bi bi-people me-1"></i>View Drivers
                            </a>
                            @if($isAdmin)
                                <button type="button"
                                        class="btn btn-outline-light js-company-status-toggle"
                                        data-target-status="{{ $result->status === 'Active' ? 'Inactive' : 'Active' }}">
                                    <i class="bi {{ $result->status === 'Active' ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
                                    {{ $result->status === 'Active' ? 'Suspend' : 'Activate' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="company-stats">
                    <div class="company-stat">
                        <div class="company-stat__label">Total Drivers</div>
                        <div class="company-stat__value">{{ $profileStats['total_drivers'] }}</div>
                    </div>
                    <div class="company-stat">
                        <div class="company-stat__label">Active Trips</div>
                        <div class="company-stat__value">{{ $profileStats['active_trips'] }}</div>
                    </div>
                    <div class="company-stat">
                        <div class="company-stat__label">Completed Trips</div>
                        <div class="company-stat__value">{{ $profileStats['completed_trips'] }}</div>
                    </div>
                    <div class="company-stat">
                        <div class="company-stat__label">{{ $isAdmin ? 'Revenue Snapshot' : 'Wallet / Revenue' }}</div>
                        <div class="company-stat__value">
                            {{ $profileStats['revenue_total'] !== null ? $defaultCurrencySymbol . number_format((float) $profileStats['revenue_total'], 2) : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card company-panel">
            <div class="card-body p-3 p-lg-4">
                <ul class="nav nav-pills company-tabs mb-4" id="company-profile-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#company-tab-overview" type="button" role="tab">Overview</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-business" type="button" role="tab">Business Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-contact" type="button" role="tab">Contact and Address</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-documents" type="button" role="tab">Documents and Compliance</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-billing" type="button" role="tab">Commission and Billing</button>
                    </li>
                    @if($saasEnabled)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-saas" type="button" role="tab">SaaS Settings</button>
                        </li>
                    @endif
                    @if($whiteLabelEnabled)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-branding" type="button" role="tab">Branding</button>
                        </li>
                    @endif
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#company-tab-security" type="button" role="tab">Security</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="company-tab-overview" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Operator Snapshot</h3>
                                        <p class="company-section-card__subtitle">A quick summary of the operator account, verification state, and profile readiness.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="company-summary-grid">
                                            <div class="summary-item">
                                                <span class="summary-item__label">Company Name</span>
                                                <div class="summary-item__value">{{ $result->name ?: 'Not provided' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Email</span>
                                                <div class="summary-item__value">{{ $result->email ?: 'Not provided' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Phone</span>
                                                <div class="summary-item__value">{{ $result->country_code ? '+' . $result->country_code : '' }} {{ $result->env_mobile_number ?: 'Not provided' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Country</span>
                                                <div class="summary-item__value">{{ $countryName }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Address Summary</span>
                                                <div class="summary-item__value">{{ $result->address ?: 'Not provided' }}{{ $result->city ? ', '.$result->city : '' }}{{ $result->state ? ', '.$result->state : '' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Commission Rate</span>
                                                <div class="summary-item__value">{{ $result->company_commission !== null ? number_format((float) $result->company_commission, 2) . '%' : 'Not provided' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Verification State</span>
                                                <div class="summary-item__value">{{ $documentSummary['verified'] ? 'Verified' : 'Pending verification' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Account Created</span>
                                                <div class="summary-item__value">{{ optional($result->created_at)->format('d M Y, H:i') ?: 'Not available' }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <span class="summary-item__label">Last Updated</span>
                                                <div class="summary-item__value">{{ optional($result->updated_at)->format('d M Y, H:i') ?: 'Not available' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Quick Actions</h3>
                                        <p class="company-section-card__subtitle">Jump to the most common operational areas without leaving the profile center.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="company-quick-actions">
                                            <a href="{{ $driversUrl }}" class="quick-action-card">
                                                <span class="quick-action-card__icon"><i class="bi bi-people"></i></span>
                                                <span>
                                                    <span class="quick-action-card__title">Manage Drivers</span>
                                                    <span class="quick-action-card__text">Open the operator driver workspace.</span>
                                                </span>
                                            </a>
                                            <a href="{{ $tripsUrl }}" class="quick-action-card">
                                                <span class="quick-action-card__icon"><i class="bi bi-car-front"></i></span>
                                                <span>
                                                    <span class="quick-action-card__title">Manage Trips</span>
                                                    <span class="quick-action-card__text">Review live and historical trip operations.</span>
                                                </span>
                                            </a>
                                            <a href="{{ $manageFareUrl }}" class="quick-action-card">
                                                <span class="quick-action-card__icon"><i class="bi bi-cash-stack"></i></span>
                                                <span>
                                                    <span class="quick-action-card__title">Manage Fare</span>
                                                    <span class="quick-action-card__text">Review pricing, commission, and fare setup.</span>
                                                </span>
                                            </a>
                                            <a href="#company-tab-documents" class="quick-action-card js-tab-shortcut" data-tab-target="#company-tab-documents">
                                                <span class="quick-action-card__icon"><i class="bi bi-file-earmark-check"></i></span>
                                                <span>
                                                    <span class="quick-action-card__title">Manage Documents</span>
                                                    <span class="quick-action-card__text">Update uploads, status, and compliance dates.</span>
                                                </span>
                                            </a>
                                            @if($saasEnabled && $saasSettingsUrl)
                                                <a href="{{ $saasSettingsUrl }}" class="quick-action-card">
                                                    <span class="quick-action-card__icon"><i class="bi bi-grid"></i></span>
                                                    <span>
                                                        <span class="quick-action-card__title">SaaS Settings</span>
                                                        <span class="quick-action-card__text">Review dispatch mode and company-owned resources.</span>
                                                    </span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="company-tab-business" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Business Identity</h3>
                                        <p class="company-section-card__subtitle">Core operator account information used across dispatch, billing, and company settings.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="mb-3">
                                            <label class="form-label" for="input_name">{{ __('messages.admin.manage_company_page.form.name') }} <em class="text-danger">*</em></label>
                                            {!! Form::text('name', old('name', $result->name), ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Company name']) !!}
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="input_vat_number">{{ __('messages.admin.manage_company_page.form.vat_number') }}</label>
                                            {!! Form::text('vat_number', old('vat_number', $result->vat_number), ['class' => 'form-control', 'id' => 'input_vat_number', 'placeholder' => 'VAT number']) !!}
                                            <span class="text-danger">{{ $errors->first('vat_number') }}</span>
                                        </div>

                                        @if($fieldSupport['registration_number'])
                                            <div class="mb-3">
                                                <label class="form-label" for="input_registration_number">{{ __('messages.registration_number') }}</label>
                                                {!! Form::text('registration_number', old('registration_number', $result->registration_number), ['class' => 'form-control', 'id' => 'input_registration_number', 'placeholder' => 'Registration number']) !!}
                                            </div>
                                        @endif

                                        @if($fieldSupport['business_type'])
                                            <div class="mb-3">
                                                <label class="form-label" for="input_business_type">Business Type</label>
                                                {!! Form::text('business_type', old('business_type', $result->business_type), ['class' => 'form-control', 'id' => 'input_business_type', 'placeholder' => 'Business type']) !!}
                                            </div>
                                        @endif

                                        @if($fieldSupport['admin_notes'] && $isAdmin)
                                            <div class="mb-0">
                                                <label class="form-label" for="input_admin_notes">Admin Notes</label>
                                                {!! Form::textarea('admin_notes', old('admin_notes', $result->admin_notes), ['class' => 'form-control', 'rows' => 4, 'id' => 'input_admin_notes', 'placeholder' => 'Internal account notes']) !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Operations Controls</h3>
                                        <p class="company-section-card__subtitle">Admin-only governance controls stay editable here. Company users see them as read-only account metadata.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="mb-3">
                                            <label class="form-label" for="input_profile">{{ __('messages.admin.manage_company_page.form.profile') }}</label>
                                            <div class="company-profile-upload">
                                                <div class="company-profile-upload__image">
                                                    <img src="{{ $profileImage }}" alt="{{ $result->name }}">
                                                </div>
                                                <div class="flex-grow-1">
                                                    {!! Form::file('profile', ['class' => 'form-control', 'id' => 'input_profile', 'accept' => 'image/*']) !!}
                                                    <span class="text-danger">{{ $errors->first('profile') }}</span>
                                                    <div class="form-text">Upload a company logo or operator avatar. PNG and JPG are supported.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="input_status">{{ __('messages.admin.manage_company_page.form.status') }}</label>
                                            @if($isAdmin)
                                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive', 'Pending' => 'Pending'], old('status', $result->status), ['class' => 'form-select', 'id' => 'input_status']) !!}
                                            @else
                                                <input type="text" class="form-control" value="{{ $result->status }}" readonly>
                                            @endif
                                            <span class="text-danger">{{ $errors->first('status') }}</span>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label" for="input_service_fee">{{ __('messages.admin.manage_company_page.form.company_commission') }} <em class="text-danger">*</em></label>
                                            <div class="input-group">
                                                {!! Form::text('company_commission', old('company_commission', $result->company_commission), ['class' => 'form-control', 'id' => 'input_service_fee', 'placeholder' => 'Company commission', 'readonly' => $isAdmin ? false : true]) !!}
                                                <span class="input-group-text bg-light">%</span>
                                            </div>
                                            <span class="text-danger">{{ $errors->first('company_commission') }}</span>
                                            @if(!$isAdmin)
                                                <div class="form-text">Commission is managed by the platform administrator and shown here for visibility.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="company-tab-contact" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Primary Contact</h3>
                                        <p class="company-section-card__subtitle">The main contact details used for account communication, support, and operational notifications.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="mb-3">
                                            <label class="form-label" for="input_email">{{ __('messages.admin.manage_company_page.form.email') }} <em class="text-danger">*</em></label>
                                            {!! Form::text('email', old('email', $result->email), ['class' => 'form-control', 'id' => 'input_email', 'placeholder' => 'Email address']) !!}
                                            <span class="text-danger">{{ $errors->first('email') }}</span>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="input_country_code">{{ __('messages.admin.manage_company_page.form.country_code') }} <em class="text-danger">*</em></label>
                                            {!! Form::select('country_code', $country_code_option, old('country_code', $result->country_id), ['class' => 'form-select js-company-country', 'id' => 'input_country_code', 'placeholder' => 'Select country']) !!}
                                            <span class="text-danger">{{ $errors->first('country_code') }}</span>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label" for="mobile_number">{{ __('messages.admin.manage_company_page.form.mobile_number') }}</label>
                                            {!! Form::text('mobile_number', old('mobile_number', $result->env_mobile_number), ['class' => 'form-control', 'id' => 'mobile_number', 'placeholder' => 'Mobile number']) !!}
                                            <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Address and Territory</h3>
                                        <p class="company-section-card__subtitle">Physical business location and address values used on invoices, profile summaries, and compliance records.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="mb-3">
                                            <label class="form-label" for="address_line">{{ __('messages.admin.manage_company_page.form.address_line') }} <em class="text-danger">*</em></label>
                                            {!! Form::text('address_line', old('address_line', $result->address), ['class' => 'form-control', 'id' => 'address_line', 'placeholder' => 'Address line']) !!}
                                            <span class="text-danger">{{ $errors->first('address_line') }}</span>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="city">{{ __('messages.admin.manage_company_page.form.city') }}</label>
                                                {!! Form::text('city', old('city', $result->city), ['class' => 'form-control', 'id' => 'city', 'placeholder' => 'City']) !!}
                                                <span class="text-danger">{{ $errors->first('city') }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="state">{{ __('messages.admin.manage_company_page.form.state') }}</label>
                                                {!! Form::text('state', old('state', $result->state), ['class' => 'form-control', 'id' => 'state', 'placeholder' => 'State / County']) !!}
                                                <span class="text-danger">{{ $errors->first('state') }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="postal_code">{{ __('messages.admin.manage_company_page.form.postal_code') }} <em class="text-danger">*</em></label>
                                                {!! Form::text('postal_code', old('postal_code', $result->postal_code), ['class' => 'form-control', 'id' => 'postal_code', 'placeholder' => 'Postal code']) !!}
                                                <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Country</label>
                                                <input type="text" class="form-control" value="{{ $countryName }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="company-tab-documents" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Compliance Documents</h3>
                                        <p class="company-section-card__subtitle">Upload, replace, review, and approve company compliance documents without breaking the current dynamic document flow.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-4">
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Required Documents</span>
                                                    <div class="summary-item__value">{{ $documentSummary['required'] ?: 'No requirement configured' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Uploaded</span>
                                                    <div class="summary-item__value">{{ $documentSummary['uploaded'] }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Approval State</span>
                                                    <div class="summary-item__value">{{ $documentSummary['verified'] ? 'Verified' : 'Pending verification' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="loading d-none" id="company_loading"></div>

                                        <div class="row g-4" ng-if="company_doc && company_doc.length">
                                            <div class="col-xl-6" ng-repeat="doc in company_doc" ng-cloak>
                                                <div class="company-doc-card">
                                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                        <div>
                                                            <h5 class="mb-1">@{{ doc.document_name }}</h5>
                                                            <div class="company-doc-badges">
                                                                <span class="company-status-chip company-status-chip--pending">
                                                                    <i class="bi bi-file-earmark"></i>
                                                                    Required
                                                                </span>
                                                                <span class="company-status-chip" ng-if="doc.expiry_required == '1'">
                                                                    <i class="bi bi-calendar-event"></i>
                                                                    Expiry required
                                                                </span>
                                                                <span class="company-status-chip"
                                                                      ng-class="{
                                                                        'company-status-chip--pending': doc.status == 0,
                                                                        'company-status-chip--approved': doc.status == 1,
                                                                        'company-status-chip--rejected': doc.status == 2
                                                                      }">
                                                                    <i class="bi"
                                                                       ng-class="{
                                                                        'bi-hourglass-split': doc.status == 0,
                                                                        'bi-patch-check-fill': doc.status == 1,
                                                                        'bi-x-circle-fill': doc.status == 2
                                                                       }"></i>
                                                                    <span ng-if="doc.status == 0">{{ __('messages.blade_static.pending_96f608c1') }}</span>
                                                                    <span ng-if="doc.status == 1">{{ __('messages.blade_static.approved_41b81eb8') }}</span>
                                                                    <span ng-if="doc.status == 2">{{ __('messages.blade_static.rejected_27eeb7a2') }}</span>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div ng-if="doc.document">
                                                            <a ng-href="@{{ doc.document }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-box-arrow-up-right me-1"></i>View File
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="company-doc-card__preview">
                                                        <a ng-if="doc.document" ng-href="@{{ doc.document }}" target="_blank" class="w-100 h-100">
                                                            <img ng-src="@{{ doc.document }}" alt="@{{ doc.document_name }}">
                                                        </a>
                                                        <div class="company-empty w-100" ng-if="!doc.document">
                                                            <i class="bi bi-file-earmark-arrow-up fs-2 d-block mb-2"></i>
                                                            No document uploaded
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="form-label">Upload or Replace File <em class="text-danger">*</em></label>
                                                        <input type="file" name="file_@{{ doc.id }}" class="form-control">
                                                        <span class="text-danger">@{{ errors['file_'+doc.id][0] }}</span>
                                                    </div>

                                                    <div class="row g-3 mt-1">
                                                        <div class="col-md-6" ng-if="doc.expiry_required == '1'">
                                                            <label class="form-label">{{ __('messages.blade_static.expire_date_16484486') }} <em class="text-danger">*</em></label>
                                                            <input type="text"
                                                                   name="expired_date_@{{ doc.id }}"
                                                                   value="@{{ doc.expired_date }}"
                                                                   class="form-control document_expired air-datepicker"
                                                                   placeholder="{{ __('messages.blade_static.expire_date_b5b8f80b') }}"
                                                                   autocomplete="off">
                                                            <span class="text-danger">@{{ errors['expired_date_'+doc.id][0] }}</span>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label">Approval Status</label>
                                                            @if($isAdmin)
                                                                <select class="form-select" ng-attr-name="@{{ doc.doc_name }}_status">
                                                                    <option value="0" ng-selected="doc.status == 0">{{ __('messages.blade_static.pending_96f608c1') }}</option>
                                                                    <option value="1" ng-selected="doc.status == 1">{{ __('messages.blade_static.approved_41b81eb8') }}</option>
                                                                    <option value="2" ng-selected="doc.status == 2">{{ __('messages.blade_static.rejected_27eeb7a2') }}</option>
                                                                </select>
                                                            @else
                                                                <input type="hidden" ng-attr-name="@{{ doc.doc_name }}_status" ng-value="doc.status || 0">
                                                                <div class="form-control d-flex align-items-center">
                                                                    <span ng-if="doc.status == 0">{{ __('messages.blade_static.pending_96f608c1') }}</span>
                                                                    <span ng-if="doc.status == 1">{{ __('messages.blade_static.approved_41b81eb8') }}</span>
                                                                    <span ng-if="doc.status == 2">{{ __('messages.blade_static.rejected_27eeb7a2') }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="company-empty" ng-if="!company_doc || !company_doc.length">
                                            No compliance documents are configured for the selected country yet.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="company-tab-billing" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Commission and Settlement</h3>
                                        <p class="company-section-card__subtitle">Commercial settings and visibility into placeholder financial summaries for the company account.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="mb-3">
                                            <label class="form-label">Company Commission</label>
                                            <div class="input-group">
                                                {!! Form::text('company_commission_display', old('company_commission', $result->company_commission), ['class' => 'form-control', 'readonly' => true]) !!}
                                                <span class="input-group-text bg-light">%</span>
                                            </div>
                                        </div>

                                        <div class="company-placeholder-panel mb-3">
                                            <strong>Billing Status</strong>
                                            <span>{{ $isAdmin ? 'Ready for billing workflow integration.' : 'No billing data available yet.' }}</span>
                                        </div>

                                        <div class="company-placeholder-panel mb-3">
                                            <strong>Payment Mode</strong>
                                            <span>Future-ready placeholder for settlement and payout preferences.</span>
                                        </div>

                                        <div class="company-placeholder-panel">
                                            <strong>Wallet / Settlement Summary</strong>
                                            <span>{{ $profileStats['revenue_total'] !== null ? 'Revenue snapshot available in the header above.' : 'No wallet or settlement totals available.' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Operator Revenue Snapshot</h3>
                                        <p class="company-section-card__subtitle">A clean place for current and future account commercial metrics without changing existing save logic.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="summary-item mb-3">
                                            <span class="summary-item__label">Company Balance Placeholder</span>
                                            <div class="summary-item__value">{{ $profileStats['revenue_total'] !== null ? $defaultCurrencySymbol . number_format((float) $profileStats['revenue_total'], 2) : 'No billing data available' }}</div>
                                        </div>
                                        <div class="summary-item mb-3">
                                            <span class="summary-item__label">Subscription Status</span>
                                            <div class="summary-item__value">{{ $saasEnabled ? 'SaaS-ready' : 'Standard platform account' }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-item__label">Payment Controls</span>
                                            <div class="summary-item__value">{{ $isAdmin ? 'Admin-managed commercial controls.' : 'Read-only visibility for company users.' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($saasEnabled)
                        <div class="tab-pane fade" id="company-tab-saas" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="company-section-card">
                                        <div class="company-section-card__head">
                                            <h3 class="company-section-card__title">SaaS Dispatch Controls</h3>
                                            <p class="company-section-card__subtitle">Show dispatch ownership and company-specific feature availability only when the SaaS Dispatch module is enabled.</p>
                                        </div>
                                        <div class="company-section-card__body">
                                            <div class="company-summary-grid">
                                                <div class="summary-item">
                                                    <span class="summary-item__label">SaaS Enabled</span>
                                                    <div class="summary-item__value">{{ optional($saasSetting)->is_enabled ? 'Enabled' : 'Disabled' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Dispatch Mode</span>
                                                    <div class="summary-item__value">{{ optional($saasSetting)->dispatch_mode ?: 'global' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Own Fares</span>
                                                    <div class="summary-item__value">{{ optional($saasSetting)->use_own_fares ? 'Enabled' : 'Disabled' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Own Vehicle Types</span>
                                                    <div class="summary-item__value">{{ optional($saasSetting)->use_own_vehicle_types ? 'Enabled' : 'Disabled' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Own Service Areas</span>
                                                    <div class="summary-item__value">{{ optional($saasSetting)->use_own_service_areas ? 'Enabled' : 'Disabled' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Own Dispatch Settings</span>
                                                    <div class="summary-item__value">{{ optional($saasSetting)->use_own_dispatch_settings ? 'Enabled' : 'Disabled' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-5">
                                    <div class="company-section-card">
                                        <div class="company-section-card__head">
                                            <h3 class="company-section-card__title">Module Access</h3>
                                            <p class="company-section-card__subtitle">Open the dedicated module screens for full SaaS company configuration when access is available.</p>
                                        </div>
                                        <div class="company-section-card__body">
                                            <div class="company-placeholder-panel mb-3">
                                                <strong>Dispatch Settings</strong>
                                                <span>Global, SaaS, or hybrid mode can be managed from the dedicated SaaS module screens.</span>
                                            </div>
                                            @if($saasSettingsUrl)
                                                <a href="{{ $saasSettingsUrl }}" class="btn btn-primary w-100">
                                                    <i class="bi bi-arrow-up-right-circle me-1"></i>
                                                    {{ $isAdmin ? 'Open Company SaaS Settings' : 'Open My SaaS Settings' }}
                                                </a>
                                            @else
                                                <div class="company-empty">No SaaS settings route is currently available.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($whiteLabelEnabled)
                        <div class="tab-pane fade" id="company-tab-branding" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="company-section-card">
                                        <div class="company-section-card__head">
                                            <h3 class="company-section-card__title">Branding and White Label</h3>
                                            <p class="company-section-card__subtitle">Surface brand identity settings only when the white-label module is enabled.</p>
                                        </div>
                                        <div class="company-section-card__body">
                                            <div class="company-summary-grid mb-4">
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Brand Name</span>
                                                    <div class="summary-item__value">{{ $result->name ?: 'Not provided' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Support Email</span>
                                                    <div class="summary-item__value">{{ $result->email ?: 'Not provided' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Support Phone</span>
                                                    <div class="summary-item__value">{{ $result->env_mobile_number ?: 'Not provided' }}</div>
                                                </div>
                                                <div class="summary-item">
                                                    <span class="summary-item__label">Domain / Subdomain</span>
                                                    <div class="summary-item__value">No domain data available</div>
                                                </div>
                                            </div>
                                            @if($brandingSettingsUrl)
                                                <a href="{{ $brandingSettingsUrl }}" class="btn btn-outline-primary">
                                                    <i class="bi bi-palette me-1"></i>Open Branding Settings
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="tab-pane fade" id="company-tab-security" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Password and Access</h3>
                                        <p class="company-section-card__subtitle">Update the company account password without exposing the current value. Leave blank to keep the existing password.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="mb-3">
                                            <label class="form-label" for="input_password">{{ __('messages.admin.manage_company_page.form.password') }}</label>
                                            {!! Form::password('password', ['class' => 'form-control', 'id' => 'input_password', 'placeholder' => 'Leave blank to keep current password']) !!}
                                            <span class="text-danger">{{ $errors->first('password') }}</span>
                                        </div>
                                        <div class="company-placeholder-panel">
                                            <strong>{{ $isAdmin ? 'Admin reset capability' : 'Self-service password update' }}</strong>
                                            <span>{{ $isAdmin ? 'Admins can rotate the company password from this tab when needed.' : 'Use this tab to update the account password without changing other profile data.' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="company-section-card">
                                    <div class="company-section-card__head">
                                        <h3 class="company-section-card__title">Security Notes</h3>
                                        <p class="company-section-card__subtitle">Helpful reminders for profile administrators and company operators.</p>
                                    </div>
                                    <div class="company-section-card__body">
                                        <div class="company-placeholder-panel mb-3">
                                            <strong>Password Hygiene</strong>
                                            <span>Choose a unique password for the operator account and rotate it when the responsible personnel changes.</span>
                                        </div>
                                        <div class="company-placeholder-panel">
                                            <strong>Save Behaviour</strong>
                                            <span>The entire profile center still submits through the same company update endpoint you already use today.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="company-sticky-actions">
                    <div class="company-sticky-actions__content">
                        <div class="company-sticky-actions__note">
                            <strong class="d-block text-dark">Ready to save</strong>
                            Keep using the same backend validation, document processing, and company update logic while working through the new profile center.
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ $backUrl }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var countrySelect = document.querySelector('.js-company-country');
            if (countrySelect) {
                new Choices(countrySelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: ''
                });
            }

            document.querySelectorAll('.js-tab-shortcut').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    var target = link.getAttribute('data-tab-target');
                    var tabButton = document.querySelector('[data-bs-target="' + target + '"]');
                    if (tabButton && window.bootstrap && bootstrap.Tab) {
                        bootstrap.Tab.getOrCreateInstance(tabButton).show();
                    }
                });
            });

            document.querySelectorAll('.js-company-status-toggle').forEach(function (button) {
                button.addEventListener('click', function () {
                    var targetStatus = button.getAttribute('data-target-status');
                    var select = document.getElementById('input_status');
                    if (!select) {
                        return;
                    }

                    select.value = targetStatus;
                    var businessTab = document.querySelector('[data-bs-target="#company-tab-business"]');
                    if (businessTab && window.bootstrap && bootstrap.Tab) {
                        bootstrap.Tab.getOrCreateInstance(businessTab).show();
                    }
                });
            });
        });
    </script>
@endsection
