@extends('admin.template')

@php
    $isEdit = isset($editingLocation) && $editingLocation;
    $pageConfig = $locationPageConfig ?? [];
    $formAction = $pageConfig['form_action'] ?? ($isEdit ? url('admin/edit_location/'.$editingLocation->id) : url('admin/add_location'));
    $formMethod = $pageConfig['form_method'] ?? 'POST';
    $indexUrl = $pageConfig['index_url'] ?? url('admin/locations');
    $cancelUrl = $pageConfig['cancel_url'] ?? $indexUrl;
    $bulkExtraFareUrl = $pageConfig['bulk_extra_fare_url'] ?? route('admin.locations.bulk_extra_fare');
    $toggleExtraFareBaseUrl = $pageConfig['toggle_extra_fare_base_url'] ?? url('admin/locations/toggle-extra-fare');
    $toggleStatusBaseUrl = $pageConfig['toggle_status_base_url'] ?? url('admin/locations/toggle-status');
    $submitLabel = $pageConfig['submit_label'] ?? ($isEdit ? 'Update' : 'Submit');
    $cancelLabel = $pageConfig['cancel_label'] ?? 'Reset';
    $titleSub = $titleSub ?? ($isEdit ? 'Edit Zone' : 'Zone Setup');
    $pageTitle = $pageTitle ?? 'Zone Setup';
    $isSaasServiceAreaPage = $isSaasServiceAreaPage ?? false;
@endphp

@section('title-sub', $titleSub)
@section('pagetitle', $pageTitle)

@section('css')
<style>
    #map {
        height: 360px;
        width: 100%;
        border-radius: 1rem;
        border: 1px solid var(--color-as-rgba-20-33-61-0-08);
    }
    .controls.hide {
        display: none;
    }
    .zone-guide-media {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid var(--color-as-rgba-20-33-61-0-08);
        overflow: hidden;
        background: var(--color-as-f8fbff);
    }
    .zone-guide-media img {
        width: 100%;
        display: block;
    }
    .zone-action-menu .dropdown-menu {
        min-width: 220px;
        border-radius: 1rem;
        padding: .5rem;
    }
    .zone-extra-popover {
        position: absolute;
        right: 22rem;
        top: -7.75rem;
        z-index: 3;
        width: 225px;
        border-radius: 1rem;
        background: var(--color-white);
        border: 1px solid var(--color-as-rgba-20-33-61-0-12);
        box-shadow: 0 16px 40px var(--color-as-rgba-15-23-42-0-12);
        padding: 1rem 1.1rem;
    }
    .zone-extra-popover:after {
        content: "";
        position: absolute;
        bottom: -10px;
        right: 38px;
        border-width: 10px 10px 0 10px;
        border-style: solid;
        border-color: var(--color-white) transparent transparent transparent;
    }
    .zone-extra-popover:before {
        content: "";
        position: absolute;
        bottom: -11px;
        right: 38px;
        border-width: 11px 11px 0 11px;
        border-style: solid;
        border-color: var(--color-as-rgba-20-33-61-0-12) transparent transparent transparent;
    }
    .zone-status-switch {
        min-width: 42px;
    }
    .map-canvas-wrap .gm-style #pac-input.controls {
        position: static !important;
        width: min(420px, calc(100vw - 96px)) !important;
        max-width: calc(100vw - 96px);
        margin: 10px 0 0 0;
        box-shadow: 0 2px 6px var(--color-as-rgba-0-0-0-0-18);
        z-index: 1;
    }
    .map-canvas-wrap {
        position: relative;
    }
    #draw-start-btn .bi-bezier2 {
        font-size: 1.1rem;
    }
</style>
@endsection

