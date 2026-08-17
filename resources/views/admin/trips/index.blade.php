@extends('admin.template')

@section('title-sub', 'Trip Operations Center')
@section('pagetitle', 'Trip Operations Center')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="{{ url('css/buttons.dataTables.css') }}">
    <style>
        .trip-ops-shell {
            --trip-ops-shadow: 0 20px 45px var(--color-as-rgba-15-23-42-0-08);
            --trip-ops-border: var(--color-as-rgba-148-163-184-0-22);
        }

        .trip-ops-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 0;
            color: var(--color-white);
            background: var(--color-as-0f172a);
            box-shadow: var(--trip-ops-shadow);
        }

        .trip-ops-hero__body {
            position: relative;
            z-index: 1;
            padding: 1.6rem;
        }

        .trip-ops-hero__title {
            margin: 0 0 .4rem;
            font-size: clamp(1.75rem, 3vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .trip-ops-hero__subtitle {
            margin: 0;
            color: var(--color-as-rgba-255-255-255-0-84);
            max-width: 760px;
            line-height: 1.65;
        }

        .trip-ops-actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .trip-ops-actions .btn {
            border-radius: 0;
            font-weight: 700;
        }

        .trip-kpi-card,
        .trip-panel,
        .trip-analytics-card {
            border: 0;
            border-radius: 0;
            box-shadow: var(--trip-ops-shadow);
        }

        .trip-kpi-card {
            height: 100%;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .trip-kpi-card:hover {
            transform: translateY(-3px);
        }

        .trip-kpi-card .card-body {
            padding: 1.15rem;
        }

        .trip-kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .trip-kpi-label {
            color: var(--color-as-64748b);
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .trip-kpi-value {
            margin-top: .4rem;
            color: var(--color-as-0f172a);
            font-size: 1.55rem;
            font-weight: 800;
        }

        .trip-kpi-change {
            margin-top: .55rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .84rem;
            font-weight: 700;
        }

        .trip-alert-strip {
            display: flex;
            gap: .9rem;
            overflow-x: auto;
            padding-bottom: .2rem;
        }

        .trip-alert-card {
            min-width: 240px;
            border: 1px solid var(--trip-ops-border);
            border-radius: 0;
            background: var(--color-white);
            padding: 1rem;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 12px 28px var(--color-as-rgba-15-23-42-0-05);
        }

        .trip-filter-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 1rem;
        }

        .trip-quick-filters {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .trip-quick-filters .btn {
            border-radius: 0;
        }

        .trip-view-switcher {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .trip-view-switcher .btn {
            border-radius: 0;
        }

        .trip-ops-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            color: var(--color-as-64748b);
            font-size: .9rem;
        }

        .trip-ops-live-dot {
            width: 10px;
            height: 10px;
            border-radius: 0;
            background: var(--color-as-22c55e);
            display: inline-block;
            box-shadow: 0 0 0 0 var(--color-as-rgba-34-197-94-0-6);
            animation: pulse-live 1.6s infinite;
        }

        @keyframes pulse-live {
            0% { box-shadow: 0 0 0 0 var(--color-as-rgba-34-197-94-0-55); }
            100% { box-shadow: 0 0 0 14px var(--color-as-rgba-34-197-94-0); }
        }

        .trip-card-view {
            display: none;
        }

        .trip-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .trip-mini-card {
            border: 1px solid var(--trip-ops-border);
            border-radius: 0;
            padding: 1rem;
            background: var(--color-white);
            box-shadow: 0 12px 28px var(--color-as-rgba-15-23-42-0-05);
        }

        .trip-placeholder-view {
            display: none;
            border: 1px dashed var(--color-as-rgba-148-163-184-0-28);
            border-radius: 0;
            padding: 2rem;
            text-align: center;
            color: var(--color-as-64748b);
            background: var(--color-as-f8fafc);
        }

        .trip-map-shell {
            display: none;
            border: 1px solid var(--color-as-rgba-148-163-184-0-18);
            border-radius: 0;
            overflow: hidden;
            background: var(--color-white);
            box-shadow: 0 12px 28px var(--color-as-rgba-15-23-42-0-05);
        }

        .trip-map-shell.active {
            display: block;
        }

        .trip-map-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--color-as-rgba-148-163-184-0-18);
        }

        .trip-map-metrics {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            color: var(--color-as-64748b);
            font-size: .9rem;
        }

        .trip-map-canvas {
            width: 100%;
            min-height: 560px;
            background: var(--color-as-rgba-15-118-110-0-06);
        }

        .trip-map-empty {
            padding: 1rem 1.2rem 1.25rem;
            color: var(--color-as-64748b);
        }

        .trip-timeline-view-shell {
            display: none;
        }

        .trip-timeline-view-shell.active {
            display: block;
        }

        .trip-timeline-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .trip-timeline-card {
            border: 1px solid var(--trip-ops-border);
            border-radius: 0;
            padding: 1.1rem;
            background: var(--color-white);
            box-shadow: 0 12px 28px var(--color-as-rgba-15-23-42-0-05);
        }

        .trip-timeline-card__meta {
            color: var(--color-as-64748b);
            font-size: .88rem;
        }

        .trip-empty-state {
            display: none;
            border-radius: 0;
            padding: 2rem;
            text-align: center;
            background: var(--color-as-rgba-15-118-110-0-07);
            border: 1px dashed var(--color-as-rgba-37-99-235-0-24);
        }

        .trip-analytics-card .card-body {
            padding: 1.1rem;
        }

        .trip-list-mini {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .trip-list-mini li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .7rem 0;
            border-bottom: 1px solid var(--color-as-rgba-148-163-184-0-14);
        }

        .trip-list-mini li:last-child {
            border-bottom: 0;
        }

        .trip-offcanvas-section + .trip-offcanvas-section {
            margin-top: 1.25rem;
        }

        .trip-timeline {
            list-style: none;
            margin: 0;
            padding: 0 0 0 1rem;
            position: relative;
        }

        .trip-timeline::before {
            content: '';
            position: absolute;
            left: .24rem;
            top: .15rem;
            bottom: .15rem;
            width: 2px;
            background: var(--color-as-rgba-148-163-184-0-25);
        }

        .trip-timeline li {
            position: relative;
            padding-left: 1rem;
            margin-bottom: .95rem;
        }

        .trip-timeline li::before {
            content: '';
            position: absolute;
            left: -.02rem;
            top: .28rem;
            width: .65rem;
            height: .65rem;
            border-radius: 0;
            background: var(--color-info);
        }

        .table.trip-compact-table > :not(caption) > * > * {
            padding-top: .45rem;
            padding-bottom: .45rem;
            font-size: .9rem;
        }

        @media (max-width: 1399px) {
            .trip-filter-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .trip-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .trip-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .trip-card-grid {
                grid-template-columns: 1fr;
            }

            .trip-timeline-grid {
                grid-template-columns: 1fr;
            }

            .trip-ops-actions {
                justify-content: flex-start;
                margin-top: 1rem;
            }
        }

        @media (max-width: 767px) {
            .trip-filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('main')
    @php
        $filterOptions = $filterOptions ?? [];
        $tripSummary = $tripSummary ?? [];
        $tripAlerts = collect($tripAlerts ?? [])->filter(fn ($item) => ($item['count'] ?? 0) > 0)->values();
        $tripAnalytics = $tripAnalytics ?? [];
        $schemaSupport = $schemaSupport ?? [];
        $manualBookingUrl = url(LOGIN_USER_TYPE.'/manual_booking');
        $laterBookingUrl = url(LOGIN_USER_TYPE.'/later_booking');
        $paymentsUrl = url(LOGIN_USER_TYPE.'/payments');
        $ratingsUrl = url(LOGIN_USER_TYPE.'/rating');
        $requestsUrl = url(LOGIN_USER_TYPE.'/request');
        $reportsUrl = $reportsUrl ?? url(LOGIN_USER_TYPE.'/statements/overall');
        $exportFrom = $fromDate ?: now()->startOfMonth()->toDateString();
        $exportTo = $toDate ?: now()->toDateString();
    @endphp

    <div id="layout-wrapper" class="trip-ops-shell">
        <div class="card trip-ops-hero mb-4">
            <div class="trip-ops-hero__body">
                <div class="row g-4 align-items-start">
                    <div class="col-xl-7">
                        <h1 class="trip-ops-hero__title">Trip Operations Center</h1>
                        <p class="trip-ops-hero__subtitle">Monitor, filter, investigate, and manage all operator trips in real time.</p>
                    </div>
                    <div class="col-xl-5">
                        <div class="trip-ops-actions">
                            <a href="{{ $manualBookingUrl }}" class="btn btn-light text-dark">
                                <i class="bi bi-plus-circle me-1"></i>New Manual Booking
                            </a>
                            <a href="{{ $laterBookingUrl }}" class="btn btn-outline-light">
                                <i class="bi bi-calendar2-plus me-1"></i>Scheduled Booking
                            </a>
                            <a href="{{ url(LOGIN_USER_TYPE.'/trips/export/'.$exportFrom.'/'.$exportTo) }}" class="btn btn-outline-light">
                                <i class="bi bi-download me-1"></i>Export
                            </a>
                            <button type="button" class="btn btn-outline-light js-refresh-trips">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                            <button type="button" class="btn btn-outline-light js-live-toggle" data-live="off">
                                <i class="bi bi-broadcast-pin me-1"></i>Live Mode
                            </button>
                            @if($mapViewAvailable)
                                <button type="button" class="btn btn-outline-light js-switch-view" data-view="map">
                                    <i class="bi bi-map me-1"></i>Map View
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach($tripSummary as $key => $item)
                @php
                    $tone = $item['tone'] ?? 'primary';
                    $icon = $item['icon'] ?? 'bi bi-activity';
                    $isPositive = ($item['change'] ?? 0) >= 0;
                    $displayValue = !empty($item['money'])
                        ? (($default_currency->symbol ?? '$') . number_format((float) ($item['value'] ?? 0), 2))
                        : number_format((float) ($item['value'] ?? 0));
                @endphp
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <a href="{{ url(LOGIN_USER_TYPE.'/trips') }}?status={{ $item['filter'] ?? 'all' }}" class="text-decoration-none">
                        <div class="card trip-kpi-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="trip-kpi-label">{{ str_replace('_', ' ', ucfirst($key)) }}</div>
                                        <div class="trip-kpi-value">{{ $displayValue }}</div>
                                    </div>
                                    <span class="trip-kpi-icon bg-{{ $tone }}-subtle text-{{ $tone }}">
                                        <i class="{{ $icon }}"></i>
                                    </span>
                                </div>
                                <div class="trip-kpi-change text-{{ $isPositive ? 'success' : 'danger' }}">
                                    <i class="bi {{ $isPositive ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    <span>{{ number_format((float) ($item['change'] ?? 0), 1) }}% vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card trip-panel mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <h5 class="mb-1">Operational Alerts</h5>
                        <p class="text-muted mb-0">Immediate issues and upcoming trip events that need ops attention.</p>
                    </div>
                    <div class="trip-ops-meta">
                        <span class="d-inline-flex align-items-center gap-2"><span class="trip-ops-live-dot d-none" id="live-indicator"></span> <span id="live-indicator-text">Live mode off</span></span>
                        <span>Last updated: <strong id="trip-last-updated">{{ $lastUpdatedAt }}</strong></span>
                    </div>
                </div>

                @if($tripAlerts->count())
                    <div class="trip-alert-strip">
                        @foreach($tripAlerts as $alert)
                            <a href="{{ url(LOGIN_USER_TYPE.'/trips') }}?quick_filter={{ $alert['filter'] }}" class="trip-alert-card">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $alert['label'] }}</div>
                                        <div class="text-muted small mt-1">Click to filter matching trips</div>
                                    </div>
                                    <span class="badge bg-{{ $alert['tone'] }}-subtle text-{{ $alert['tone'] }}">{{ $alert['count'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-success mb-0">No urgent operational alerts right now.</div>
                @endif
            </div>
        </div>

        <div class="card trip-panel mb-4">
            <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-1">Advanced Filters</h5>
                    <p class="text-muted mb-0">Slice trips by status, operator, vehicle, payment flow, time window, and dispatch mode.</p>
                </div>
                <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#trip-filters-panel" aria-expanded="true">
                    <i class="bi bi-sliders me-1"></i>Toggle Filters
                </button>
            </div>
            <div class="collapse show" id="trip-filters-panel">
                <div class="card-body">
                    <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/trips') }}" id="trip-filter-form">
                        <input type="hidden" name="quick_filter" id="trip-quick-filter-input" value="{{ $activeQuickFilter }}">
                        <div class="trip-filter-grid">
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach(($filterOptions['statuses'] ?? []) as $value => $label)
                                        <option value="{{ $value }}" {{ ($activeStatus ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Trip Type</label>
                                <select name="trip_type" class="form-select">
                                    <option value="">All</option>
                                    @foreach(($filterOptions['trip_types'] ?? []) as $value => $label)
                                        <option value="{{ $value }}" {{ request('trip_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">All</option>
                                    @foreach(($filterOptions['payment_methods'] ?? []) as $method)
                                        <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!empty($schemaSupport['trips_payment_status']))
                                <div>
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="">All</option>
                                        @foreach(($filterOptions['payment_statuses'] ?? []) as $status)
                                            <option value="{{ $status }}" {{ request('payment_status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if(LOGIN_USER_TYPE !== 'company')
                                <div>
                                    <label class="form-label">Company / Operator</label>
                                    <select name="company_id" class="form-select">
                                        <option value="">All</option>
                                        @foreach(($filterOptions['companies'] ?? []) as $id => $name)
                                            <option value="{{ $id }}" {{ (string) request('company_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="form-label">Driver</label>
                                <select name="driver_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach(($filterOptions['drivers'] ?? []) as $driver)
                                        <option value="{{ $driver->id }}" {{ (string) request('driver_id') === (string) $driver->id ? 'selected' : '' }}>{{ trim(($driver->first_name ?? '').' '.($driver->last_name ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Rider / Customer</label>
                                <select name="rider_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach(($filterOptions['riders'] ?? []) as $rider)
                                        <option value="{{ $rider->id }}" {{ (string) request('rider_id') === (string) $rider->id ? 'selected' : '' }}>{{ trim(($rider->first_name ?? '').' '.($rider->last_name ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicle_type_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach(($filterOptions['vehicle_types'] ?? []) as $id => $name)
                                        <option value="{{ $id }}" {{ (string) request('vehicle_type_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!empty($schemaSupport['request_location_id']))
                                <div>
                                    <label class="form-label">Service Area / Location</label>
                                    <select name="location_id" class="form-select">
                                        <option value="">All</option>
                                        @foreach(($filterOptions['locations'] ?? []) as $id => $name)
                                            <option value="{{ $id }}" {{ (string) request('location_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="form-label">Pickup City / Zone</label>
                                <input type="text" name="pickup_zone" value="{{ request('pickup_zone') }}" class="form-control" placeholder="Pickup city or zone">
                            </div>
                            <div>
                                <label class="form-label">From</label>
                                <input type="date" name="from" value="{{ $fromDate ?? '' }}" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">To</label>
                                <input type="date" name="to" value="{{ $toDate ?? '' }}" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">Min Fare</label>
                                <input type="number" step="0.01" name="fare_min" value="{{ request('fare_min') }}" class="form-control" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Max Fare</label>
                                <input type="number" step="0.01" name="fare_max" value="{{ request('fare_max') }}" class="form-control" placeholder="0.00">
                            </div>
                            @if(!empty($schemaSupport['schedule_ride_dispatch_mode']))
                                <div>
                                    <label class="form-label">Dispatch Mode</label>
                                    <select name="dispatch_mode" class="form-select">
                                        <option value="">All</option>
                                        @foreach(($filterOptions['dispatch_modes'] ?? []) as $value => $label)
                                            <option value="{{ $value }}" {{ request('dispatch_mode') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if(!empty($schemaSupport['trips_operator_type']))
                                <div>
                                    <label class="form-label">Operator Type</label>
                                    <select name="operator_type" class="form-select">
                                        <option value="">All</option>
                                        @foreach(($filterOptions['operator_types'] ?? []) as $value => $label)
                                            <option value="{{ $value }}" {{ request('operator_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
                            <div class="trip-quick-filters">
                                @foreach(($filterOptions['quick_filters'] ?? []) as $value => $label)
                                    <button type="button" class="btn {{ ($activeQuickFilter ?? '') === $value ? 'btn-primary' : 'btn-light' }} js-quick-filter" data-filter="{{ $value }}">{{ $label }}</button>
                                @endforeach
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-primary" type="submit">Apply</button>
                                <a class="btn btn-light" href="{{ url(LOGIN_USER_TYPE.'/trips') }}">Reset</a>
                                <button class="btn btn-outline-secondary" type="button" id="save-filter-view">Save Filter View</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card trip-analytics-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">Trips by Status</h5>
                                <p class="text-muted mb-0">Real-time distribution of current trip results and operational states.</p>
                            </div>
                        </div>
                        <div id="trip-status-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card trip-analytics-card h-100">
                    <div class="card-body">
                        <h5 class="mb-1">Payment Method Split</h5>
                        <p class="text-muted mb-3">How collections are currently distributed across payment channels.</p>
                        <div id="trip-payment-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card trip-analytics-card h-100">
                    <div class="card-body">
                        <h5 class="mb-1">Revenue by Day</h5>
                        <p class="text-muted mb-3">Rolling seven-day revenue movement for the current trip filter scope.</p>
                        <div id="trip-revenue-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card trip-analytics-card h-100">
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="mb-1">Top Drivers</h5>
                                <p class="text-muted mb-3">Most completed trips in this scope.</p>
                                <ul class="trip-list-mini">
                                    @forelse(($tripAnalytics['top_drivers'] ?? []) as $driver)
                                        <li>
                                            <span>{{ trim(($driver->first_name ?? '').' '.($driver->last_name ?? '')) ?: 'Unknown driver' }}</span>
                                            <span class="badge bg-primary-subtle text-primary">{{ $driver->total }}</span>
                                        </li>
                                    @empty
                                        <li><span class="text-muted">No driver data available</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">Busiest Pickup Zones</h5>
                                <p class="text-muted mb-3">Pickup hotspots from the filtered trips.</p>
                                <ul class="trip-list-mini">
                                    @forelse(($tripAnalytics['pickup_zones'] ?? []) as $zone)
                                        <li>
                                            <span title="{{ $zone->pickup_location }}">{{ \Illuminate\Support\Str::limit($zone->pickup_location, 24) }}</span>
                                            <span class="badge bg-info-subtle text-info">{{ $zone->total }}</span>
                                        </li>
                                    @empty
                                        <li><span class="text-muted">No pickup zone data available</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">Cancellation Reasons</h5>
                                <p class="text-muted mb-3">Latest cancellation patterns for ops review.</p>
                                <ul class="trip-list-mini">
                                    @forelse(($tripAnalytics['cancel_reasons'] ?? []) as $reason)
                                        <li>
                                            <span title="{{ $reason->cancel_reason }}">{{ \Illuminate\Support\Str::limit($reason->cancel_reason, 24) }}</span>
                                            <span class="badge bg-danger-subtle text-danger">{{ $reason->total }}</span>
                                        </li>
                                    @empty
                                        <li><span class="text-muted">No cancellation reason data available</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">Trip Reports</h5>
                                <p class="text-muted mb-3">Open broader reporting and operator statement views.</p>
                                <a href="{{ $reportsUrl }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-bar-chart-line me-1"></i>Open Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card trip-panel">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">Trip Grid</h5>
                        <p class="text-muted mb-0">Operate from table, compact, card, map, or timeline mode without leaving the existing trip route.</p>
                    </div>
                    <div class="trip-view-switcher">
                        <button type="button" class="btn btn-primary js-switch-view" data-view="table">Table View</button>
                        <button type="button" class="btn btn-light js-switch-view" data-view="compact">Compact View</button>
                        <button type="button" class="btn btn-light js-switch-view" data-view="card">Card View</button>
                        <button type="button" class="btn btn-light js-switch-view" data-view="map">Map View</button>
                        <button type="button" class="btn btn-light js-switch-view" data-view="timeline">Timeline View</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
                    <div class="d-flex gap-2 flex-wrap" id="bulk-actions-bar">
                        <button type="button" class="btn btn-light btn-sm js-bulk-action" data-action="export">Export Selected</button>
                        <button type="button" class="btn btn-light btn-sm js-bulk-action" data-action="reviewed">Mark Reviewed</button>
                        <button type="button" class="btn btn-light btn-sm js-bulk-action" data-action="assign">Assign Dispatcher</button>
                        <button type="button" class="btn btn-light btn-sm js-bulk-action" data-action="receipts">Download Receipts</button>
                        <button type="button" class="btn btn-light btn-sm js-bulk-action" data-action="audit">Flag for Audit</button>
                    </div>
                    <div class="trip-ops-meta">
                        <span id="selected-trips-count">0 trips selected</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="trip-table-view">
                    <div class="table-responsive">
                        <table class="table table-nowrap table-striped align-middle w-100" id="trips-table">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" class="form-check-input" id="select-all-trips"></th>
                                    <th>Trip ID / Reference</th>
                                    <th>Status</th>
                                    <th>Trip Type</th>
                                    <th>Rider</th>
                                    <th>Driver</th>
                                    @if(LOGIN_USER_TYPE !== 'company')
                                        <th>Company / Operator</th>
                                    @endif
                                    <th>Vehicle Type</th>
                                    <th>Pickup</th>
                                    <th>Dropoff</th>
                                    <th>Distance</th>
                                    <th>Duration</th>
                                    <th>Fare</th>
                                    <th>Payment Method</th>
                                    <th>Payment Status</th>
                                    <th>Driver Earnings</th>
                                    <th>Commission</th>
                                    <th>Created Time</th>
                                    <th>Scheduled Time</th>
                                    <th>Completed Time</th>
                                    <th>Dispatch Mode</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trips as $trip)
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input js-trip-select" value="{{ $trip->id }}"></td>
                                        <td><button type="button" class="btn btn-link p-0 fw-semibold text-decoration-none js-trip-preview" data-trip-id="{{ $trip->id }}">{{ $trip->trip_reference }}</button></td>
                                        <td>{!! $trip->status_badge !!}</td>
                                        <td>{!! $trip->trip_type_badge !!}</td>
                                        <td>{{ $trip->rider_name }}</td>
                                        <td>{{ $trip->driver_name }}</td>
                                        @if(LOGIN_USER_TYPE !== 'company')
                                            <td>{{ $trip->company_name }}</td>
                                        @endif
                                        <td>{{ $trip->vehicle_type }}</td>
                                        <td>{!! $trip->pickup_short !!}</td>
                                        <td>{!! $trip->drop_short !!}</td>
                                        <td>{{ $trip->distance_display }}</td>
                                        <td>{{ $trip->duration_display }}</td>
                                        <td>{{ $trip->fare_display }}</td>
                                        <td>{{ $trip->payment_method }}</td>
                                        <td>{!! $trip->payment_status_badge !!}</td>
                                        <td>{{ $trip->driver_earnings_display }}</td>
                                        <td>{{ $trip->commission_display }}</td>
                                        <td>{{ $trip->created_time }}</td>
                                        <td>{{ $trip->scheduled_time }}</td>
                                        <td>{{ $trip->completed_time }}</td>
                                        <td>{{ $trip->dispatch_mode_label }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ url(LOGIN_USER_TYPE.'/view_trips/'.$trip->id) }}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                                    @if(!empty($trip->request_id))
                                                        <li><a class="dropdown-item" href="{{ url(LOGIN_USER_TYPE.'/detail_request/'.$trip->request_id) }}"><i class="bi bi-card-list me-2"></i>View Booking</a></li>
                                                    @endif
                                                    @if(!empty($trip->payment_record_id) && (LOGIN_USER_TYPE === 'company' || optional(auth('admin')->user())->can('manage_payments')))
                                                        <li><a class="dropdown-item" href="{{ url(LOGIN_USER_TYPE.'/view_payments/'.$trip->payment_record_id) }}"><i class="bi bi-wallet2 me-2"></i>Payment Review</a></li>
                                                    @endif
                                                    <li><button type="button" class="dropdown-item js-trip-preview" data-trip-id="{{ $trip->id }}"><i class="bi bi-broadcast-pin me-2"></i>Track Trip</button></li>
                                                    @if(!empty($trip->driver_phone))
                                                        <li><a class="dropdown-item" href="tel:{{ $trip->driver_phone }}"><i class="bi bi-telephone-outbound me-2"></i>Contact Driver</a></li>
                                                    @endif
                                                    @if(!empty($trip->rider_phone))
                                                        <li><a class="dropdown-item" href="tel:{{ $trip->rider_phone }}"><i class="bi bi-telephone me-2"></i>Contact Rider</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ LOGIN_USER_TYPE !== 'company' ? 22 : 21 }}" class="text-center py-5 text-muted">No trips found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($trips->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $trips->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>

                <div class="trip-card-view" id="trip-card-view">
                    <div class="trip-card-grid" id="trip-card-grid"></div>
                </div>

                <div class="trip-map-shell" id="trip-map-view">
                    <div class="trip-map-header">
                        <div>
                            <h5 class="mb-1">Map View</h5>
                            <p class="text-muted mb-0">Pickup and dropoff points for the currently visible trip results.</p>
                        </div>
                        <div class="trip-map-metrics">
                            <span id="trip-map-route-count">0 routes</span>
                            <span id="trip-map-point-count">0 points</span>
                            <span id="trip-map-status">Waiting for map data</span>
                        </div>
                    </div>
                    <div id="trip-map-canvas" class="trip-map-canvas"></div>
                    <div class="trip-map-empty" id="trip-map-empty">Switch to Map View to plot trips with available pickup and dropoff coordinates.</div>
                </div>

                <div class="trip-timeline-view-shell" id="trip-timeline-view">
                    <div class="trip-timeline-grid" id="trip-timeline-grid"></div>
                </div>

                <div class="trip-empty-state mt-4" id="trip-empty-state">
                    <i class="bi bi-inboxes fs-1 text-primary d-block mb-3"></i>
                    <h5 class="mb-2">No trips found</h5>
                    <p class="text-muted mb-3">Try changing filters, switch the trip state, or create a new manual booking.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="{{ $manualBookingUrl }}" class="btn btn-primary">Create Manual Booking</a>
                        <a href="{{ url(LOGIN_USER_TYPE.'/trips') }}" class="btn btn-light">Reset Filters</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="tripDetailCanvas" aria-labelledby="tripDetailCanvasLabel">
        <div class="offcanvas-header">
            <div>
                <h5 class="offcanvas-title mb-1" id="tripDetailCanvasLabel">Trip Quick Preview</h5>
                <p class="text-muted mb-0" id="trip-preview-subtitle">Select a trip to inspect details, timeline, and payments.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="trip-offcanvas-section">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Trip ID</div>
                        <h4 class="mb-1" id="preview-trip-id">-</h4>
                    </div>
                    <div id="preview-status-badge"></div>
                </div>
            </div>

            <div class="trip-offcanvas-section">
                <h6 class="mb-3">Core Details</h6>
                <div class="row g-3">
                    <div class="col-6"><small class="text-muted d-block">Rider</small><strong id="preview-rider">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Driver</small><strong id="preview-driver">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Operator</small><strong id="preview-company">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Vehicle</small><strong id="preview-vehicle">-</strong></div>
                    <div class="col-12"><small class="text-muted d-block">Pickup</small><strong id="preview-pickup">-</strong></div>
                    <div class="col-12"><small class="text-muted d-block">Dropoff</small><strong id="preview-drop">-</strong></div>
                </div>
            </div>

            <div class="trip-offcanvas-section">
                <h6 class="mb-3">Revenue and Payment Summary</h6>
                <div class="row g-3">
                    <div class="col-6"><small class="text-muted d-block">Gross Fare</small><strong id="preview-fare">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Payment Method</small><strong id="preview-payment-method">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Payment Status</small><strong id="preview-payment-status">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Driver Earnings</small><strong id="preview-driver-earnings">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Commission</small><strong id="preview-commission">-</strong></div>
                    <div class="col-6"><small class="text-muted d-block">Dispatch Mode</small><strong id="preview-dispatch">-</strong></div>
                </div>
            </div>

            <div class="trip-offcanvas-section">
                <h6 class="mb-3">Trip Timeline</h6>
                <ul class="trip-timeline" id="preview-timeline"></ul>
            </div>

            <div class="trip-offcanvas-section">
                <div class="d-grid gap-2">
                    <a href="#" id="preview-full-view" class="btn btn-primary">Open Full Trip View</a>
                    <a href="#" id="preview-booking-view" class="btn btn-light">Open Booking Request</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tripRows = @json($tripRows->values());
            var selectedTrips = new Set();
            var liveMode = false;
            var liveInterval = null;
            var currentView = 'table';
            var tripMap = null;
            var tripMapInfoWindow = null;
            var tripMapMarkers = [];
            var tripMapPolylines = [];
            var tripMapBooting = false;
            var offcanvasElement = document.getElementById('tripDetailCanvas');
            var tripOffcanvas = window.bootstrap && offcanvasElement ? new bootstrap.Offcanvas(offcanvasElement) : null;

            var statusChartData = @json($tripAnalytics['status_chart'] ?? ['labels' => [], 'series' => []]);
            var revenueChartData = @json($tripAnalytics['revenue_chart'] ?? ['labels' => [], 'series' => []]);
            var paymentChartData = @json($tripAnalytics['payment_chart'] ?? ['labels' => [], 'series' => []]);

            renderChart('trip-status-chart', {
                chart: { type: 'donut', height: 320 },
                labels: statusChartData.labels || [],
                series: statusChartData.series || [],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true }
            });

            renderChart('trip-revenue-chart', {
                chart: { type: 'area', height: 320, toolbar: { show: false } },
                series: [{ name: 'Revenue', data: revenueChartData.series || [] }],
                xaxis: { categories: revenueChartData.labels || [] },
                stroke: { curve: 'smooth', width: 3 },
                fill: { opacity: 0.2 },
                colors: [LalaColors.info]
            });

            renderChart('trip-payment-chart', {
                chart: { type: 'pie', height: 320 },
                labels: paymentChartData.labels || [],
                series: paymentChartData.series || [],
                legend: { position: 'bottom' }
            });

            function renderChart(elId, options) {
                var el = document.getElementById(elId);
                if (!el || typeof ApexCharts === 'undefined') {
                    return;
                }

                var chart = new ApexCharts(el, options);
                chart.render();
            }

            function getVisibleTrips() {
                return tripRows.slice();
            }

            function updateSelectedTripsCount() {
                document.getElementById('selected-trips-count').textContent = selectedTrips.size + ' trips selected';
            }

            function updateLastUpdated() {
                var now = new Date();
                document.getElementById('trip-last-updated').textContent = now.toLocaleString();
            }

            function setTripMapStatus(message) {
                document.getElementById('trip-map-status').textContent = message;
            }

            function clearTripMapOverlays() {
                tripMapMarkers.forEach(function (marker) {
                    marker.setMap(null);
                });
                tripMapPolylines.forEach(function (polyline) {
                    polyline.setMap(null);
                });
                tripMapMarkers = [];
                tripMapPolylines = [];
            }

            function getMappableTrips() {
                return getVisibleTrips().filter(function (row) {
                    return row.pickup_latitude && row.pickup_longitude && row.drop_latitude && row.drop_longitude;
                });
            }

            function updateTripMapSummary(routeCount, pointCount, status) {
                document.getElementById('trip-map-route-count').textContent = routeCount + ' route' + (routeCount === 1 ? '' : 's');
                document.getElementById('trip-map-point-count').textContent = pointCount + ' point' + (pointCount === 1 ? '' : 's');
                setTripMapStatus(status);
            }

            function renderTripMap() {
                var mapCanvas = document.getElementById('trip-map-canvas');
                var mapEmpty = document.getElementById('trip-map-empty');
                var trips = getMappableTrips();

                if (!mapCanvas) {
                    return;
                }

                if (!window.google || !google.maps) {
                    updateTripMapSummary(0, 0, 'Google Maps is not available');
                    mapEmpty.textContent = 'Google Maps could not be loaded for this page. Check the configured map key and footer loader.';
                    return;
                }

                if (!tripMap) {
                    tripMap = new google.maps.Map(mapCanvas, {
                        zoom: 3,
                        minZoom: 2,
                        center: { lat: 0, lng: 0 },
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        streetViewControl: false,
                        mapTypeControl: true,
                        fullscreenControl: false
                    });
                    tripMapInfoWindow = new google.maps.InfoWindow();
                }

                clearTripMapOverlays();

                if (!trips.length) {
                    updateTripMapSummary(0, 0, 'No coordinates in current results');
                    mapEmpty.textContent = 'No trips with pickup and dropoff coordinates are available in the current filtered result set.';
                    return;
                }

                var bounds = new google.maps.LatLngBounds();

                trips.forEach(function (row) {
                    var pickup = {
                        lat: parseFloat(row.pickup_latitude),
                        lng: parseFloat(row.pickup_longitude)
                    };
                    var drop = {
                        lat: parseFloat(row.drop_latitude),
                        lng: parseFloat(row.drop_longitude)
                    };

                    if ([pickup.lat, pickup.lng, drop.lat, drop.lng].some(function (value) { return Number.isNaN(value); })) {
                        return;
                    }

                    var pickupMarker = new google.maps.Marker({
                        map: tripMap,
                        position: pickup,
                        title: 'Pickup #' + row.id,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            fillColor: LalaColors.success,
                            fillOpacity: 1,
                            strokeColor: LalaColors.white,
                            strokeWeight: 2,
                            scale: 7
                        }
                    });

                    var dropMarker = new google.maps.Marker({
                        map: tripMap,
                        position: drop,
                        title: 'Dropoff #' + row.id,
                        icon: {
                            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                            fillColor: LalaColors.error,
                            fillOpacity: 1,
                            strokeColor: LalaColors.white,
                            strokeWeight: 2,
                            scale: 5
                        }
                    });

                    var routeLine = new google.maps.Polyline({
                        map: tripMap,
                        path: [pickup, drop],
                        strokeColor: LalaColors.info,
                        strokeOpacity: 0.8,
                        strokeWeight: 3
                    });

                    [pickupMarker, dropMarker].forEach(function (marker) {
                        marker.addListener('click', function () {
                            tripMapInfoWindow.setContent(
                                '<div style="min-width:220px;">' +
                                    '<div style="font-weight:700;margin-bottom:6px;">Trip #' + row.id + '</div>' +
                                    '<div><strong>Rider:</strong> ' + (row.rider_name || '-') + '</div>' +
                                    '<div><strong>Driver:</strong> ' + $('<div>').html(row.driver_name || '-').text() + '</div>' +
                                    '<div><strong>Status:</strong> ' + $('<div>').html(row.status_badge || '-').text() + '</div>' +
                                    '<div><strong>Fare:</strong> ' + (row.fare_display || '-') + '</div>' +
                                '</div>'
                            );
                            tripMapInfoWindow.open(tripMap, marker);
                        });
                    });

                    tripMapMarkers.push(pickupMarker, dropMarker);
                    tripMapPolylines.push(routeLine);
                    bounds.extend(pickup);
                    bounds.extend(drop);
                });

                if (!bounds.isEmpty()) {
                    tripMap.fitBounds(bounds);
                }

                updateTripMapSummary(tripMapPolylines.length, tripMapMarkers.length, 'Showing filtered trips');
                mapEmpty.textContent = 'Pickup markers are green, dropoff markers are red, and each blue line links the visible trip route.';
            }

            function queueTripMapRender() {
                if (currentView !== 'map') {
                    return;
                }

                if (window.google && google.maps) {
                    renderTripMap();
                    return;
                }

                if (tripMapBooting) {
                    return;
                }

                tripMapBooting = true;
                updateTripMapSummary(0, 0, 'Waiting for Google Maps');

                (function waitForMaps() {
                    if (window.google && google.maps) {
                        tripMapBooting = false;
                        renderTripMap();
                        return;
                    }

                    window.setTimeout(waitForMaps, 250);
                })();
            }

            function syncEmptyState() {
                var hasRows = getVisibleTrips().length > 0;
                document.getElementById('trip-empty-state').style.display = hasRows ? 'none' : 'block';
            }

            function buildCardView() {
                var rows = getVisibleTrips();
                var grid = document.getElementById('trip-card-grid');
                grid.innerHTML = '';

                rows.forEach(function (row) {
                    var card = document.createElement('div');
                    card.className = 'trip-mini-card';
                    card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div>
                                <div class="fw-bold">${row.trip_reference}</div>
                                <div class="small text-muted">${row.created_time || '-'}</div>
                            </div>
                            <div>${row.status_badge || ''}</div>
                        </div>
                        <div class="small text-muted mb-1">Rider</div>
                        <div class="fw-semibold mb-2">${row.rider_name || '-'}</div>
                        <div class="small text-muted mb-1">Driver</div>
                        <div class="fw-semibold mb-2">${row.driver_name || '-'}</div>
                        <div class="small text-muted mb-1">Route</div>
                        <div class="mb-2">${row.pickup_location_full || '-'}<br><span class="text-muted">to</span><br>${row.drop_location_full || '-'}</div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <strong>${row.fare_display || '-'}</strong>
                            <button type="button" class="btn btn-sm btn-outline-primary js-trip-preview" data-trip-id="${row.id}">Preview</button>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            }

            function buildTimelineView() {
                var rows = getVisibleTrips();
                var grid = document.getElementById('trip-timeline-grid');
                grid.innerHTML = '';

                rows.forEach(function (row) {
                    var card = document.createElement('div');
                    card.className = 'trip-timeline-card';
                    card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="fw-bold">${row.trip_reference}</div>
                                <div class="trip-timeline-card__meta">${row.created_time || '-'}</div>
                            </div>
                            <div>${row.status_badge || ''}</div>
                        </div>
                        <ul class="trip-timeline mb-0">
                            ${(row.timeline || []).map(function (event) {
                                return `<li><strong>${event.label}</strong><div class="text-muted small">${event.value}</div></li>`;
                            }).join('')}
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted small">${row.pickup_location_full || '-'} to ${row.drop_location_full || '-'}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary js-trip-preview" data-trip-id="${row.id}">Preview</button>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            }

            function switchView(view) {
                currentView = view;
                document.querySelectorAll('.js-switch-view').forEach(function (button) {
                    button.classList.toggle('btn-primary', button.dataset.view === view);
                    button.classList.toggle('btn-light', button.dataset.view !== view);
                });

                document.getElementById('trip-table-view').style.display = (view === 'table' || view === 'compact') ? 'block' : 'none';
                document.getElementById('trip-card-view').style.display = view === 'card' ? 'block' : 'none';
                document.getElementById('trip-map-view').classList.toggle('active', view === 'map');
                document.getElementById('trip-timeline-view').classList.toggle('active', view === 'timeline');
                document.getElementById('trips-table').classList.toggle('trip-compact-table', view === 'compact');

                if (view === 'card') {
                    buildCardView();
                }
                if (view === 'timeline') {
                    buildTimelineView();
                }

                if (view === 'map') {
                    queueTripMapRender();
                }
            }

            function openTripPreviewById(tripId) {
                var rowData = getVisibleTrips().find(function (row) {
                    return String(row.id) === String(tripId);
                });

                if (!rowData) {
                    return;
                }

                document.getElementById('preview-trip-id').textContent = '#' + rowData.id;
                document.getElementById('preview-status-badge').innerHTML = rowData.status_badge || '';
                document.getElementById('preview-rider').textContent = rowData.rider_name || '-';
                document.getElementById('preview-driver').textContent = $('<div>').html(rowData.driver_name || '-').text() || '-';
                document.getElementById('preview-company').textContent = $('<div>').html(rowData.company_name || 'Platform').text() || 'Platform';
                document.getElementById('preview-vehicle').textContent = rowData.vehicle_type || '-';
                document.getElementById('preview-pickup').textContent = rowData.pickup_location_full || '-';
                document.getElementById('preview-drop').textContent = rowData.drop_location_full || '-';
                document.getElementById('preview-fare').textContent = rowData.fare_display || '-';
                document.getElementById('preview-payment-method').textContent = rowData.payment_method || '-';
                document.getElementById('preview-payment-status').textContent = $('<div>').html(rowData.payment_status_badge || '-').text() || '-';
                document.getElementById('preview-driver-earnings').textContent = rowData.driver_earnings_display || '-';
                document.getElementById('preview-commission').textContent = rowData.commission_display || '-';
                document.getElementById('preview-dispatch').textContent = $('<div>').html(rowData.dispatch_mode_label || '-').text() || '-';
                document.getElementById('preview-full-view').href = '{{ url(LOGIN_USER_TYPE.'/view_trips') }}/' + rowData.id;
                document.getElementById('preview-booking-view').href = rowData.request_id ? ('{{ url(LOGIN_USER_TYPE.'/detail_request') }}/' + rowData.request_id) : '#';
                document.getElementById('preview-booking-view').classList.toggle('disabled', !rowData.request_id);

                var timeline = document.getElementById('preview-timeline');
                timeline.innerHTML = '';
                [
                    { label: 'Booking created', value: rowData.created_time },
                    { label: 'Scheduled time', value: rowData.scheduled_time && rowData.scheduled_time !== '-' ? rowData.scheduled_time : null },
                    { label: 'Trip started', value: rowData.begin_trip && rowData.begin_trip !== '0000-00-00 00:00:00' ? rowData.begin_trip : null },
                    { label: 'Trip completed', value: rowData.completed_time && rowData.completed_time !== '-' ? rowData.completed_time : null },
                    { label: 'Payment status', value: $('<div>').html(rowData.payment_status_badge || '').text() || null }
                ].filter(function (event) {
                    return event.value;
                }).forEach(function (event) {
                    var li = document.createElement('li');
                    li.innerHTML = '<strong>' + event.label + '</strong><div class="text-muted small">' + event.value + '</div>';
                    timeline.appendChild(li);
                });

                if (tripOffcanvas) {
                    tripOffcanvas.show();
                }
            }

            function reloadTrips() {
                window.location.reload();
            }

            $(document).on('change', '#select-all-trips', function () {
                var checked = this.checked;
                $('.js-trip-select').prop('checked', checked).trigger('change');
            });

            $(document).on('change', '.js-trip-select', function () {
                if (this.checked) {
                    selectedTrips.add(this.value);
                } else {
                    selectedTrips.delete(this.value);
                }
                updateSelectedTripsCount();
            });

            $(document).on('click', '.js-trip-preview', function () {
                openTripPreviewById(this.dataset.tripId);
            });

            document.querySelectorAll('.js-switch-view').forEach(function (button) {
                button.addEventListener('click', function () {
                    switchView(button.dataset.view);
                });
            });

            document.querySelectorAll('.js-quick-filter').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.getElementById('trip-quick-filter-input').value = button.dataset.filter;
                    document.getElementById('trip-filter-form').submit();
                });
            });

            document.querySelector('.js-refresh-trips').addEventListener('click', reloadTrips);

            document.querySelector('.js-live-toggle').addEventListener('click', function () {
                liveMode = !liveMode;
                this.dataset.live = liveMode ? 'on' : 'off';
                this.classList.toggle('btn-success', liveMode);
                this.classList.toggle('btn-outline-light', !liveMode);
                document.getElementById('live-indicator').classList.toggle('d-none', !liveMode);
                document.getElementById('live-indicator-text').textContent = liveMode ? 'Live mode on' : 'Live mode off';

                if (liveInterval) {
                    clearInterval(liveInterval);
                    liveInterval = null;
                }

                if (liveMode) {
                    liveInterval = setInterval(reloadTrips, {{ (int) ($liveRefreshSeconds ?? 30) * 1000 }});
                }
            });

            document.querySelectorAll('.js-bulk-action').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!selectedTrips.size) {
                        alert('Select at least one trip first.');
                        return;
                    }

                    var action = button.dataset.action;
                    if (action === 'export') {
                        window.location.href = '{{ url(LOGIN_USER_TYPE.'/trips/export/'.$exportFrom.'/'.$exportTo) }}';
                        return;
                    }

                    if (action === 'reviewed') {
                        localStorage.setItem('trip_operations_reviewed', JSON.stringify(Array.from(selectedTrips)));
                        alert('Selected trips were marked as reviewed in this browser.');
                        return;
                    }

                    if (action === 'receipts') {
                        Array.from(selectedTrips).slice(0, 5).forEach(function (tripId) {
                            window.open('{{ url(LOGIN_USER_TYPE.'/view_trips') }}/' + tripId, '_blank');
                        });
                        return;
                    }

                    if (action === 'assign') {
                        openTripPreviewById(Array.from(selectedTrips)[0]);
                        return;
                    }

                    if (action === 'audit') {
                        localStorage.setItem('trip_operations_audit', JSON.stringify(Array.from(selectedTrips)));
                        alert('Selected trips were flagged for audit in this browser.');
                    }
                });
            });

            document.getElementById('save-filter-view').addEventListener('click', function () {
                try {
                    localStorage.setItem('trip_operations_saved_filter', new URLSearchParams(new FormData(document.getElementById('trip-filter-form'))).toString());
                    alert('Current filter view saved in this browser.');
                } catch (error) {
                    alert('Unable to save the filter view in this browser.');
                }
            });

            setTimeout(function () {
                syncEmptyState();
            }, 1200);
        });
    </script>
@endpush
