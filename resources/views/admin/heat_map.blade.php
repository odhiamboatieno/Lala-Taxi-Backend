@extends('admin.template')

@section('title-sub', __('messages.admin.heat_map_page.title'))
@section('pagetitle', __('messages.admin.heat_map_page.pagetitle'))

@section('css')
<style>
    .heatmap-card {
        border-radius: 0;
        overflow: hidden;
    }
    .heatmap-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        align-items: center;
    }
    .heatmap-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--color-as-23395d);
        margin-bottom: 4px;
    }
    .heatmap-subtitle {
        color: var(--color-as-64748b);
        margin: 0;
    }
    .heatmap-tabs {
        display: inline-flex;
        background: var(--color-as-f3f4f6);
        padding: 4px;
        border-radius: 0;
        gap: 4px;
    }
    .heatmap-tab {
        padding: 10px 22px;
        border-radius: 0;
        font-weight: 700;
        color: var(--color-as-334155);
    }
    .heatmap-tab.active {
        background: var(--color-as-27b7a8);
        color: var(--color-white);
    }
    .heatmap-layout {
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        min-height: 620px;
        align-items: start;
    }
    .heatmap-side {
        border-right: 1px solid var(--color-as-rgba-16-24-40-0-08);
        background: var(--color-as-rgba-248-250-252-0-46);
        padding: 20px;
        position: sticky;
        top: 96px;
        align-self: start;
        height: calc(100vh - 120px);
        min-height: 620px;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .heatmap-main {
        padding: 20px;
        position: sticky;
        top: 96px;
        align-self: start;
    }
    .heatmap-side-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--color-as-23395d);
        margin-bottom: 14px;
    }
    .heatmap-search {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
    }
    .heatmap-search .form-control,
    .heatmap-search .btn,
    .heatmap-filter-stack .form-control,
    .heatmap-filter-stack .btn {
        border-radius: 0;
    }
    .heatmap-zone-list {
        display: flex;
        flex-direction: column;
        gap: 0;
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }
    .heatmap-zone-item {
        padding: 16px 0;
        border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
        cursor: pointer;
    }
    .heatmap-zone-item.active .heatmap-zone-name {
        color: var(--color-as-27b7a8);
    }
    .heatmap-zone-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--color-as-23395d);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }
    .heatmap-zone-check {
        width: 18px;
        height: 18px;
        border-radius: 0;
        background: var(--color-as-27b7a8);
        color: var(--color-white);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex: 0 0 auto;
    }
    .heatmap-zone-meta {
        color: var(--color-as-475569);
        font-size: 14px;
        padding-left: 28px;
    }
    .heatmap-range-shell {
        margin-bottom: 18px;
    }
    .heatmap-range-display {
        width: 100%;
        min-height: 46px;
        border: 1px solid var(--color-as-rgba-16-24-40-0-12);
        border-radius: 0;
        padding: 0 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        color: var(--color-as-64748b);
        background: var(--color-white);
    }
    .heatmap-range-panel {
        display: none;
        margin-top: 10px;
        border: 1px solid var(--color-as-rgba-16-24-40-0-12);
        border-radius: 0;
        background: var(--color-white);
        overflow: hidden;
        box-shadow: 0 18px 34px var(--color-as-rgba-15-23-42-0-12);
    }
    .heatmap-range-panel.active {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
    }
    .heatmap-range-presets {
        border-right: 1px solid var(--color-as-rgba-16-24-40-0-08);
        padding: 8px 0;
        background: var(--color-as-f8fafc);
    }
    .heatmap-range-preset {
        display: block;
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        padding: 10px 14px;
        color: var(--color-as-334155);
    }
    .heatmap-range-preset.active {
        background: var(--color-as-2f86c3);
        color: var(--color-white);
    }
    .heatmap-range-custom {
        padding: 14px;
    }
    .heatmap-range-custom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }
    .heatmap-range-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        align-items: center;
    }
    .heatmap-map-shell {
        position: relative;
        min-height: 540px;
        border-radius: 0;
        overflow: hidden;
        border: 1px solid var(--color-as-rgba-16-24-40-0-08);
        background: var(--color-as-f4f7fb);
    }
    .heatmap-map-toolbar {
        position: absolute;
        left: 14px;
        right: 62px;
        top: 12px;
        z-index: 9;
    }
    .heatmap-map-toolbar .form-control {
        border-radius: 0;
        height: 44px;
    }
    .heatmap-map-actions {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 9;
    }
    .heatmap-map-actions .btn {
        width: 42px;
        height: 42px;
        border-radius: 0;
        background: var(--color-white);
        border: 0;
        box-shadow: 0 10px 24px var(--color-as-rgba-15-23-42-0-14);
    }
    .heatmap-map-actions .btn svg,
    .heatmap-range-display svg,
    .heatmap-zone-check svg {
        width: 18px;
        height: 18px;
        stroke: currentColor;
    }
    #heatmap-overview {
        width: 100%;
        min-height: 540px;
    }
    .heatmap-compare-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 20px;
    }
    .heatmap-compare-card {
        border: 1px solid var(--color-as-rgba-16-24-40-0-08);
        border-radius: 0;
        padding: 0;
        overflow: hidden;
        background: var(--color-white);
    }
    .heatmap-compare-head {
        font-size: 14px;
        font-weight: 800;
        color: var(--color-as-23395d);
        padding: 12px 14px;
    }
    .heatmap-compare-map {
        width: 100%;
        min-height: 220px;
    }
    .heatmap-chart-card {
        border: 1px solid var(--color-as-rgba-16-24-40-0-08);
        border-radius: 0;
        background: var(--color-white);
    }
    .heatmap-chart-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 18px;
        border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
    }
    .heatmap-chart-head h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--color-as-23395d);
    }
    .heatmap-chart-box {
        padding: 14px 18px 22px;
    }
    #heatmap-compare-chart {
        min-height: 320px;
    }
    .heatmap-filter-stack {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 18px;
        margin-bottom: 18px;
        align-items: center;
    }
    .heatmap-filter-actions {
        display: inline-flex;
        gap: 8px;
    }
    .heatmap-filter-actions .btn {
        min-width: 130px;
        border-radius: 0;
    }
    /* Ensure the compare cards have visible maps */
    .heatmap-compare-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }
    .heatmap-compare-card {
        display: flex;
        flex-direction: column;
    }
    .heatmap-compare-map {
        width: 100%;
        height: 360px;
        min-height: 360px;
        background: var(--color-as-eef2f6);
        border-radius: 0;
        border-radius: 0;
    }
    /* Ensure the overview map has a height even before init */
    #heatmap-overview {
        width: 100%;
        height: calc(100vh - 280px);
        min-height: 540px;
        background: var(--color-as-eef2f6);
    }
    @media (max-width: 1200px) {
        .heatmap-layout {
            grid-template-columns: 1fr;
        }
        .heatmap-side {
            border-right: 0;
            border-bottom: 1px solid var(--color-as-rgba-16-24-40-0-08);
            position: static;
            top: auto;
            align-self: auto;
            height: auto;
            min-height: 0;
            overflow: visible;
        }
        .heatmap-compare-grid {
            grid-template-columns: 1fr;
        }
        .heatmap-main {
            position: static;
            top: auto;
        }
    }
    @media (max-width: 768px) {
        .heatmap-range-panel.active,
        .heatmap-filter-stack,
        .heatmap-range-custom-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('main')
<div id="layout-wrapper" class="heatmap-page" data-mode="{{ $mode }}">
    <div class="row">
        <div class="col-12">
            <div class="card heatmap-card">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom">
                        <div class="heatmap-header">
                            <div>
                                <div class="heatmap-title">{{ __('messages.admin.heat_map_page.title') }}</div>
                                <p class="heatmap-subtitle">{{ __('messages.admin.heat_map_page.subtitle') }}</p>
                            </div>
                            <div class="heatmap-tabs">
                                @foreach($heatMapPayload['tabs'] as $tabKey => $tab)
                                    <a href="{{ $tab['url'] }}" class="heatmap-tab {{ $mode === $tabKey ? 'active' : '' }}">{{ $tab['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="heatmap-layout">
                        <aside class="heatmap-side">
                            @if($mode === 'compare')
                                <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/heat-map/compare') }}">
                                    <div class="heatmap-side-title">{{ __('messages.admin.heat_map_page.filter') }}</div>
                                    <div class="heatmap-filter-stack" style="grid-template-columns:1fr;">
                                        <div>
                                            <label class="form-label">{{ __('messages.admin.heat_map_page.select_zone') }}</label>
                                            <select name="location_id" class="form-control">
                                                <option value="0">{{ __('messages.admin.heat_map_page.all_over_the_world') }}</option>
                                                @foreach($heatMapPayload['locations'] as $location)
                                                    <option value="{{ $location['id'] }}" {{ (int) $heatMapPayload['selected_location_id'] === (int) $location['id'] ? 'selected' : '' }}>
                                                        {{ $location['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label">{{ __('messages.admin.heat_map_page.select_time_frame') }}</label>
                                            <select name="range" class="form-control">
                                                <option value="all" {{ $heatMapPayload['range'] === 'all' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.all_time') }}</option>
                                                <option value="today" {{ $heatMapPayload['range'] === 'today' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.today') }}</option>
                                                <option value="yesterday" {{ $heatMapPayload['range'] === 'yesterday' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.yesterday') }}</option>
                                                <option value="7_days" {{ $heatMapPayload['range'] === '7_days' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.last_7_days') }}</option>
                                                <option value="30_days" {{ $heatMapPayload['range'] === '30_days' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.last_30_days') }}</option>
                                                <option value="this_month" {{ $heatMapPayload['range'] === 'this_month' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.this_month') }}</option>
                                                <option value="last_month" {{ $heatMapPayload['range'] === 'last_month' ? 'selected' : '' }}>{{ __('messages.admin.heat_map_page.last_month') }}</option>
                                            </select>
                                        </div>
                                        <div class="heatmap-filter-actions" style="justify-content:space-between;">
                                            <a href="{{ url(LOGIN_USER_TYPE.'/heat-map/compare') }}" class="btn btn-light">{{ __('messages.admin.heat_map_page.reset') }}</a>
                                            <button type="submit" class="btn btn-primary">{{ __('messages.admin.heat_map_page.submit') }}</button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/heat-map') }}" id="heatmap-overview-form">
                                    <div class="heatmap-range-shell">
                                        <div class="heatmap-range-display" id="heatmap-range-display">
                                            <span>{{ $heatMapPayload['range_label'] }}</span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                                <path d="M16 2v4"></path>
                                                <path d="M8 2v4"></path>
                                                <path d="M3 10h18"></path>
                                            </svg>
                                        </div>
                                        <div class="heatmap-range-panel" id="heatmap-range-panel">
                                            <div class="heatmap-range-presets">
                                                @php
                                                    $presets = [
                                                        'today' => __('messages.admin.heat_map_page.today'),
                                                        'yesterday' => __('messages.admin.heat_map_page.yesterday'),
                                                        '7_days' => __('messages.admin.heat_map_page.last_7_days'),
                                                        '30_days' => __('messages.admin.heat_map_page.last_30_days'),
                                                        'this_month' => __('messages.admin.heat_map_page.this_month'),
                                                        'last_month' => __('messages.admin.heat_map_page.last_month'),
                                                        'all' => __('messages.admin.heat_map_page.all_time'),
                                                    ];
                                                @endphp
                                                @foreach($presets as $presetKey => $presetLabel)
                                                    <button type="button" class="heatmap-range-preset {{ $heatMapPayload['range'] === $presetKey ? 'active' : '' }}" data-range="{{ $presetKey }}">{{ $presetLabel }}</button>
                                                @endforeach
                                            </div>
                                            <div class="heatmap-range-custom">
                                                <div class="heatmap-range-custom-grid">
                                                    <div>
                                                        <label class="form-label">{{ __('messages.admin.heat_map_page.from') }}</label>
                                                        <input type="date" class="form-control" name="from_date" id="heatmap-from-date" value="{{ $heatMapPayload['from_date'] }}">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">{{ __('messages.admin.heat_map_page.to') }}</label>
                                                        <input type="date" class="form-control" name="to_date" id="heatmap-to-date" value="{{ $heatMapPayload['to_date'] }}">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="range" id="heatmap-range-input" value="{{ $heatMapPayload['range'] }}">
                                                <input type="hidden" name="location_id" value="{{ $heatMapPayload['selected_location_id'] }}">
                                                <div class="heatmap-range-footer">
                                                    <button type="button" class="btn btn-light" id="heatmap-range-clear">{{ __('messages.admin.heat_map_page.clear') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ __('messages.admin.heat_map_page.apply') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="heatmap-side-title">{{ __('messages.admin.heat_map_page.zone_list') }}</div>
                                    <div class="heatmap-search">
                                        <input type="text" name="search" value="{{ $heatMapPayload['selected_zone_name'] }}" class="form-control" placeholder="{{ __('messages.admin.heat_map_page.search_here_by_zone_name') }}">
                                        <button type="submit" class="btn btn-primary">{{ __('messages.admin.heat_map_page.search') }}</button>
                                    </div>
                                </form>
                            @endif

                            <div class="heatmap-zone-list">
                                @foreach($heatMapPayload['zone_stats'] as $zone)
                                    <div class="heatmap-zone-item {{ (int) $heatMapPayload['selected_location_id'] === (int) $zone['id'] ? 'active' : '' }}" data-id="{{ $zone['id'] }}">
                                        <div class="heatmap-zone-name">
                                            <span class="heatmap-zone-check">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M20 6L9 17l-5-5"></path>
                                                </svg>
                                            </span>
                                            <span>{{ $zone['name'] }}{{ $zone['id'] > 0 ? ' #'.$zone['id'] : '' }}</span>
                                        </div>
                                        <div class="heatmap-zone-meta">{{ __('messages.admin.heat_map_page.ride_label') }} : {{ $zone['ride_count'] }} &nbsp; | &nbsp; {{ __('messages.admin.heat_map_page.parcel_label') }} : {{ $zone['parcel_count'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </aside>

                        <section class="heatmap-main">
                            @if($mode === 'compare')
                                <div class="heatmap-compare-grid">
                                    @foreach($heatMapPayload['compare']['years'] as $index => $yearItem)
                                        <div class="heatmap-compare-card">
                                            <div class="heatmap-compare-head">{{ $yearItem['label'] }}</div>
                                            <div class="heatmap-compare-map" id="heatmap-compare-map-{{ $index }}"></div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="heatmap-chart-card">
                                    <div class="heatmap-chart-head">
                                        <h5>{{ __('messages.admin.heat_map_page.trip_statistics') }}</h5>
                                        <div>{{ __('messages.admin.heat_map_page.total_trip') }}: {{ array_sum($heatMapPayload['compare']['chart']['rides']) + array_sum($heatMapPayload['compare']['chart']['parcels']) }}</div>
                                    </div>
                                    <div class="heatmap-chart-box">
                                        <div id="heatmap-compare-chart"></div>
                                    </div>
                                </div>
                            @else
                                <div class="heatmap-map-shell">
                                    <div class="heatmap-map-toolbar">
                                        <input type="text" id="heatmap-search-input" class="form-control" placeholder="{{ __('messages.admin.heat_map_page.search_for_location') }}">
                                    </div>
                                    <div class="heatmap-map-actions">
                                        <button type="button" class="btn" id="heatmap-fit-btn" title="{{ __('messages.admin.heat_map_page.fit_heat_map') }}">
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
                                    <div id="heatmap-overview"></div>
                                </div>
                            @endif
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ url('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    window.heatMapPayload = @json($heatMapPayload);
</script>
@endsection