@section('main')
<div id="layout-wrapper" data-coords="{{ json_encode($formatted_coords) }}">
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['url' => $formAction, 'class' => 'row g-4']) !!}
                        @if(strtoupper($formMethod) !== 'POST')
                            @method($formMethod)
                        @endif
                        <div class="col-xl-5">
                            <div class="card h-100 border shadow-none">
                                <div class="card-body">
                                    <h4 class="mb-4">{{ $isEdit ? 'Zone Setup' : 'Zone Setup' }}</h4>
                                    <div class="mb-4">
                                        <h5 class="text-uppercase text-success mb-4">Instructions</h5>
                                        <p class="fs-5 mb-4">Create zone by click on map and connect the dots together</p>

                                        <div class="d-flex gap-3 mb-4">
                                            <div class="border rounded p-2"><i class="bi bi-hand-index-thumb fs-3"></i></div>
                                            <div class="fs-5">Use this to drag map to find proper area</div>
                                        </div>
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="border rounded p-2"><i class="bi bi-bezier2 fs-3"></i></div>
                                            <div class="fs-5">Click this icon to start pin points in the map and connect them to draw a zone . Minimum 3 points required</div>
                                        </div>

                                        <div class="zone-guide-media">
                                            <img src="{{ $instructionGif }}" alt="Zone drawing instructions">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-7">
                            <div class="card h-100 border shadow-none">
                                <div class="card-body map-canvas-wrap">
                                    <div class="mb-4">
                                        <label class="form-label fs-5" for="input_name">Zone Name <em class="text-danger">*</em></label>
                                        {!! Form::text('name', old('name', $editingLocation->name ?? ''), ['class' => 'form-control form-control-lg', 'id' => 'input_name', 'placeholder' => 'Ex: London']) !!}
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    </div>

                                    @if($isSaasServiceAreaPage)
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label" for="input_country">Country</label>
                                                {!! Form::text('country', old('country', $editingLocation->country ?? ''), ['class' => 'form-control', 'id' => 'input_country', 'placeholder' => 'Country']) !!}
                                                <span class="text-danger">{{ $errors->first('country') }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="input_state">State</label>
                                                {!! Form::text('state', old('state', $editingLocation->state ?? ''), ['class' => 'form-control', 'id' => 'input_state', 'placeholder' => 'State']) !!}
                                                <span class="text-danger">{{ $errors->first('state') }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="input_city">City</label>
                                                {!! Form::text('city', old('city', $editingLocation->city ?? ''), ['class' => 'form-control', 'id' => 'input_city', 'placeholder' => 'City']) !!}
                                                <span class="text-danger">{{ $errors->first('city') }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="input_zone_name">Internal Zone Label</label>
                                                {!! Form::text('zone_name', old('zone_name', $editingLocation->zone_name ?? ''), ['class' => 'form-control', 'id' => 'input_zone_name', 'placeholder' => 'Internal label']) !!}
                                                <span class="text-danger">{{ $errors->first('zone_name') }}</span>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="pickup_enabled" value="1" id="pickup_enabled" {{ old('pickup_enabled', $editingLocation->pickup_enabled ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="pickup_enabled">Pickup enabled</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="dropoff_enabled" value="1" id="dropoff_enabled" {{ old('dropoff_enabled', $editingLocation->dropoff_enabled ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="dropoff_enabled">Dropoff enabled</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status', $editingLocation->status ?? 'Active'), ['class' => 'd-none', 'id' => 'input_status']) !!}
                                        {{ Form::hidden('coordinates', '', ['class' => 'coordinates', 'id' => 'coordinates-field']) }}
                                        <input id="pac-input" class="form-control controls mb-3" type="text" placeholder="Search here">
                                        <div id="map"></div>
                                        <span class="text-danger d-block mt-2">{{ $errors->first('coordinates') }}</span>
                                        <span class="text-danger d-block">{{ old('location_set') }}</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent d-flex justify-content-end gap-3">
                                    <a href="{{ $cancelUrl }}" class="btn btn-light px-4">{{ $cancelLabel }}</a>
                                    <button type="submit" class="btn btn-primary px-4" name="submit" value="submit">{{ $submitLabel }}</button>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="mb-1">Zone List</h4>
                        </div>
                        <div class="text-muted">Total Zones: <span class="text-success fw-semibold">{{ $zoneCount }}</span></div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ $indexUrl.'?status=all&search='.urlencode($search) }}" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-light' }}">All</a>
                        <a href="{{ $indexUrl.'?status=active&search='.urlencode($search) }}" class="btn {{ $statusFilter === 'active' ? 'btn-primary' : 'btn-light' }}">Active</a>
                        <a href="{{ $indexUrl.'?status=inactive&search='.urlencode($search) }}" class="btn {{ $statusFilter === 'inactive' ? 'btn-primary' : 'btn-light' }}">Inactive</a>
                    </div>

                    <div class="position-relative">
                        @if(($globalExtraFare['enabled'] ?? false) || ($globalExtraFare['consistency'] ?? '') === 'mixed')
                            <div class="zone-extra-popover">
                                <div class="fw-semibold mb-2">Extra Fare: {{ number_format((float) ($globalExtraFare['percentage'] ?? 0), 2) }}%</div>
                                <div class="fw-semibold">Reasons:</div>
                                <div class="text-muted">{{ $globalExtraFare['reason'] ?: (($globalExtraFare['consistency'] ?? '') === 'mixed' ? 'Mixed zone values' : 'Not set') }}</div>
                            </div>
                        @endif

                        <div class="card border shadow-none mb-0">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
                                    <form method="GET" action="{{ $indexUrl }}" class="d-flex flex-wrap gap-2">
                                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search here by zone name">
                                        <button class="btn btn-primary" type="submit">Search</button>
                                    </form>

                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#allZoneExtraFareModal">
                                            Apply For All Zone Extra Fare <i class="bi bi-info-circle"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-download me-1"></i> Download
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" id="download-csv"><i class="bi bi-filetype-csv me-2"></i>Download as CSV</a></li>
                                                <li><a class="dropdown-item" href="#" id="download-json"><i class="bi bi-filetype-json me-2"></i>Download as JSON</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Zone Name</th>
                                                <th>Trip Request Volume</th>
                                                @if($isSaasServiceAreaPage)
                                                    <th>Coverage</th>
                                                @endif
                                                <th>Extra Fare Status</th>
                                                <th>Extra Fare (%)</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($zoneRows as $zone)
                                                <tr>
                                                    <td>{{ $zone['index'] }}</td>
                                                    <td>{{ $zone['name'] }}</td>
                                                    <td>{{ $zone['trip_request_volume'] }}</td>
                                                    @if($isSaasServiceAreaPage)
                                                        <td>
                                                            <span class="badge {{ !empty($zone['pickup_enabled']) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">Pickup {{ !empty($zone['pickup_enabled']) ? 'On' : 'Off' }}</span>
                                                            <span class="badge {{ !empty($zone['dropoff_enabled']) ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">Dropoff {{ !empty($zone['dropoff_enabled']) ? 'On' : 'Off' }}</span>
                                                        </td>
                                                    @endif
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input zone-extra-toggle" type="checkbox" role="switch" data-id="{{ $zone['id'] }}" {{ $zone['extra_fare_enabled'] ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>{{ number_format((float) $zone['extra_fare_percentage'], 2) }}%</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input zone-status-switch" type="checkbox" role="switch" data-id="{{ $zone['id'] }}" {{ $zone['status'] === 'Active' ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown zone-action-menu">
                                                            <button class="btn btn-outline-info btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="{{ $zone['edit_url'] ?? url('admin/edit_location/'.$zone['id']) }}"><i class="bi bi-pencil me-2 text-success"></i>Zone Edit</a></li>
                                                                <li><a class="dropdown-item" href="{{ $zone['extra_fare_url'] ?? url('admin/location_extra_fare/'.$zone['id']) }}"><i class="bi bi-gear me-2 text-primary"></i>Extra Fare Setup</a></li>
                                                                <li>
                                                                    @if(!empty($zone['delete_method']))
                                                                        <form method="POST" action="{{ $zone['delete_url'] }}" onsubmit="return confirm('Delete this zone?')">
                                                                            @csrf
                                                                            @method($zone['delete_method'])
                                                                            <button type="submit" class="dropdown-item {{ $zone['can_delete'] ? '' : 'disabled' }}" {{ $zone['can_delete'] ? '' : 'disabled' }}>
                                                                                <i class="bi bi-trash me-2 text-danger"></i>Delete Zone
                                                                            </button>
                                                                        </form>
                                                                    @else
                                                                        <a class="dropdown-item {{ $zone['can_delete'] ? '' : 'disabled' }}" href="{{ $zone['can_delete'] ? ($zone['delete_url'] ?? url('admin/delete_location/'.$zone['id'])) : 'javascript:void(0)' }}">
                                                                            <i class="bi bi-trash me-2 text-danger"></i>Delete Zone
                                                                        </a>
                                                                    @endif
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $isSaasServiceAreaPage ? 8 : 7 }}" class="text-center text-muted py-5">No zones found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="allZoneExtraFareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                {!! Form::open(['url' => $bulkExtraFareUrl]) !!}
                    <input type="hidden" name="enabled" value="0">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title mb-1">Extra Fare Setup - All Zone</h4>
                            <p class="text-muted mb-0">Enabling this option will apply the extra fare to all rides and parcels across all Zones when the weather conditions change or heavy traffic.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-end mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" id="all_zone_extra_fare_enabled" {{ old('enabled', $globalExtraFare['enabled'] ?? false) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Extra Fare (%) <i class="bi bi-info-circle text-info"></i></label>
                                <input type="number" step="0.01" min="0" name="extra_fare_percentage" value="{{ old('extra_fare_percentage', $globalExtraFare['percentage'] ?? 0) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reasons For Extra Fare</label>
                                <input type="text" name="extra_fare_reason" value="{{ old('extra_fare_reason', $globalExtraFare['reason'] ?? '') }}" class="form-control">
                            </div>
                        </div>
                        <div class="card border shadow-none mb-0">
                            <div class="card-body">
                                <h5 class="mb-3">Instructions</h5>
                                <ol class="mb-0 ps-3">
                                    <li>When Allow Extra Fare Setup - All Zone, the Extra Fare(%) will be applicable for all the active zones.</li>
                                    <li>If want to set up separately for each zone, use the Extra Fare Setup action on the zone row.</li>
                                    <li>If want to update, you can return to the Zone Settings page and apply new values again.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Zone toggle switches (extra fare / status) ──
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.zone-extra-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            fetch("{{ $toggleExtraFareBaseUrl }}/" + this.dataset.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ enabled: this.checked ? 1 : 0 })
            }).catch(function () { window.location.reload(); });
        });
    });

    document.querySelectorAll('.zone-status-switch').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            fetch("{{ $toggleStatusBaseUrl }}/" + this.dataset.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ enabled: this.checked ? 1 : 0 })
            }).catch(function () { window.location.reload(); });
        });
    });
});

