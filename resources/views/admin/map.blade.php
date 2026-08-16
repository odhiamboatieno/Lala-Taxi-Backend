@extends('admin.template')

@section('title-sub', __('messages.admin.manage_map_page.fleet_view'))
@section('pagetitle', __('messages.admin.manage_map_page.fleet_view'))

@section('css')
<style>
    .fleet-live-card {
        border-radius: 18px;
        overflow: hidden;
    }
    .fleet-live-card .card-header {
        background: transparent;
        padding: 22px 24px;
        border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
    }
    .fleet-live-tabs {
        display: flex;
        gap: 22px;
        align-items: center;
        flex-wrap: wrap;
    }
    .fleet-live-tab {
        color: var(--color-as-23395d);
        font-weight: 600;
        padding: 0 0 10px;
        border-bottom: 2px solid transparent;
        display: inline-flex;
        gap: 8px;
        align-items: center;
    }
    .fleet-live-tab.active {
        color: var(--color-as-27b7a8);
        border-color: var(--color-as-27b7a8);
    }
    .fleet-live-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        min-height: 620px;
        align-items: start;
    }
    .fleet-side-panel {
        border-right: 1px solid var(--color-as-rgba-16-24-40-0-08);
        padding: 24px 0 0;
        background: var(--color-as-rgba-248-250-252-0-46);
        position: sticky;
        top: 96px;
        align-self: start;
        height: calc(100vh - 120px);
        min-height: 620px;
        overflow: hidden;
    }
    .fleet-panel-inner {
        padding: 0 20px 20px;
        height: 100%;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
    .fleet-panel-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--color-as-23395d);
        margin-bottom: 14px;
    }
    .fleet-search {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
    }
    .fleet-search .form-control {
        border-radius: 10px;
        height: 40px;
    }
    .fleet-search .btn {
        min-width: 88px;
        border-radius: 10px;
    }
    .fleet-list {
        flex: 1 1 auto;
        min-height: 0;
        margin: 0 -20px;
        padding: 0 20px;
    }
    .fleet-list-item {
        border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
        padding: 16px 0;
        cursor: pointer;
    }
    .fleet-list-item:last-child {
        border-bottom: 0;
    }
    .fleet-list-item.active .fleet-list-name {
        color: var(--color-as-27b7a8);
    }
    .fleet-list-name {
        font-size: 17px;
        font-weight: 700;
        color: var(--color-as-23395d);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .fleet-dot {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        background: var(--color-as-414b5f);
        flex: 0 0 auto;
    }
    .fleet-dot.on-trip {
        background: var(--color-as-ff9f43);
    }
    .fleet-dot.idle {
        background: var(--color-as-27b7a8);
    }
    .fleet-dot.alert {
        background: var(--color-as-ff5c5c);
    }
    .fleet-list-meta,
    .fleet-list-submeta {
        font-size: 14px;
        color: var(--color-as-4a5568);
        line-height: 1.55;
    }
    .fleet-map-column {
        padding: 26px 18px 18px;
        position: sticky;
        top: 96px;
        align-self: start;
    }
    .fleet-map-toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }
    .fleet-map-toolbar .form-control,
    .fleet-map-toolbar .form-select,
    .fleet-map-toolbar select {
        border-radius: 10px;
        height: 44px;
    }
    .fleet-map-shell {
        position: relative;
        min-height: 560px;
        border-radius: 14px;
        overflow: hidden;
        background: var(--color-as-f4f7fb);
        border: 1px solid var(--color-as-rgba-16-24-40-0-08);
    }
    #fleet-map {
        width: 100%;
        min-height: 560px;
    }
    .fleet-map-actions {
        position: absolute;
        right: 12px;
        top: 12px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .fleet-map-action {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 0;
        background: var(--color-white);
        box-shadow: 0 10px 24px var(--color-as-rgba-15-23-42-0-14);
        color: var(--color-as-23395d);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fleet-map-action svg,
    .fleet-back-link svg {
        width: 18px;
        height: 18px;
        stroke: currentColor;
    }
    .fleet-alert-fab {
        position: absolute;
        right: 18px;
        top: 64px;
        z-index: 11;
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: 0;
        background: var(--color-white);
        box-shadow: 0 14px 30px var(--color-as-rgba-15-23-42-0-16);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fleet-alert-fab .badge {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 24px;
        height: 24px;
        border-radius: 999px;
        font-size: 11px;
        line-height: 24px;
        background: var(--color-as-ff5c5c);
    }
    .fleet-map-selection {
        display: none;
        margin-bottom: 16px;
    }
    .fleet-map-selection.active {
        display: block;
    }
    .fleet-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--color-as-23395d);
        font-weight: 600;
        margin-bottom: 18px;
    }
    .fleet-profile {
        display: flex;
        gap: 12px;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
        margin-bottom: 16px;
    }
    .fleet-profile img {
        width: 72px;
        height: 72px;
        border-radius: 10px;
        object-fit: cover;
    }
    .fleet-profile-name {
        font-size: 17px;
        font-weight: 700;
        color: var(--color-as-23395d);
        margin-bottom: 6px;
    }
    .fleet-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--color-as-dff7f1);
        color: var(--color-as-27b7a8);
        font-size: 12px;
        font-weight: 700;
    }
    .fleet-detail-card {
        border: 1px solid var(--color-as-rgba-16-24-40-0-08);
        border-radius: 12px;
        padding: 14px 14px 10px;
        margin-bottom: 16px;
        background: var(--color-white);
    }
    .fleet-detail-card h6 {
        font-size: 15px;
        font-weight: 700;
        color: var(--color-as-23395d);
        margin-bottom: 12px;
    }
    .fleet-detail-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px 12px;
    }
    .fleet-detail-grid div {
        font-size: 14px;
        color: var(--color-as-4a5568);
    }
    .fleet-alert-card {
        background: var(--color-as-fff5f5);
        border-color: var(--color-as-rgba-255-92-92-0-15);
    }
    .fleet-alert-card p {
        font-size: 14px;
        color: var(--color-as-7a3f3f);
        margin-bottom: 12px;
    }
    .fleet-empty {
        padding: 22px 16px;
        border-radius: 12px;
        background: var(--color-as-rgba-255-255-255-0-7);
        text-align: center;
        color: var(--color-as-64748b);
        font-size: 14px;
    }
    .fleet-map-updated {
        color: var(--color-as-64748b);
        font-size: 13px;
        margin-top: 10px;
        text-align: right;
    }
    .fleet-map-popup {
        min-width: 180px;
    }
    .fleet-map-popup__title {
        font-weight: 700;
        color: var(--color-as-23395d);
        margin-bottom: 6px;
    }
    .fleet-map-popup__meta {
        font-size: 13px;
        color: var(--color-as-64748b);
        line-height: 1.45;
    }
    @media (max-width: 1200px) {
        .fleet-live-layout {
            grid-template-columns: 1fr;
        }
        .fleet-side-panel {
            border-right: 0;
            border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
            position: static;
            top: auto;
            align-self: auto;
            height: auto;
            min-height: 0;
            overflow: visible;
        }
        .fleet-map-column {
            position: static;
            top: auto;
        }
    }
    @media (max-width: 768px) {
        .fleet-map-toolbar {
            flex-wrap: wrap;
        }
        .fleet-search {
            flex-wrap: wrap;
        }
        .fleet-map-column {
            padding: 16px;
        }
        .fleet-panel-inner {
            padding: 0 16px 16px;
        }
    }
