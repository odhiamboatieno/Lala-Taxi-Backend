@extends('admin.template')

@php
    $currencySymbol = $currency_code ?? '$';
    $legacyStats = [
        ['label' => __('messages.blade_static.total_users_a028fce2'), 'value' => $users_count ?? 0, 'icon' => 'ri-group-line', 'tone' => 'primary'],
        ['label' => __('messages.blade_static.total_drivers_2bebc766'), 'value' => $total_driver ?? 0, 'icon' => 'ri-taxi-line', 'tone' => 'success'],
        ['label' => __('messages.blade_static.total_riders_08fd75a3'), 'value' => $total_rider ?? 0, 'icon' => 'ri-user-3-line', 'tone' => 'info'],
        ['label' => __('messages.blade_static.today_bookings_8ca9b81e'), 'value' => $today_trips ?? 0, 'icon' => 'ri-calendar-check-line', 'tone' => 'warning'],
        ['label' => __('messages.blade_static.completed_trips_ba85c41c'), 'value' => $total_success_trips ?? 0, 'icon' => 'ri-checkbox-circle-line', 'tone' => 'success'],
        ['label' => __('messages.blade_static.platform_revenue_e40996c9'), 'value' => $currencySymbol . number_format((float) ($today_revenue ?? 0), 2), 'icon' => 'ri-money-dollar-circle-line', 'tone' => 'danger'],
    ];
    $overviewCards = $dashboard_overview ?? [];
    $summaryCards = array_map(function ($stat) {
        return [
            'label' => $stat['label'] ?? '',
            'value' => $stat['value'] ?? 0,
            'icon' => $stat['icon'] ?? 'fa fa-bar-chart',
        ];
    }, $legacyStats);

    foreach ($overviewCards as $card) {
        $value = !empty($card['money'])
            ? $currencySymbol . number_format((float) ($card['value'] ?? 0), 2)
            : number_format((float) ($card['value'] ?? 0), 0);

        $secondary = null;
        if (isset($card['secondary_value'])) {
            $secondaryValue = !empty($card['money_secondary'])
                ? $currencySymbol . number_format((float) $card['secondary_value'], 2)
                : number_format((float) $card['secondary_value'], 0);
            $secondary = trim($secondaryValue . ' ' . ($card['secondary_label'] ?? ''));
        }

        $summaryCards[] = [
            'label' => $card['label'] ?? '',
            'value' => $value,
            'secondary' => $secondary,
            'icon' => $card['icon'] ?? 'fa fa-bar-chart',
        ];
    }
    $locationStats = $location_trip_statistics ?? ['periods' => [], 'default_period' => 'all_time'];
    $earningsChart = $earnings_chart ?? ['periods' => [], 'default_period' => 'all_time', 'series_title' => 'Earnings'];
    $driverBoard = $driver_leaderboard ?? ['periods' => [], 'default_period' => 'all_time'];
    $customerBoard = $customer_leaderboard ?? ['periods' => [], 'default_period' => 'all_time'];
    $recentTransactions = $recent_transactions ?? [];
    $recentTripActivity = $recent_trip_activity ?? [];
@endphp

@section('title-sub', __('messages.blade_static.dashboard_d87f47b4'))
@section('pagetitle', __('messages.blade_static.carpool_dashboard_641f6f61'))