// ── Custom Polygon Drawing & Editing Manager ──
var locationMap = null;
var zonePolygon = null;
var editVertexMarkers = [];
var drawMarkers = [];
var isDrawing = false;
var drawVertices = [];
var drawPolyline = null;
var drawPolygon = null;
var editMode = false;
var selectedPolygon = null;

function initLocationMap() {
    var mapEl = document.getElementById('map');
    if (!mapEl) return;

    locationMap = new google.maps.Map(mapEl, {
        zoom: 2,
        minZoom: 1,
        center: { lat: 0, lng: 0 },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        streetViewControl: false,
        mapTypeControl: false,
        fullscreenControl: false,
        zoomControl: true,
    });

    // Search box
    var input = document.getElementById('pac-input');
    if (input) {
        var searchBox = new google.maps.places.SearchBox(input);
        locationMap.controls[google.maps.ControlPosition.TOP_RIGHT].push(input);
        locationMap.addListener('bounds_changed', function () {
            searchBox.setBounds(locationMap.getBounds());
        });
        searchBox.addListener('places_changed', function () {
            var places = searchBox.getPlaces();
            if (places.length === 0) return;
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function (place) {
                if (!place.geometry) return;
                if (place.geometry.viewport) bounds.union(place.geometry.viewport);
                else bounds.extend(place.geometry.location);
            });
            locationMap.fitBounds(bounds);
        });
        input.classList.remove('hide');
    }

    // Drawing & editing toolbar
    var toolbar = document.createElement('div');
    toolbar.style.cssText = 'display:flex;gap:6px;background:var(--color-white);padding:6px 10px;border-radius:8px;box-shadow:0 2px 8px var(--color-as-rgba-0-0-0-0-18);margin:10px 0 0 10px;';
    toolbar.innerHTML =
        '<button id="draw-btn" class="btn btn-sm btn-outline-primary" title="Draw zone boundary" type="button"><i class="bi bi-bezier2"></i> Draw</button>' +
        '<button id="finish-btn" class="btn btn-sm btn-success" style="display:none" type="button"><i class="bi bi-check-lg"></i> Finish</button>' +
        '<button id="cancel-btn" class="btn btn-sm btn-outline-danger" style="display:none" type="button"><i class="bi bi-x-lg"></i></button>' +
        '<button id="edit-btn" class="btn btn-sm btn-outline-info" style="display:none" type="button"><i class="bi bi-pencil"></i> Edit</button>' +
        '<button id="delete-btn" class="btn btn-sm btn-outline-danger" style="display:none" type="button"><i class="bi bi-trash"></i> Delete</button>';
    locationMap.controls[google.maps.ControlPosition.TOP_LEFT].push(toolbar);

    // Wire toolbar buttons
    document.getElementById('draw-btn').addEventListener('click', startDrawing);
    document.getElementById('finish-btn').addEventListener('click', finishDrawing);
    document.getElementById('cancel-btn').addEventListener('click', cancelDrawing);
    document.getElementById('edit-btn').addEventListener('click', toggleEditMode);
    document.getElementById('delete-btn').addEventListener('click', deletePolygon);

    // Map click for drawing vertices
    locationMap.addListener('click', function (e) {
        if (isDrawing) {
            addVertex(e.latLng);
            return;
        }
        // Deselect polygon when clicking away from it
        if (!editMode && selectedPolygon && zonePolygon && google.maps.geometry) {
            if (!google.maps.geometry.poly.containsLocation(e.latLng, zonePolygon)) {
                deselectPolygon();
            }
        }
    });

    // Double-click to finish drawing (prevent zoom)
    locationMap.addListener('dblclick', function (e) {
        if (isDrawing) {
            e.stop();
            finishDrawing();
        }
    });

    // Load existing polygon on edit
    loadExistingPolygon();
}

