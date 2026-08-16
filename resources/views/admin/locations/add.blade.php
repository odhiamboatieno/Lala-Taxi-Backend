@extends('admin.template')

@section('title-sub', __('messages.admin.locations.add_location'))
@section('pagetitle', __('messages.admin.locations.add_location'))

@section('css')
<style>
    #map {
        height: 560px;
        width: 100%;
        border-radius: 0;
        border: 1px solid var(--color-as-rgba-20-33-61-0-08);
    }
    .controls.hide {
        display: none;
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
</style>
@endsection

@section('main')
<div id="layout-wrapper" ng-controller="manage_locations" ng-cloak ng-init="formatted_coords={{ json_encode(old('formatted_coords', [])) }};coordinates=[]">
    {!! Form::open(['url' => 'admin/add_location', 'class' => 'row g-4']) !!}
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1">{{ __('messages.admin.locations.add_title') }}</h5>
                    <p class="text-muted mb-0">{{ __('messages.blade_static.create_service_zones_with_polygon_boundaries_and_control_61f3af28') }}</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="input_name">{{ __('messages.admin.locations.form.name') }} <em class="text-danger">*</em></label>
                        {!! Form::text('name', old('name'), ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Zone name']) !!}
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="input_status">{{ __('messages.admin.locations.form.status') }} <em class="text-danger">*</em></label>
                        {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status'), ['class' => 'form-select', 'id' => 'input_status', 'placeholder' => 'Select']) !!}
                        <span class="text-danger">{{ $errors->first('status') }}</span>
                    </div>

                    {{ Form::hidden('coordinates', '', ['class' => 'coordinates', 'ng-model' => 'coordinates']) }}

                    <div class="alert alert-light border">
                        {{ __('messages.blade_static.draw_the_zone_boundary_on_the_map_the_boundary_will_be_u_817eb6a3') }}
                    </div>

                    <span class="text-danger d-block">{{ $errors->first('coordinates') }}</span>
                    <span class="text-danger d-block">{{ old('location_set') }}</span>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ url('admin/locations') }}" class="btn btn-light">{{ __('messages.admin.cancel') }}</a>
                    <button type="submit" class="btn btn-primary" name="submit" value="submit">{{ __('messages.admin.submit') }}</button>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card">
                <div class="card-body map-canvas-wrap">
                    <input id="pac-input" class="form-control controls mb-3" type="text" placeholder="{{ __('messages.blade_static.search_location_a095b901') }}">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</div>
@endsection