</style>
@endsection

@section('main')
@php
    $listTitle = match($mode) {
        'on_trip' => __('messages.admin.manage_map_page.on_trip_driver_list'),
        'idle' => __('messages.admin.manage_map_page.idle_driver_list'),
        'customers' => __('messages.admin.manage_map_page.customer_list'),
        'alerts' => __('messages.admin.manage_map_page.safety_alerts'),
        default => __('messages.admin.manage_map_page.driver_list'),
    };
@endphp
<div id="layout-wrapper" class="fleet-map-page" data-mode="{{ $mode }}">
    <div class="row">
        <div class="col-12">
            <div class="card fleet-live-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between gap-3 align-items-start flex-wrap">
                        <div>
                            <h3 class="mb-1" style="font-weight:700;color:var(--color-as-23395d);">{{ $fleetPayload['title'] }}</h3>
                            <p class="text-muted mb-0">{{ $fleetPayload['subtitle'] }}</p>
                        </div>
                        <div style="min-width:220px;">
                            <select class="form-control" id="fleet-location-filter">
                                <option value="0">{{ __('messages.admin.manage_map_page.all_over_the_world') }}</option>
                                @foreach($fleetPayload['locations'] as $location)
                                    <option value="{{ $location['id'] }}" {{ (int) $fleetPayload['selected_location_id'] === (int) $location['id'] ? 'selected' : '' }}>
                                        {{ $location['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap px-4 pt-4 pb-0">
                        <div class="fleet-live-tabs">
                            @foreach($fleetPayload['tabs'] as $tabKey => $tab)
                                <a href="{{ $tab['url'] }}" class="fleet-live-tab {{ $mode === $tabKey ? 'active' : '' }}">
                                    <span>{{ $tab['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="fleet-live-layout">
                        <aside class="fleet-side-panel">
                            <div class="fleet-panel-inner">
                                <div id="fleet-selection-view" class="fleet-map-selection"></div>

                                <div id="fleet-list-view">
                                    <div class="fleet-panel-title">{{ $listTitle }}</div>
                                    <div class="fleet-search">
                                        <input type="text" id="fleet-list-search" class="form-control" placeholder="{{ __('messages.admin.manage_map_page.search') }} {{ $mode === 'customers' ? __('messages.admin.manage_map_page.customer') : ($mode === 'alerts' ? __('messages.admin.manage_map_page.alert') : __('messages.admin.manage_map_page.driver')) }}">
                                        <button type="button" class="btn btn-primary" id="fleet-list-search-btn">{{ __('messages.admin.manage_map_page.search') }}</button>
                                    </div>
                                    <div class="fleet-list" id="fleet-list">
                                        @forelse($fleetPayload['items'] as $item)
                                            @php
                                                $dotClass = $item['type'] === 'alert'
                                                    ? 'alert'
                                                    : (($item['status_key'] ?? '') === 'on_trip'
                                                        ? 'on-trip'
                                                        : ((($item['status_key'] ?? '') === 'idle') ? 'idle' : ''));
                                                $title = $item['name'] ?? $item['title'] ?? '-';
                                                $meta1 = $item['type'] === 'alert'
                                                    ? ($item['description'] ?? __('messages.admin.manage_map_page.open_safety_alert'))
                                                    : __('messages.admin.manage_map_page.phone').' : '.($item['hidden_phone'] ?? $item['phone'] ?? '-');
                                                $meta2 = '';
                                                if (($item['type'] ?? '') === 'driver') {
                                                    $meta2 = __('messages.admin.manage_map_page.vehicle_no').' : '.($item['vehicle_number'] ?? '-').' | '.__('messages.admin.manage_map_page.model').' : '.($item['vehicle_model'] ?? '-');
                                                } elseif (($item['type'] ?? '') === 'customer' && !empty($item['latest_trip'])) {
                                                    $meta2 = __('messages.admin.manage_map_page.latest_trip').' : '.($item['latest_trip']['status'] ?? '-').' | '.__('messages.admin.manage_map_page.total_trips').' : '.($item['trip_count'] ?? 0);
                                                } elseif (($item['type'] ?? '') === 'alert') {
                                                    $meta2 = !empty($item['trip_ref']) ? __('messages.admin.manage_map_page.trip').' : '.$item['trip_ref'] : __('messages.admin.manage_map_page.open_safety_event');
                                                }
                                            @endphp
                                            <div class="fleet-list-item" data-id="{{ $item['id'] }}">
                                                <div class="fleet-list-name">
                                                    <span class="fleet-dot {{ $dotClass }}"></span>
                                                    <span>{{ $title }}</span>
                                                </div>
                                                <div class="fleet-list-meta">{{ $meta1 }}</div>
                                                @if($meta2 !== '')
                                                    <div class="fleet-list-submeta">{{ $meta2 }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="fleet-empty">{{ __('messages.admin.manage_map_page.no_map_records_found') }}</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </aside>

                        <section class="fleet-map-column">
                            <div class="fleet-map-toolbar">
                                <input type="text" id="fleet-map-search" class="form-control" placeholder="{{ __('messages.admin.manage_map_page.search_for_location') }}">
                                <button type="button" class="fleet-map-action" id="fleet-map-fit" title="{{ __('messages.admin.manage_map_page.fit_all_markers') }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M8 3H3v5"></path>
                                        <path d="M3 3l6 6"></path>
                                        <path d="M16 3h5v5"></path>
                                        <path d="M21 3l-6 6"></path>
                                        <path d="M8 21H3v-5"></path>
                                        <path d="M3 21l6-6"></path>
                                        <path d="M16 21h5v-5"></path>
                                        <path d="M21 21l-6-6"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="fleet-map-shell">
                                <div id="fleet-map"></div>
                                <div class="fleet-map-actions">
                                    <button type="button" class="fleet-map-action" id="fleet-map-fullscreen" title="{{ __('messages.admin.manage_map_page.toggle_full_screen') }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M9 3H3v6"></path>
                                            <path d="M3 3l7 7"></path>
                                            <path d="M15 3h6v6"></path>
                                            <path d="M21 3l-7 7"></path>
                                            <path d="M9 21H3v-6"></path>
                                            <path d="M3 21l7-7"></path>
                                            <path d="M15 21h6v-6"></path>
                                            <path d="M21 21l-7-7"></path>
                                        </svg>
                                    </button>
                                </div>
                                <a href="{{ $fleetPayload['alerts_url'] }}" class="fleet-alert-fab" title="{{ __('messages.admin.manage_map_page.safety_alerts') }}">
                                    <img src="{{ url('images/icon/map-pin-red.png') }}" alt="{{ __('messages.admin.manage_map_page.alerts') }}" style="width:24px;height:24px;">
                                    @if($fleetPayload['alerts_count'] > 0)
                                        <span class="badge">{{ $fleetPayload['alerts_count'] }}</span>
                                    @endif
                                </a>
                            </div>
                            <div class="fleet-map-updated">{{ __('messages.admin.manage_map_page.last_updated') }} {{ $fleetPayload['updated_at'] }}</div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.fleetMapPayload = @json($fleetPayload);
</script>
@endsection