// ── Load existing polygon from server ──
function loadExistingPolygon() {
    var wrapper = document.getElementById('layout-wrapper');
    var attr = wrapper ? wrapper.getAttribute('data-coords') : null;
    if (!attr || attr === 'null' || attr === '') return;
    var groups = [];
    try { groups = JSON.parse(attr); } catch (e) { return; }
    if (!Array.isArray(groups) || groups.length === 0) return;
    var firstGroup = groups[0];
    if (!Array.isArray(firstGroup) || firstGroup.length < 3) return;

    var paths = firstGroup.map(function (p) {
        return new google.maps.LatLng(p.lat, p.lng);
    });

    zonePolygon = new google.maps.Polygon({
        paths: paths,
        strokeWeight: 1,
        strokeColor: LalaColors.asFe2c2c,
        fillOpacity: 0.45,
        fillColor: LalaColors.asFe2c2c,
        editable: false,
        draggable: false,
        zIndex: 1,
    });
    zonePolygon.setMap(locationMap);
    zonePolygon.addListener('click', function () { selectPolygon(zonePolygon); });

    syncCoordinates();

    var bounds = new google.maps.LatLngBounds();
    paths.forEach(function (ll) { bounds.extend(ll); });
    if (!bounds.isEmpty()) {
        locationMap.fitBounds(bounds);
    }

    showEditButton();
}