@section('css')
    <style>
        .ops-dashboard {
            color: var(--color-as-1f3554);
        }

        .ops-welcome {
            align-items: center;
            background: var(--color-as-f6fbfd);
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 0;
            box-shadow: 0 8px 18px var(--color-as-rgba-27-65-93-0-05);
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            padding: 24px 26px;
        }

        .ops-welcome__title {
            color: var(--color-as-183b68);
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 6px;
        }

        .ops-welcome__subtitle {
            color: var(--color-as-68809b);
            font-size: 14px;
            margin: 0;
        }

        .ops-welcome__badge {
            background: var(--color-as-rgba-56-189-178-0-12);
            border: 1px solid var(--color-as-rgba-56-189-178-0-24);
            border-radius: 0;
            color: var(--color-as-17a398);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            padding: 10px 14px;
            text-transform: uppercase;
        }

        .dashboard-card {
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 12px;
            box-shadow: 0 6px 18px var(--color-as-rgba-27-65-93-0-05);
            margin-bottom: 20px;
            min-height: 126px;
            overflow: hidden;
        }

        .dashboard-card__inner {
            align-items: center;
            display: flex;
            flex-direction: row;
            gap: 15px;
            justify-content: flex-start;
            min-height: 126px;
            padding: 18px;
        }

        .dashboard-card__icon {
            align-items: center;
            background: var(--color-as-f6fbfd);
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 10px;
            color: var(--color-as-68809b);
            display: inline-flex;
            flex: 0 0 46px;
            font-size: 18px;
            height: 46px;
            justify-content: center;
            margin: 0;
            width: 46px;
        }

        .dashboard-card__content {
            flex: 1 1 auto;
            min-width: 0;
        }

        .dashboard-card__label {
            color: var(--color-as-6b8096);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .dashboard-card__value {
            color: var(--color-as-16375a);
            font-size: 28px;
            font-weight: 800;
            line-height: 1.1;
        }

        .dashboard-card__secondary {
            color: var(--color-as-68809b);
            font-size: 13px;
            font-weight: 600;
            margin-top: 6px;
        }

        .ops-card {
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 12px;
            box-shadow: 0 6px 18px var(--color-as-rgba-27-65-93-0-05);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .ops-card__head {
            align-items: center;
            border-bottom: 1px solid var(--color-as-rgba-216-241-244-0-9);
            display: flex;
            justify-content: space-between;
            padding: 18px 22px 16px;
        }

        .ops-card__title {
            color: var(--color-as-1d3d67);
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
            margin: 0;
        }

        .ops-card__caption {
            color: var(--color-as-8aa1b7);
            display: block;
            font-size: 13px;
            margin-top: 4px;
        }

        .ops-card__body {
            padding: 22px;
        }

        .ops-mini-grid .col-lg-3,
        .ops-mini-grid .col-md-6,
        .ops-mini-grid .col-sm-6 {
            margin-bottom: 18px;
        }

        .ops-stat {
            align-items: center;
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 12px;
            box-shadow: 0 6px 18px var(--color-as-rgba-27-65-93-0-05);
            display: flex;
            gap: 17px;
            min-height: 126px;
            padding: 18px;
            position: relative;
        }

        .ops-stat__icon {
            align-items: center;
            background: var(--color-as-f6fbfd);
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 10px;
            color: var(--color-as-68809b);
            display: inline-flex;
            flex: 0 0 50px;
            font-size: 21px;
            height: 50px;
            justify-content: center;
            margin: 0;
            width: 50px;
        }

        .ops-stat__content {
            flex: 1 1 auto;
            min-width: 0;
        }

        .ops-stat__value {
            color: var(--color-as-1f3554);
            font-weight: 700;
            margin: 0;
        }

        .ops-stat__value strong {
            display: block;
            font-size: 28px;
            line-height: 1;
            margin: 0;
        }

        .ops-stat__label {
            color: var(--color-as-6b8096);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .ops-stat__secondary {
            color: var(--color-as-68809b);
            font-size: 13px;
            font-weight: 600;
            margin-top: 6px;
        }

        .ops-zone__pane {
            display: none;
        }

        .ops-zone__pane.is-active {
            display: block;
        }

        .ops-zone__row + .ops-zone__row {
            margin-top: 24px;
        }

        .ops-zone__line {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .ops-zone__name {
            color: var(--color-as-1f3554);
            font-size: 15px;
            font-weight: 700;
        }

        .ops-zone__meta {
            color: var(--color-as-8196aa);
            font-size: 13px;
            font-weight: 700;
        }

        .ops-zone__track {
            background: var(--color-as-edf3f8);
            border-radius: 0;
            height: 12px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .ops-zone__bar {
            border-radius: 0;
            height: 100%;
        }

        .ops-zone__bar--1,
        .ops-zone__bar--2,
        .ops-zone__bar--3,
        .ops-zone__bar--4,
        .ops-zone__bar--5 { background: var(--color-as-35c5a8); }

        .ops-chart-card .ops-card__body {
            padding-top: 10px;
        }

        .ops-analytics-row {
            align-items: stretch;
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .ops-analytics__earnings,
        .ops-analytics__location {
            align-items: stretch;
            display: flex;
        }

        .ops-analytics-row .ops-card {
            margin-bottom: 24px;
            width: 100%;
        }

        .ops-chart {
            min-height: 380px;
            overflow: visible;
            position: relative;
        }

        .morris-hover.morris-default-style {
            background: var(--color-white);
            border: 1px solid var(--color-as-d8e8ef);
            border-radius: 0;
            box-shadow: 0 16px 34px var(--color-as-rgba-15-23-42-0-16);
            color: var(--color-as-16375a);
            padding: 12px 14px;
            z-index: 9999;
        }

        .morris-hover.morris-default-style .morris-hover-row-label {
            color: var(--color-as-16375a);
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .morris-hover.morris-default-style .morris-hover-point {
            color: var(--color-as-40627f) !important;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.5;
            white-space: nowrap;
        }

        .ops-toolbar {
            display: flex;
            gap: 10px;
        }

        .ops-select {
            background: var(--color-white);
            border: 1px solid var(--color-as-dcecf1);
            border-radius: 0;
            color: var(--color-as-1f3554);
            font-size: 13px;
            font-weight: 700;
            min-width: 130px;
            padding: 10px 14px;
        }

        .ops-board {
            min-height: 560px;
        }

        .ops-board__tabs {
            margin-top: 6px;
            text-align: right;
        }

        .ops-board__tab {
            background: transparent;
            border: 0;
            border-radius: 0;
            color: var(--color-as-1f3554);
            font-size: 14px;
            font-weight: 700;
            margin-left: 8px;
            padding: 8px 14px;
        }

        .ops-board__tab.is-active {
            background: var(--color-as-35c5a8);
            color: var(--color-white);
        }

        .ops-board__pane {
            display: none;
        }

        .ops-board__pane.is-active {
            display: block;
        }

        .ops-board__wrap {
            display: flex;
            gap: 18px;
        }

        .ops-board__podium {
            align-items: flex-end;
            display: flex;
            flex: 0 0 210px;
            gap: 10px;
            justify-content: center;
            padding: 28px 10px 6px;
        }

        .ops-podium__item {
            border-radius: 0;
            color: var(--color-white);
            min-height: 120px;
            padding: 16px 10px 14px;
            position: relative;
            text-align: center;
            width: 64px;
        }

        .ops-podium__item--first {
            background: linear-gradient(180deg, var(--color-as-37c3af) 0%, var(--color-as-18aa94) 100%);
            min-height: 228px;
        }

        .ops-podium__item--second {
            background: linear-gradient(180deg, var(--color-as-f9ab62) 0%, var(--color-as-f18a47) 100%);
            min-height: 168px;
        }

        .ops-podium__item--third {
            background: linear-gradient(180deg, var(--color-as-f3d879) 0%, var(--color-as-efc94a) 100%);
            min-height: 148px;
        }

        .ops-podium__avatar {
            border: 4px solid var(--color-white);
            border-radius: 0;
            height: 72px;
            left: 50%;
            object-fit: cover;
            position: absolute;
            top: -42px;
            transform: translateX(-50%);
            width: 72px;
        }

        .ops-podium__metric {
            display: block;
            font-size: 22px;
            font-weight: 800;
            margin-top: 30px;
            line-height: 1;
        }

        .ops-podium__label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-top: 4px;
            opacity: .95;
        }

        .ops-podium__rank {
            align-items: center;
            background: var(--color-as-rgba-255-255-255-0-18);
            border: 3px solid var(--color-as-rgba-255-255-255-0-7);
            border-radius: 50%;
            bottom: 16px;
            display: inline-flex;
            font-size: 18px;
            font-weight: 800;
            height: 38px;
            justify-content: center;
            left: 50%;
            margin-left: -19px;
            width: 38px;
        }

        .ops-podium__name {
            color: var(--color-white);
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-top: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ops-podium__rating {
            color: var(--color-as-rgba-255-255-255-0-92);
            display: block;
            font-size: 11px;
            margin-top: 4px;
        }

        .ops-board__list {
            flex: 1 1 auto;
            padding-top: 10px;
        }

        .ops-board__row {
            align-items: center;
            border-top: 1px solid var(--color-as-ebf4f7);
            display: flex;
            justify-content: space-between;
            padding: 16px 0;
        }

        .ops-board__row:first-child {
            border-top: 0;
            padding-top: 4px;
        }

        .ops-board__user {
            align-items: center;
            display: flex;
            gap: 12px;
        }

        .ops-board__thumb {
            border-radius: 0;
            height: 44px;
            object-fit: cover;
            width: 44px;
        }

        .ops-board__name {
            color: var(--color-as-1f3554);
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .ops-board__sub {
            color: var(--color-as-8aa1b7);
            font-size: 12px;
            font-weight: 600;
        }

        .ops-board__metric {
            color: var(--color-as-1f3554);
            font-size: 16px;
            font-weight: 800;
        }

        .ops-empty {
            color: var(--color-as-8aa1b7);
            font-size: 14px;
            padding: 40px 10px;
            text-align: center;
        }

        .ops-feed__item {
            border-top: 1px solid var(--color-as-ebf4f7);
            padding: 16px 0;
            position: relative;
        }

        .ops-floating-tooltip {
            background: var(--color-white);
            border: 1px solid var(--color-as-d8e8ef);
            border-radius: 0;
            box-shadow: 0 16px 34px var(--color-as-rgba-15-23-42-0-16);
            color: var(--color-as-16375a);
            display: none;
            min-width: 210px;
            padding: 12px 14px;
            pointer-events: none;
            position: fixed;
            z-index: 9999;
        }

        .ops-floating-tooltip.is-visible {
            display: block;
        }

        .ops-floating-tooltip__title {
            color: var(--color-as-16375a);
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .ops-floating-tooltip__line {
            color: var(--color-as-4e6880);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.6;
        }

        .ops-legacy-row .col-lg-2,
        .ops-legacy-row .col-md-4,
        .ops-legacy-row .col-sm-6 {
            margin-bottom: 0;
        }

        .ops-feed__item:first-child {
            border-top: 0;
            padding-top: 4px;
        }

        .ops-feed__dot {
            align-items: center;
            display: inline-flex;
            height: 34px;
            left: 0;
            position: absolute;
            top: 14px;
            width: 46px;
        }

        .ops-feed__content {
            padding-left: 58px;
        }

        .ops-feed__avatars {
            align-items: center;
            display: inline-flex;
            position: relative;
        }

        .ops-feed__avatar {
            background: var(--color-white);
            border: 2px solid var(--color-white);
            border-radius: 0;
            box-shadow: 0 6px 14px var(--color-as-rgba-15-23-42-0-12);
            height: 28px;
            object-fit: cover;
            width: 28px;
        }

        .ops-feed__avatar + .ops-feed__avatar {
            margin-left: -8px;
        }

        .ops-feed__amount {
            color: var(--color-as-1f3554);
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 8px;
        }

        .ops-feed__title {
            color: var(--color-as-1f3554);
            font-size: 14px;
            font-weight: 700;
        }

        .ops-feed__meta {
            color: var(--color-as-8aa1b7);
            font-size: 12px;
            margin-top: 6px;
        }

        .ops-feed__badge {
            border-radius: 0;
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            margin-top: 10px;
            padding: 4px 10px;
            text-transform: uppercase;
        }

        .ops-badge--success {
            background: var(--color-as-rgba-50-199-170-0-14);
            color: var(--color-as-19a78f);
        }

        .ops-badge--warning {
            background: var(--color-as-rgba-255-186-73-0-16);
            color: var(--color-as-d39424);
        }

        .ops-badge--danger {
            background: var(--color-as-rgba-255-108-122-0-15);
            color: var(--color-as-ee5d69);
        }

        .ops-badge--info {
            background: var(--color-as-rgba-92-151-255-0-15);
            color: var(--color-as-4678d8);
        }

        .ops-activity__row {
            align-items: center;
            border-top: 1px solid var(--color-as-ebf4f7);
            display: flex;
            gap: 14px;
            padding: 14px 0;
        }

        .ops-activity__row:first-child {
            border-top: 0;
            padding-top: 6px;
        }

        .ops-activity__icon {
            align-items: center;
            background: var(--color-as-f2f8fb);
            border: 1px solid var(--color-as-dfeff3);
            border-radius: 50% !important;
            color: var(--color-as-69839c);
            display: inline-flex;
            flex: 0 0 48px;
            font-size: 21px;
            height: 48px;
            justify-content: center;
            overflow: hidden;
            width: 48px;
        }

        .ops-activity__vehicle-image {
            border-radius: 50% !important;
            display: block;
            height: 40px;
            object-fit: cover;
            width: 40px;
        }

        .ops-activity__title {
            color: var(--color-as-1f3554);
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .ops-activity__meta {
            color: var(--color-as-8aa1b7);
            font-size: 12px;
        }

        .ops-activity__status {
            margin-left: auto;
        }

        .ops-section-spacer {
            margin-top: 8px;
        }

        @media (max-width: 1199px) {
            .ops-analytics-row {
                display: block;
            }

            .ops-board__wrap {
                display: block;
            }

            .ops-board__podium {
                margin-bottom: 18px;
            }
        }

        @media (max-width: 767px) {
            .ops-welcome {
                display: block;
                padding: 22px;
            }

            .ops-welcome__badge {
                display: inline-block;
                margin-top: 14px;
            }

            .ops-card__head {
                display: block;
            }

            .ops-toolbar,
            .ops-board__tabs {
                margin-top: 12px;
                text-align: left;
            }

            .ops-board__tab {
                margin: 0 8px 8px 0;
            }

            .ops-board__podium {
                overflow-x: auto;
            }

            .dashboard-card__inner,
            .ops-stat {
                min-height: 108px;
            }
        }

        @media (min-width: 1200px) {
            .ops-analytics-row .ops-card {
                height: 100%;
            }

            .ops-analytics__earnings {
                flex: 0 0 65%;
                max-width: 65%;
            }

            .ops-analytics__location {
                flex: 0 0 35%;
                max-width: 35%;
            }
        }

        /* Dashboard dark mode: one quiet neutral surface system with a teal accent. */
        [data-bs-theme="dark"] {
            --ops-surface: #17232d;
            --ops-surface-soft: #1e2d38;
            --ops-border: #2b3d4b;
            --ops-text: #e7eef5;
            --ops-muted: #93a4b5;
            --ops-accent: #55b9ad;
        }

        [data-bs-theme="dark"] .ops-dashboard {
            color: var(--ops-text);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-welcome,
        [data-bs-theme="dark"] .ops-dashboard .dashboard-card,
        [data-bs-theme="dark"] .ops-dashboard .ops-stat,
        [data-bs-theme="dark"] .ops-dashboard .ops-card,
        [data-bs-theme="dark"] .morris-hover.morris-default-style,
        [data-bs-theme="dark"] .ops-floating-tooltip {
            background: var(--ops-surface);
            border-color: var(--ops-border);
            box-shadow: 0 10px 28px rgba(0, 0, 0, .24);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-welcome {
            background: var(--ops-surface-soft);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-welcome__title,
        [data-bs-theme="dark"] .ops-dashboard .ops-card__title,
        [data-bs-theme="dark"] .ops-dashboard .dashboard-card__value,
        [data-bs-theme="dark"] .ops-dashboard .ops-stat__value,
        [data-bs-theme="dark"] .ops-dashboard .ops-zone__name,
        [data-bs-theme="dark"] .ops-dashboard .ops-board__name,
        [data-bs-theme="dark"] .ops-dashboard .ops-board__metric,
        [data-bs-theme="dark"] .ops-dashboard .ops-feed__title,
        [data-bs-theme="dark"] .ops-dashboard .ops-feed__amount,
        [data-bs-theme="dark"] .ops-dashboard .ops-activity__title,
        [data-bs-theme="dark"] .morris-hover.morris-default-style,
        [data-bs-theme="dark"] .morris-hover.morris-default-style .morris-hover-row-label,
        [data-bs-theme="dark"] .ops-floating-tooltip__title {
            color: var(--ops-text);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-welcome__subtitle,
        [data-bs-theme="dark"] .ops-dashboard .dashboard-card__label,
        [data-bs-theme="dark"] .ops-dashboard .dashboard-card__secondary,
        [data-bs-theme="dark"] .ops-dashboard .ops-stat__label,
        [data-bs-theme="dark"] .ops-dashboard .ops-stat__secondary,
        [data-bs-theme="dark"] .ops-dashboard .ops-card__caption,
        [data-bs-theme="dark"] .ops-dashboard .ops-zone__meta,
        [data-bs-theme="dark"] .ops-dashboard .ops-board__sub,
        [data-bs-theme="dark"] .ops-dashboard .ops-feed__meta,
        [data-bs-theme="dark"] .ops-dashboard .ops-activity__meta,
        [data-bs-theme="dark"] .ops-dashboard .ops-empty,
        [data-bs-theme="dark"] .morris-hover.morris-default-style .morris-hover-point,
        [data-bs-theme="dark"] .ops-floating-tooltip__line {
            color: var(--ops-muted) !important;
        }

        [data-bs-theme="dark"] .ops-dashboard .dashboard-card__icon,
        [data-bs-theme="dark"] .ops-dashboard .ops-stat__icon,
        [data-bs-theme="dark"] .ops-dashboard .ops-activity__icon {
            background: var(--ops-surface-soft);
            border-color: var(--ops-border);
            color: var(--ops-muted);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-card__head,
        [data-bs-theme="dark"] .ops-dashboard .ops-board__row,
        [data-bs-theme="dark"] .ops-dashboard .ops-feed__item,
        [data-bs-theme="dark"] .ops-dashboard .ops-activity__row {
            border-color: var(--ops-border);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-select {
            background: var(--ops-surface-soft);
            border-color: var(--ops-border);
            color: var(--ops-text);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-board__tab {
            color: var(--ops-muted);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-board__tab.is-active,
        [data-bs-theme="dark"] .ops-dashboard .ops-zone__bar {
            background: var(--ops-accent);
            color: var(--color-white);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-zone__track {
            background: var(--ops-surface-soft);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-welcome__badge {
            background: rgba(85, 185, 173, .14);
            border-color: rgba(85, 185, 173, .28);
            color: #79d5ca;
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-podium__item--first {
            background: linear-gradient(180deg, #276d66 0%, #1c504c 100%);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-podium__item--second {
            background: linear-gradient(180deg, #536673 0%, #3f505d 100%);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-podium__item--third {
            background: linear-gradient(180deg, #475c6b 0%, #354a59 100%);
        }

        [data-bs-theme="dark"] .ops-dashboard .ops-podium__avatar {
            border-color: var(--ops-surface);
        }
    </style>
@endsection

@section('main')
    <div class="ops-dashboard">
        <div class="ops-welcome">
            <div>
                <h1 class="ops-welcome__title">{{ $dashboard_title ?? 'Operations Dashboard' }}</h1>
                <p class="ops-welcome__subtitle">{{ $dashboard_subtitle ?? 'Monitor your business statistics in real time.' }}</p>
            </div>
            <div class="ops-welcome__badge">{{ LOGIN_USER_TYPE == 'company' ? 'Company view' : 'Platform view' }}</div>
        </div>

        <div class="row ops-metric-grid">
            @foreach($summaryCards as $stat)
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="dashboard-card card">
                        <div class="dashboard-card__inner">
                            <div class="dashboard-card__icon">
                                <i class="{{ $stat['icon'] ?? 'fa fa-bar-chart' }}"></i>
                            </div>
                            <div class="dashboard-card__content">
                                <div class="dashboard-card__label">{{ $stat['label'] }}</div>
                                <div class="dashboard-card__value">{{ $stat['value'] }}</div>
                                @if(!empty($stat['secondary']))
                                    <div class="dashboard-card__secondary">{{ $stat['secondary'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row ops-analytics-row">
            <div class="col-lg-8 ops-analytics__earnings">
                <div class="ops-card ops-chart-card card">
                    <div class="ops-card__head">
                        <div>
                            <h3 class="ops-card__title">{{ $earningsChart['title'] ?? 'Earnings Statistics' }}</h3>
                            <span class="ops-card__caption">{{ $earningsChart['subtitle'] ?? 'Gross bookings vs earnings' }}</span>
                        </div>
                        <div class="ops-toolbar">
                            <select class="ops-select" id="earnings-period-switch">
                                @foreach(($earningsChart['periods'] ?? []) as $periodKey => $periodData)
                                    <option value="{{ $periodKey }}" {{ ($earningsChart['default_period'] ?? '') === $periodKey ? 'selected' : '' }}>
                                        {{ $periodData['label'] ?? ucfirst(str_replace('_', ' ', $periodKey)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ops-card__body">
                        <div id="ops-earnings-chart" class="ops-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 ops-analytics__location">
                <div class="ops-card ops-location-card card">
                    <div class="ops-card__head">
                        <div>
                            <h3 class="ops-card__title">{{ $locationStats['title'] ?? 'Location-Based Trip Statistics' }}</h3>
                            <span class="ops-card__caption">{{ $locationStats['subtitle'] ?? 'Trip volume by active zone' }}</span>
                        </div>
                        <div class="ops-toolbar">
                            <select class="ops-select js-pane-select" data-target="location-stats">
                                @foreach(($locationStats['periods'] ?? []) as $periodKey => $periodData)
                                    <option value="{{ $periodKey }}" {{ ($locationStats['default_period'] ?? '') === $periodKey ? 'selected' : '' }}>
                                        {{ $periodData['label'] ?? ucfirst(str_replace('_', ' ', $periodKey)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ops-card__body">
                        @forelse(($locationStats['periods'] ?? []) as $periodKey => $periodData)
                            <div class="ops-zone__pane {{ ($locationStats['default_period'] ?? '') === $periodKey ? 'is-active' : '' }}" data-pane-group="location-stats" data-pane="{{ $periodKey }}">
                                @forelse(($periodData['items'] ?? []) as $index => $item)
                                    <div class="ops-zone__row">
                                        <div class="ops-zone__line">
                                            <span class="ops-zone__name">{{ $item['name'] ?? 'Unknown zone' }}</span>
                                            <span class="ops-zone__meta">{{ number_format((float) ($item['volume_percentage'] ?? 0), 1) }}% Trip volume</span>
                                        </div>
                                        <div
                                            class="ops-zone__track js-zone-tooltip"
                                            data-zone-name="{{ $item['name'] ?? 'Zone' }}"
                                            data-on-trip="{{ number_format((int) ($item['on_trip_trips'] ?? 0)) }}"
                                            data-completed="{{ number_format((int) ($item['completed_trips'] ?? 0)) }}"
                                            data-cancelled="{{ number_format((int) ($item['cancelled_trips'] ?? 0)) }}"
                                            data-total="{{ number_format((int) ($item['total_trips'] ?? 0)) }}"
                                        >
                                            <div class="ops-zone__bar ops-zone__bar--{{ ($index % 5) + 1 }}" style="width: {{ max(4, (float) ($item['volume_percentage'] ?? 0)) }}%;"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="ops-empty">No trip data available for this period.</div>
                                @endforelse
                            </div>
                        @empty
                            <div class="ops-empty">No zone statistics available yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row ops-section-spacer">
            <div class="col-lg-8">
                <div class="ops-card ops-board card">
                    <div class="ops-card__head">
                        <div>
                            <h3 class="ops-card__title">Leader Board</h3>
                            <span class="ops-card__caption">Driver performance ranking</span>
                        </div>
                        <div class="ops-board__tabs">
                            @foreach(($driverBoard['periods'] ?? []) as $periodKey => $periodData)
                                <button type="button" class="ops-board__tab js-board-tab {{ ($driverBoard['default_period'] ?? '') === $periodKey ? 'is-active' : '' }}" data-group="driver-board" data-period="{{ $periodKey }}">
                                    {{ $periodData['label'] ?? ucfirst(str_replace('_', ' ', $periodKey)) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="ops-card__body">
                        @foreach(($driverBoard['periods'] ?? []) as $periodKey => $periodData)
                            @php
                                $items = collect($periodData['items'] ?? []);
                                $first = $items->get(0);
                                $second = $items->get(1);
                                $third = $items->get(2);
                            @endphp
                            <div class="ops-board__pane {{ ($driverBoard['default_period'] ?? '') === $periodKey ? 'is-active' : '' }}" data-group-pane="driver-board" data-period="{{ $periodKey }}">
                                @if($items->isEmpty())
                                    <div class="ops-empty">No driver leaderboard data available for this period.</div>
                                @else
                                    <div class="ops-board__wrap">
                                        <div class="ops-board__podium">
                                            @if($second)
                                                <div class="ops-podium__item ops-podium__item--second">
                                                    <img src="{{ $second['avatar'] }}" alt="{{ $second['name'] }}" class="ops-podium__avatar">
                                                    <span class="ops-podium__metric">{{ $second['metric_total'] }}</span>
                                                    <span class="ops-podium__label">Trips</span>
                                                    <span class="ops-podium__name">{{ $second['name'] }}</span>
                                                    @if(($second['rating_count'] ?? 0) > 0)
                                                        <span class="ops-podium__rating">{{ number_format((float) $second['rating_avg'], 1) }} rating</span>
                                                    @endif
                                                    <span class="ops-podium__rank">2</span>
                                                </div>
                                            @endif
                                            @if($first)
                                                <div class="ops-podium__item ops-podium__item--first">
                                                    <img src="{{ $first['avatar'] }}" alt="{{ $first['name'] }}" class="ops-podium__avatar">
                                                    <span class="ops-podium__metric">{{ $first['metric_total'] }}</span>
                                                    <span class="ops-podium__label">Trips</span>
                                                    <span class="ops-podium__name">{{ $first['name'] }}</span>
                                                    @if(($first['rating_count'] ?? 0) > 0)
                                                        <span class="ops-podium__rating">{{ number_format((float) $first['rating_avg'], 1) }} rating</span>
                                                    @endif
                                                    <span class="ops-podium__rank">1</span>
                                                </div>
                                            @endif
                                            @if($third)
                                                <div class="ops-podium__item ops-podium__item--third">
                                                    <img src="{{ $third['avatar'] }}" alt="{{ $third['name'] }}" class="ops-podium__avatar">
                                                    <span class="ops-podium__metric">{{ $third['metric_total'] }}</span>
                                                    <span class="ops-podium__label">Trips</span>
                                                    <span class="ops-podium__name">{{ $third['name'] }}</span>
                                                    @if(($third['rating_count'] ?? 0) > 0)
                                                        <span class="ops-podium__rating">{{ number_format((float) $third['rating_avg'], 1) }} rating</span>
                                                    @endif
                                                    <span class="ops-podium__rank">3</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ops-board__list">
                                            @foreach($items as $index => $item)
                                                <div class="ops-board__row">
                                                    <div class="ops-board__user">
                                                        <span class="ops-board__metric">{{ $index + 1 }}.</span>
                                                        <img src="{{ $item['avatar'] }}" alt="{{ $item['name'] }}" class="ops-board__thumb">
                                                        <div>
                                                            <div class="ops-board__name">{{ $item['name'] }}</div>
                                                            <div class="ops-board__sub">
                                                                {{ number_format((float) $item['earnings_total'], 2) }} {{ LOGIN_USER_TYPE == 'company' ? 'earned' : 'payout' }}
                                                                @if(($item['rating_count'] ?? 0) > 0)
                                                                    • {{ number_format((float) $item['rating_avg'], 1) }} from {{ $item['rating_count'] }} ratings
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ops-board__metric">{{ $item['metric_total'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ops-card card">
                    <div class="ops-card__head">
                        <div>
                            <h3 class="ops-card__title">Recent transactions</h3>
                            <span class="ops-card__caption">Last {{ count($recentTransactions) }} transactions this month</span>
                        </div>
                    </div>
                    <div class="ops-card__body">
                        @forelse($recentTransactions as $transaction)
                            <div class="ops-feed__item">
                                <span class="ops-feed__dot">
                                    <span class="ops-feed__avatars">
                                        <img src="{{ $transaction['rider_avatar'] ?? url('images/user.jpeg') }}" alt="{{ $transaction['rider_name'] ?? 'Rider' }}" class="ops-feed__avatar">
                                        <img src="{{ $transaction['driver_avatar'] ?? url('images/user.jpeg') }}" alt="{{ $transaction['driver_name'] ?? 'Driver' }}" class="ops-feed__avatar">
                                    </span>
                                </span>
                                <div class="ops-feed__content">
                                    <div class="ops-feed__title">
                                        {{ $currencySymbol }} {{ number_format((float) ($transaction['amount'] ?? 0), 2) }} {{ $transaction['title'] ?? 'Transaction' }}
                                    </div>
                                    <div class="ops-feed__meta">
                                        {{ $transaction['reference'] ?? '' }}
                                        @if(!empty($transaction['meta']))
                                            • {{ $transaction['meta'] }}
                                        @endif
                                        • {{ $transaction['time'] ?? '' }}
                                    </div>
                                    <span class="ops-feed__badge ops-badge--{{ in_array(($transaction['status'] ?? ''), ['Completed', 'Paid']) ? 'success' : 'warning' }}">
                                        {{ $transaction['status'] ?? 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="ops-empty">No recent transactions found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="ops-card ops-board card">
                    <div class="ops-card__head">
                        <div>
                            <h3 class="ops-card__title">Leader Board</h3>
                            <span class="ops-card__caption">Customer trip and rating ranking</span>
                        </div>
                        <div class="ops-board__tabs">
                            @foreach(($customerBoard['periods'] ?? []) as $periodKey => $periodData)
                                <button type="button" class="ops-board__tab js-board-tab {{ ($customerBoard['default_period'] ?? '') === $periodKey ? 'is-active' : '' }}" data-group="customer-board" data-period="{{ $periodKey }}">
                                    {{ $periodData['label'] ?? ucfirst(str_replace('_', ' ', $periodKey)) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="ops-card__body">
                        @foreach(($customerBoard['periods'] ?? []) as $periodKey => $periodData)
                            @php
                                $items = collect($periodData['items'] ?? []);
                                $first = $items->get(0);
                                $second = $items->get(1);
                                $third = $items->get(2);
                            @endphp
                            <div class="ops-board__pane {{ ($customerBoard['default_period'] ?? '') === $periodKey ? 'is-active' : '' }}" data-group-pane="customer-board" data-period="{{ $periodKey }}">
                                @if($items->isEmpty())
                                    <div class="ops-empty">No customer leaderboard data available for this period.</div>
                                @else
                                    <div class="ops-board__wrap">
                                        <div class="ops-board__podium">
                                            @if($second)
                                                <div class="ops-podium__item ops-podium__item--second">
                                                    <img src="{{ $second['avatar'] }}" alt="{{ $second['name'] }}" class="ops-podium__avatar">
                                                    <span class="ops-podium__metric">{{ number_format((float) $second['rating_avg'], 1) }}</span>
                                                    <span class="ops-podium__label">{{ $second['rating_count'] }} ratings</span>
                                                    <span class="ops-podium__name">{{ $second['name'] }}</span>
                                                    <span class="ops-podium__rating">{{ $second['metric_total'] }} trips</span>
                                                    <span class="ops-podium__rank">2</span>
                                                </div>
                                            @endif
                                            @if($first)
                                                <div class="ops-podium__item ops-podium__item--first">
                                                    <img src="{{ $first['avatar'] }}" alt="{{ $first['name'] }}" class="ops-podium__avatar">
                                                    <span class="ops-podium__metric">{{ number_format((float) $first['rating_avg'], 1) }}</span>
                                                    <span class="ops-podium__label">{{ $first['rating_count'] }} ratings</span>
                                                    <span class="ops-podium__name">{{ $first['name'] }}</span>
                                                    <span class="ops-podium__rating">{{ $first['metric_total'] }} trips</span>
                                                    <span class="ops-podium__rank">1</span>
                                                </div>
                                            @endif
                                            @if($third)
                                                <div class="ops-podium__item ops-podium__item--third">
                                                    <img src="{{ $third['avatar'] }}" alt="{{ $third['name'] }}" class="ops-podium__avatar">
                                                    <span class="ops-podium__metric">{{ number_format((float) $third['rating_avg'], 1) }}</span>
                                                    <span class="ops-podium__label">{{ $third['rating_count'] }} ratings</span>
                                                    <span class="ops-podium__name">{{ $third['name'] }}</span>
                                                    <span class="ops-podium__rating">{{ $third['metric_total'] }} trips</span>
                                                    <span class="ops-podium__rank">3</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ops-board__list">
                                            @foreach($items as $index => $item)
                                                <div class="ops-board__row">
                                                    <div class="ops-board__user">
                                                        <span class="ops-board__metric">{{ $index + 1 }}.</span>
                                                        <img src="{{ $item['avatar'] }}" alt="{{ $item['name'] }}" class="ops-board__thumb">
                                                        <div>
                                                            <div class="ops-board__name">{{ $item['name'] }}</div>
                                                            <div class="ops-board__sub">
                                                                {{ $item['metric_total'] }} trips
                                                                @if(($item['rating_count'] ?? 0) > 0)
                                                                    • {{ number_format((float) $item['rating_avg'], 1) }} from {{ $item['rating_count'] }} ratings
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ops-board__metric">{{ $item['metric_total'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ops-card card">
                    <div class="ops-card__head">
                        <div>
                            <h3 class="ops-card__title">Recent Trips Activity</h3>
                            <span class="ops-card__caption">Latest trip updates</span>
                        </div>
                    </div>
                    <div class="ops-card__body">
                        @forelse($recentTripActivity as $activity)
                            <div class="ops-activity__row">
                                <div class="ops-activity__icon">
                                    @if(!empty($activity['vehicle_image']))
                                        <img src="{{ $activity['vehicle_image'] }}" alt="{{ $activity['vehicle'] ?? 'Vehicle Type' }}" class="ops-activity__vehicle-image">
                                    @else
                                        <i class="ri-car-line"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="ops-activity__title">Trip# {{ $activity['trip_id'] }}</div>
                                    <div class="ops-activity__meta">{{ $activity['time'] }} • {{ $activity['vehicle'] }}</div>
                                </div>
                                <div class="ops-activity__status">
                                    <span class="ops-feed__badge ops-badge--{{ $activity['tone'] }}">{{ $activity['status'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="ops-empty">No recent trip activity found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="ops-floating-tooltip" class="ops-floating-tooltip"></div>
@endsection

@push('scripts')
    <script>
        window.opsDashboardEarnings = @json($earningsChart['periods'] ?? []);

        $(function () {
            var $floatingTooltip = $('#ops-floating-tooltip');

            function showFloatingTooltip(html, event) {
                if (!$floatingTooltip.length) {
                    return;
                }

                $floatingTooltip.html(html).addClass('is-visible');

                var offset = 18;
                var tooltipWidth = $floatingTooltip.outerWidth();
                var tooltipHeight = $floatingTooltip.outerHeight();
                var left = event.pageX + offset;
                var top = event.pageY - tooltipHeight - offset;

                if (left + tooltipWidth > $(window).width() - 10) {
                    left = event.pageX - tooltipWidth - offset;
                }

                if (top < $(window).scrollTop() + 10) {
                    top = event.pageY + offset;
                }

                $floatingTooltip.css({
                    left: left + 'px',
                    top: top + 'px'
                });
            }

            function hideFloatingTooltip() {
                $floatingTooltip.removeClass('is-visible').empty();
            }

            $('.js-pane-select').on('change', function () {
                var target = $(this).data('target');
                var selected = $(this).val();
                $('[data-pane-group="' + target + '"]').removeClass('is-active');
                $('[data-pane-group="' + target + '"][data-pane="' + selected + '"]').addClass('is-active');
            });

            $(document).on('mouseenter mousemove', '.js-zone-tooltip', function (event) {
                var $zone = $(this);
                var html = ''
                    + '<div class="ops-floating-tooltip__title">' + ($zone.data('zone-name') || 'Zone') + '</div>'
                    + '<div class="ops-floating-tooltip__line">On trip: ' + ($zone.data('on-trip') || 0) + '</div>'
                    + '<div class="ops-floating-tooltip__line">Completed: ' + ($zone.data('completed') || 0) + '</div>'
                    + '<div class="ops-floating-tooltip__line">Cancelled: ' + ($zone.data('cancelled') || 0) + '</div>'
                    + '<div class="ops-floating-tooltip__line">Total: ' + ($zone.data('total') || 0) + '</div>';

                showFloatingTooltip(html, event);
            });

            $(document).on('mouseleave', '.js-zone-tooltip', function () {
                hideFloatingTooltip();
            });

            $('.js-board-tab').on('click', function () {
                var group = $(this).data('group');
                var period = $(this).data('period');

                $('.js-board-tab[data-group="' + group + '"]').removeClass('is-active');
                $(this).addClass('is-active');

                $('[data-group-pane="' + group + '"]').removeClass('is-active');
                $('[data-group-pane="' + group + '"][data-period="' + period + '"]').addClass('is-active');
            });

            var earningsChartInstance = null;

            function renderEarningsChart(periodKey) {
                if (!window.opsDashboardEarnings || !window.opsDashboardEarnings[periodKey]) {
                    return;
                }

                var periodPayload = window.opsDashboardEarnings[periodKey].data || {};
                var labels = periodPayload.labels || [];
                var gross = periodPayload.gross || [];
                var operator = periodPayload.operator || [];
                var chartData = [];
                var isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                var chartLineColors = isDarkMode
                    ? ['#55b9ad', '#82a9d6']
                    : [LalaColors.as35c5a8, LalaColors.as5c97ff];

                for (var i = 0; i < labels.length; i++) {
                    chartData.push({
                        label: labels[i],
                        gross: gross[i] || 0,
                        operator: operator[i] || 0
                    });
                }

                $('#ops-earnings-chart').empty();

                earningsChartInstance = new Morris.Line({
                    element: 'ops-earnings-chart',
                    resize: true,
                    data: chartData,
                    xkey: 'label',
                    ykeys: ['gross', 'operator'],
                    labels: ['Gross Bookings', @json($earningsChart['series_title'] ?? 'Earnings')],
                    lineColors: chartLineColors,
                    lineWidth: 3,
                    pointSize: 3,
                    pointFillColors: chartLineColors,
                    pointStrokeColors: chartLineColors,
                    hideHover: false,
                    gridTextColor: isDarkMode ? '#93a4b5' : LalaColors.as658098,
                    gridLineColor: isDarkMode ? '#2b3d4b' : LalaColors.asEdf3f8,
                    parseTime: false,
                    hoverCallback: function (index, options, content, row) {
                        return '<div class="morris-hover-row-label">' + row.label + '</div>'
                            + '<div class="morris-hover-point">Gross Bookings: {{ $currencySymbol }}' + Number(row.gross || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</div>'
                            + '<div class="morris-hover-point">{{ $earningsChart['series_title'] ?? 'Earnings' }}: {{ $currencySymbol }}' + Number(row.operator || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</div>';
                    }
                });
            }

            $('#earnings-period-switch').on('change', function () {
                renderEarningsChart($(this).val());
            });

            renderEarningsChart($('#earnings-period-switch').val());

            if (window.MutationObserver) {
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'data-bs-theme') {
                            renderEarningsChart($('#earnings-period-switch').val());
                        }
                    });
                }).observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['data-bs-theme']
                });
            }
        });
    </script>
@endpush
