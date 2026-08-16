@extends('admin.template')

@section('title-sub', 'Add Zone')
@section('pagetitle', 'Add Zone')

@section('css')
<style>
    #map { height: 560px; width: 100%; border-radius: 1rem; border: 1px solid rgba(20, 33, 61, 0.08); }
    .controls.hide { display: none; }
    /* Make sure the search bar is visible and styled */
    #pac-input {
        position: absolute;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        width: 280px;
        max-width: calc(100% - 80px);
        z-index: 5;
        box-shadow: 0 2px 6px rgba(0,0,0,0.18);
    }
    .map-canvas-wrap {
        position: relative;
    }
    /* Make sure the drawing control icons are visible */
    .gm-style .gmnoprint.gm-control-container,
    .gm-style > div > .gmnoprint > div {
        z-index: 10 !important;
    }
</style>
@endsection

@section('main')
<div id="layout-wrapper" ng-controller="manage_locations" ng-cloak ng-init="formatted_coords={{ json_encode(old('coordinates') ? app(\App\Http\Start\Helpers::class)->getFormattedCoordinates(old('coordinates')) : $formattedCoords) }};coordinates=[]">
    {!! Form::open(['url' => $isAdmin ? route('saasdispatch.companies.service-areas.store', $company) : route('saasdispatch.self.service-areas.store'), 'class' => 'row g-4']) !!}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">Zone Setup</h5>
                <p class="text-muted mb-0">Create company zones with polygon boundaries, trip coverage, and extra fare controls.</p>
            </div>
            <div class="card-body">
                <p class="text-danger mb-4">(*) Fields are mandatory</p>
                <div class="mb-4">
                    <label class="form-label">Zone Name <em class="text-danger">*</em></label>
                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Ex: Downtown Zone']) !!}
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                </div>
                <div class="mb-4">
                    <label class="form-label">Status <em class="text-danger">*</em></label>
                    {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status'), ['class' => 'form-select', 'placeholder' => 'Select']) !!}
                    <span class="text-danger">{{ $errors->first('status') }}</span>
                </div>
                {{ Form::hidden('coordinates', '', ['class' => 'coordinates', 'ng-model' => 'coordinates']) }}
                <div class="d-flex gap-3 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pickup_enabled" value="1" id="pickup_enabled" checked>
                        <label class="form-check-label" for="pickup_enabled">Pickup enabled</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="dropoff_enabled" value="1" id="dropoff_enabled" checked>
                        <label class="form-check-label" for="dropoff_enabled">Dropoff enabled</label>
                    </div>
                </div>
                <div class="border rounded p-3 mb-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="extra_fare_enabled" value="1" id="extra_fare_enabled" {{ old('extra_fare_enabled') ? 'checked' : '' }}>
                        <label class="form-check-label" for="extra_fare_enabled">Enable Extra Fare</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Extra Fare (%)</label>
                        <input type="number" step="0.01" name="extra_fare_percentage" value="{{ old('extra_fare_percentage', 0) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Reason</label>
                        <input type="text" name="extra_fare_reason" value="{{ old('extra_fare_reason') }}" class="form-control" placeholder="Heavy traffic / high demand">
                    </div>
                </div>
                <div class="alert alert-light border">Draw the zone boundary on the map. The boundary will be used for company trip coverage.</div>
                <span class="text-danger d-block">{{ $errors->first('coordinates') }}</span>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ $isAdmin ? route('saasdispatch.companies.service-areas.index', $company) : route('saasdispatch.self.service-areas.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body map-canvas-wrap">
                <input id="pac-input" class="form-control controls mb-3" type="text" placeholder="Search location">
                <div id="map"></div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</div>
@endsection