// ── Drawing ──
function startDrawing() {
    if (zonePolygon) {
        zonePolygon.setMap(null);
        zonePolygon = null;
    }
    removeEditMarkers();
    clearDrawHelpers();
    isDrawing = true;
    drawVertices = [];
    drawPolygon = null;
    drawPolyline = null;
    drawMarkers = [];
    selectedPolygon = null;
    hideToolbarButtons(['draw-btn']);
    showToolbarButtons(['finish-btn', 'cancel-btn']);
    locationMap.setOptions({ draggable: false, disableDoubleClickZoom: true });
    syncCoordinates();
}

function addVertex(latLng) {
    var threshold = 0.0005 / Math.pow(2, locationMap.getZoom() - 10);
    if (drawVertices.length >= 3) {
        var first = drawVertices[0];
        var dLat = latLng.lat() - first.lat;
        var dLng = latLng.lng() - first.lng;
        if ((dLat * dLat + dLng * dLng) < (threshold * threshold)) {
            finishDrawing();
            return;
        }
    }

    var pos = { lat: latLng.lat(), lng: latLng.lng() };
    drawVertices.push(pos);

    var isFirst = drawVertices.length === 1;
    var marker = new google.maps.Marker({
        position: pos,
        map: locationMap,
        draggable: false,
        clickable: false,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: isFirst ? 10 : 6,
            fillColor: LalaColors.as0f766e,
            fillOpacity: 1,
            strokeColor: LalaColors.white,
            strokeWeight: isFirst ? 3 : 2,
        },
        zIndex: 3,
    });
    drawMarkers.push(marker);

    // Update preview polyline
    if (drawPolyline) drawPolyline.setMap(null);
    drawPolyline = new google.maps.Polyline({
        path: drawVertices,
        map: locationMap,
        strokeColor: LalaColors.as0f766e,
        strokeWeight: 2,
        strokeOpacity: 0.8,
        zIndex: 2,
    });

    // Update preview polygon
    if (drawVertices.length >= 2) {
        if (drawPolygon) drawPolygon.setMap(null);
        drawPolygon = new google.maps.Polygon({
            paths: drawVertices,
            strokeWeight: 1,
            strokeColor: LalaColors.as0f766e,
            fillOpacity: 0.35,
            fillColor: LalaColors.as14b8a6,
            editable: false,
            draggable: false,
            zIndex: 1,
        });
        drawPolygon.setMap(locationMap);
    }
}

