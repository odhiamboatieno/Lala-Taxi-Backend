@extends('admin.template')

@section('title-sub', 'Edit Company Fare Rule')
@section('pagetitle', 'Edit Company Fare Rule')

@section('css')
<link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
<div id="layout-wrapper" ng-controller='manage_peak_fare' ng-cloak>
    @include('saasdispatch::partials.company_tabs')
    <div class="row">
        <div class="col-xl-6">
            <div class="card card-h-100">
                <div class="card-body">
                    {!! Form::open(['url' => $isAdmin ? route('saasdispatch.companies.fare-rules.update', [$company, $result]) : route('saasdispatch.self.fare-rules.update', $result), 'class' => 'row g-5']) !!}
                    @method('PUT')
                    <div class="col-12">
                        <h6 class="mb-3 text-primary">Fare Rule Details</h6>
                        <p class="text-danger mb-3">(*) Fields are mandatory</p>
                    </div>
                    <div class="col-12" ng-init="time_options={{ json_encode($time_options) }};day_options={{json_encode($day_options)}};">
                        <div class="mb-4">
                            <label class="form-label">Location <em class="text-danger">*</em></label>
                            {!! Form::select('company_service_area_id', $locations, old('company_service_area_id', $result->company_service_area_id), ['class' => 'form-select', 'placeholder' => 'Select']) !!}
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Vehicle Type <em class="text-danger">*</em></label>
                            {!! Form::select('company_vehicle_type_id', $vehicle_types, old('company_vehicle_type_id', $result->company_vehicle_type_id), ['class' => 'form-select', 'placeholder' => 'Select']) !!}
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label class="form-label">Base Fare</label>{!! Form::text('base_fare', old('base_fare', $result->base_fare), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Capacity</label>{!! Form::text('capacity', old('capacity', $result->capacity), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Minimum Fare</label>{!! Form::text('minimum_fare', old('minimum_fare', $result->minimum_fare), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Per Minutes</label>{!! Form::text('per_min', old('per_min', $result->per_minute), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Per Kilometer</label>{!! Form::text('per_km', old('per_km', $result->per_km), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Waiting Time</label>{!! Form::text('waiting_time', old('waiting_time', $result->waiting_time), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Waiting Charge</label>{!! Form::text('waiting_charge', old('waiting_charge', $result->waiting_charge), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Schedule Fare</label>{!! Form::text('schedule_fare', old('schedule_fare', $result->schedule_fare), ['class' => 'form-control']) !!}</div>
                            <div class="col-md-6"><label class="form-label">Currency Code</label>{!! Form::select('currency_code', $currency, old('currency_code', $result->currency_code), ['class' => 'form-select', 'placeholder' => 'Select']) !!}</div>
                        </div>
                        <div class="col-12 text-end">
                            <a href="{{ $isAdmin ? route('saasdispatch.companies.fare-rules.index', $company) : route('saasdispatch.self.fare-rules.index') }}" class="btn btn-light-primary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Apply Peak Fare <em class="text-danger">*</em></label>
                        <div ng-init="apply_peak='{{ old('apply_peak', $result->apply_peak) }}'">
                            {!! Form::select('apply_peak', ['Yes' => 'Yes','No' => 'No'], old('apply_peak', $result->apply_peak), ['class' => 'form-select', 'ng-model' => 'apply_peak']) !!}
                        </div>
                    </div>
                    <div class="mb-4" ng-show="apply_peak == 'Yes'">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white"><h6 class="mb-0">Peak Fare Details</h6></div>
                            <div class="card-body" ng-init="peak_fare_details = {{ json_encode(old('peak_fare_details') ?: $peak_fare_details) }};">
                                <div class="row g-3" ng-repeat="fare_detail in peak_fare_details">
                                    <div class="col-md-3"><input type="hidden" name="peak_fare_details[@{{$index}}][id]" ng-model="fare_detail.id"><select name="peak_fare_details[@{{$index}}][day]" ng-model="fare_detail.day" class="form-select form-select-sm"><option value="">Select</option><option ng-repeat="(key, value) in day_options" value="@{{ key }}">@{{ value }}</option></select></div>
                                    <div class="col-md-3"><select name="peak_fare_details[@{{$index}}][start_time]" ng-model="fare_detail.start_time" class="form-select form-select-sm"><option value="">Select</option><option ng-repeat="(key, value) in time_options" value="@{{ key }}">@{{ value }}</option></select></div>
                                    <div class="col-md-3"><select name="peak_fare_details[@{{$index}}][end_time]" ng-model="fare_detail.end_time" class="form-select form-select-sm"><option value="">Select</option><option ng-repeat="(key, value) in time_options" value="@{{ key }}">@{{ value }}</option></select></div>
                                    <div class="col-md-3"><input type="text" name="peak_fare_details[@{{$index}}][price]" ng-model="fare_detail.price" class="form-control form-control-sm" placeholder="Multiplier"></div>
                                </div>
                                <div class="text-end mt-3"><button type="button" class="btn btn-success btn-sm" ng-click="peak_fare_details.push({})">Add Peak Fare</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Apply Night Fare <em class="text-danger">*</em></label>
                        <div ng-init="apply_night='{{ old('apply_night', $result->apply_night) }}'">
                            {!! Form::select('apply_night', ['Yes' => 'Yes','No' => 'No'], old('apply_night', $result->apply_night), ['class' => 'form-select', 'ng-model' => 'apply_night']) !!}
                        </div>
                    </div>
                    <div class="mb-4" ng-show="apply_night == 'Yes'">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white"><h6 class="mb-0">Night Fare Details</h6></div>
                            <div class="card-body" ng-init="night_fare_details = {{ json_encode(old('night_fare_details') ?: $night_fare_details) }};">
                                <div class="row g-3">
                                    <div class="col-md-4"><input type="hidden" name="night_fare_details[id]" ng-model="night_fare_details.id"><select name="night_fare_details[start_time]" ng-model="night_fare_details.start_time" class="form-select form-select-sm"><option value="">Select</option><option ng-repeat="(key, value) in time_options" value="@{{ key }}">@{{ value }}</option></select></div>
                                    <div class="col-md-4"><select name="night_fare_details[end_time]" ng-model="night_fare_details.end_time" class="form-select form-select-sm"><option value="">Select</option><option ng-repeat="(key, value) in time_options" value="@{{ key }}">@{{ value }}</option></select></div>
                                    <div class="col-md-4"><input type="text" name="night_fare_details[price]" ng-model="night_fare_details.price" class="form-control form-control-sm" placeholder="Multiplier"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