function finishDrawing() {
    if (drawVertices.length < 3) {
        alert('Please place at least 3 points to create a zone boundary.');
        return;
    }
    isDrawing = false;
    clearDrawHelpers();

    if (drawPolygon) {
        drawPolygon.setOptions({
            strokeWeight: 1.5,
            strokeColor: LalaColors.asFe2c2c,
            fillOpacity: 0.45,
            fillColor: LalaColors.asFe2c2c,
        });
        zonePolygon = drawPolygon;
        drawPolygon = null;
        zonePolygon.addListener('click', function () { selectPolygon(zonePolygon); });
    }

    syncCoordinates();
    showToolbarButtons(['draw-btn']);
    hideToolbarButtons(['finish-btn', 'cancel-btn']);
    showEditButton();
    locationMap.setOptions({ draggable: true, disableDoubleClickZoom: false });
}

function cancelDrawing() {
    isDrawing = false;
    clearDrawHelpers();
    if (drawPolygon) { drawPolygon.setMap(null); drawPolygon = null; }
    drawVertices = [];
    syncCoordinates();
    showToolbarButtons(['draw-btn']);
    hideToolbarButtons(['finish-btn', 'cancel-btn']);
    if (zonePolygon) showEditButton();
    locationMap.setOptions({ draggable: true, disableDoubleClickZoom: false });
}

function clearDrawHelpers() {
    if (drawPolyline) { drawPolyline.setMap(null); drawPolyline = null; }
    drawMarkers.forEach(function (m) { m.setMap(null); });
    drawMarkers = [];
}

// ── Selection ──
function selectPolygon(poly) {
    if (isDrawing) return;
    if (editMode) return;
    selectedPolygon = poly;
    zonePolygon.setOptions({ strokeColor: LalaColors.info, strokeWeight: 2.5 });
    showToolbarButtons(['edit-btn', 'delete-btn']);
    hideToolbarButtons(['draw-btn']);
}

function deselectPolygon() {
    if (!zonePolygon) return;
    if (editMode) return;
    selectedPolygon = null;
    zonePolygon.setOptions({ strokeColor: LalaColors.asFe2c2c, strokeWeight: 1.5 });
    showToolbarButtons(['draw-btn']);
    hideToolbarButtons(['edit-btn', 'delete-btn']);
}

// ── Editing ──
function toggleEditMode() {
    if (editMode) {
        disableEditMode();
    } else {
        enableEditMode();
    }
}

function enableEditMode() {
    if (!zonePolygon) return;
    editMode = true;
    selectedPolygon = null;
    zonePolygon.setOptions({ strokeColor: LalaColors.info, strokeWeight: 2.5, editable: false });
    document.getElementById('edit-btn').innerHTML = '<i class="bi bi-check-lg"></i> Done';
    document.getElementById('edit-btn').className = 'btn btn-sm btn-success';
    hideToolbarButtons(['delete-btn', 'draw-btn']);

    var path = zonePolygon.getPath().getArray();
    removeEditMarkers();
    path.forEach(function (ll, i) {
        var marker = new google.maps.Marker({
            position: ll,
            map: locationMap,
            draggable: true,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 7,
                fillColor: LalaColors.white,
                fillOpacity: 1,
                strokeColor: LalaColors.info,
                strokeWeight: 3,
            },
            zIndex: 4,
            title: 'Vertex ' + (i + 1),
        });
        marker.addListener('drag', function () {
            updatePolygonFromMarkers();
        });
        marker.addListener('dragend', function () {
            syncCoordinates();
        });
        editVertexMarkers.push(marker);
    });
}

function disableEditMode() {
    editMode = false;
    removeEditMarkers();
    zonePolygon.setOptions({ strokeColor: LalaColors.asFe2c2c, strokeWeight: 1.5 });
    document.getElementById('edit-btn').innerHTML = '<i class="bi bi-pencil"></i> Edit';
    document.getElementById('edit-btn').className = 'btn btn-sm btn-outline-info';
    showToolbarButtons(['draw-btn']);
    hideToolbarButtons(['delete-btn']);
    syncCoordinates();
}

function updatePolygonFromMarkers() {
    if (!zonePolygon) return;
    var newPath = editVertexMarkers.map(function (m) { return m.getPosition(); });
    zonePolygon.setPath(newPath);
}

function removeEditMarkers() {
    editVertexMarkers.forEach(function (m) { m.setMap(null); });
    editVertexMarkers = [];
}

// ── Delete ──
function deletePolygon() {
    if (!zonePolygon) return;
    if (!confirm('Delete this zone boundary?')) return;
    zonePolygon.setMap(null);
    zonePolygon = null;
    removeEditMarkers();
    editMode = false;
    selectedPolygon = null;
    syncCoordinates();
    showToolbarButtons(['draw-btn']);
    hideToolbarButtons(['edit-btn', 'delete-btn', 'finish-btn', 'cancel-btn']);
    document.getElementById('edit-btn').innerHTML = '<i class="bi bi-pencil"></i> Edit';
    document.getElementById('edit-btn').className = 'btn btn-sm btn-outline-info';
}

// ── Coordinate syncing ──
function syncCoordinates() {
    if (!zonePolygon) {
        document.getElementById('coordinates-field').value = '';
        return;
    }
    var path = zonePolygon.getPath().getArray().map(function (ll) {
        return ll.lat().toFixed(6) + ' ' + ll.lng().toFixed(6);
    });
    if (path.length > 0) {
        path.push(path[0]);
    }
    document.getElementById('coordinates-field').value = path.join(', ');
}

// ── Toolbar helpers ──
function showToolbarButtons(ids) {
    ids.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.style.display = '';
    });
}

function hideToolbarButtons(ids) {
    ids.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

function showEditButton() {
    if (zonePolygon) {
        document.getElementById('edit-btn').style.display = '';
    }
}

// ── Download handlers ──
document.addEventListener('DOMContentLoaded', function () {
    var csvBtn = document.getElementById('download-csv');
    var jsonBtn = document.getElementById('download-json');

    if (csvBtn) {
        csvBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var rows = [];
            var cols = ['SL', 'Zone Name', 'Trip Request Volume', 'Extra Fare Enabled', 'Extra Fare (%)', 'Status'];
            rows.push(cols.join(','));
            document.querySelectorAll('.table tbody tr').forEach(function (tr) {
                var cells = tr.querySelectorAll('td');
                if (cells.length < 2) return;
                var row = [];
                row.push(cells[0].textContent.trim());
                row.push('"' + (cells[1].textContent.trim()) + '"');
                row.push(cells[2].textContent.trim());
                row.push(cells[3].textContent.trim());
                row.push(cells[4].textContent.trim());
                row.push(cells[5].textContent.trim());
                rows.push(row.join(','));
            });
            var blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'zones.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    }

    if (jsonBtn) {
        jsonBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var data = [];
            document.querySelectorAll('.table tbody tr').forEach(function (tr) {
                var cells = tr.querySelectorAll('td');
                if (cells.length < 2) return;
                data.push({
                    sl: cells[0].textContent.trim(),
                    name: cells[1].textContent.trim(),
                    trip_volume: cells[2].textContent.trim(),
                    extra_fare_enabled: cells[3].textContent.trim(),
                    extra_fare_percentage: cells[4].textContent.trim(),
                    status: cells[5].textContent.trim()
                });
            });
            var blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'zones.json';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    }
});
</script>
@endpush
