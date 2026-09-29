@extends('admin.template')

@section('title-sub', __('messages.admin.manage_fares.add_manage_fare'))
@section('pagetitle', __('messages.admin.manage_fares.add_manage_fare'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller='manage_peak_fare' ng-cloak>

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/add_manage_fare', 'class' => 'row g-5']) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_fares.add_title') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-12" ng-init="time_options={{ json_encode($time_options) }};day_options={{json_encode($day_options)}};">
                                <!-- Location -->
                                <div class="mb-4" ng-init="location='{{ old('location','') }}'">
                                    <label class="form-label" for="input_location">
                                        {{ __('messages.admin.manage_fares.form.location') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::select('location', $locations, '', [
                                        'class' => 'form-select',
                                        'id' => 'input_location'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('location') }}</span>
                                </div>

                                <!-- Vehicle Type -->
                                <div class="mb-4" ng-init="vehicle_type='{{ old('vehicle_type','') }}'">
                                    <label class="form-label" for="input_vehicle_type">
                                        {{ __('messages.admin.manage_fares.form.vehicle_type') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::select('vehicle_type', $vehicle_types, '', [
                                        'class' => 'form-select',
                                        'id' => 'input_vehicle_type'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('vehicle_type') }}</span>
                                    <span class="text-danger">{{ $errors->first('same_loc_error') }}</span>
                                </div>

                                <!-- Fare Details -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label" for="input_base_fare">
                                            {{ __('messages.admin.manage_fares.form.base_fare') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('base_fare', '', [
                                            'class' => 'form-control',
                                            'id' => 'input_base_fare',
                                            'placeholder' => __('messages.admin.manage_fares.form.base_fare')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('base_fare') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_capacity">
                                            {{ __('messages.admin.manage_fares.form.capacity') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('capacity', '', [
                                            'class' => 'form-control',
                                            'id' => 'input_capacity',
                                            'placeholder' => __('messages.admin.manage_fares.form.capacity')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('capacity') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_min_fare">
                                            {{ __('messages.admin.manage_fares.form.minimum_fare') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('min_fare', '', [
                                            'class' => 'form-control',
                                            'id' => 'input_min_fare',
                                            'placeholder' => __('messages.admin.manage_fares.form.minimum_fare')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('min_fare') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_per_min">
                                            {{ __('messages.admin.manage_fares.form.per_minutes') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('per_min', '', [
                                            'class' => 'form-control',
                                            'id' => 'input_per_min',
                                            'placeholder' => __('messages.admin.manage_fares.form.per_minutes')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('per_min') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_per_km">
                                            {{ __('messages.admin.manage_fares.form.per_kilometer') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('per_km', '', [
                                            'class' => 'form-control',
                                            'id' => 'input_per_km',
                                            'placeholder' => __('messages.admin.manage_fares.form.per_kilometer')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('per_km') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_waiting_time">
                                            {{ __('messages.admin.manage_fares.form.waiting_time_limit') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('waiting_time', old('waiting_time',''), [
                                            'class' => 'form-control',
                                            'id' => 'input_waiting_time',
                                            'placeholder' => __('messages.admin.manage_fares.form.waiting_time_limit')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('waiting_time') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_waiting_charge">
                                            {{ __('messages.admin.manage_fares.form.waiting_charges') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('waiting_charge', old('waiting_charge',''), [
                                            'class' => 'form-control',
                                            'id' => 'input_waiting_charge',
                                            'placeholder' => __('messages.admin.manage_fares.form.waiting_charges')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('waiting_charge') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_schedule_fare">
                                            {{ __('messages.admin.manage_fares.form.schedule_ride_fare') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('schedule_fare', '', [
                                            'class' => 'form-control',
                                            'id' => 'input_schedule_fare',
                                            'placeholder' => __('messages.admin.manage_fares.form.schedule_ride_fare')
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('schedule_fare') }}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="input_currency_code">
                                            {{ __('messages.admin.manage_fares.form.currency_code') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::select('currency_code', $currency, '', [
                                            'class' => 'form-select',
                                            'id' => 'input_currency_code',
                                            'placeholder' => 'Select'
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('currency_code') }}</span>
                                    </div>
                                </div>

                             

                                <!-- Buttons -->
                                <div class="col-12 text-end">
                                    <a href="{{ url('admin/manage_fare') }}" class="btn btn-light-primary">
                                        {{ __('messages.admin.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary manage_fare_submit" name="submit" value="submit" ng-click="disableButton($event)">
                                        {{ __('messages.admin.submit') }}
                                    </button>
                                </div>
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                           <!-- Peak Fare Section -->
                                <div class="mb-4">
                                    <label class="form-label" for="input_peak_fare">
                                        {{ __('messages.admin.manage_fares.form.apply_peak_fare') }} <em class="text-danger">*</em>
                                    </label>
                                    <div ng-init="apply_peak='{{ old('apply_peak','') }}'">
                                        {!! Form::select('apply_peak', array_merge([''=>'Select','Yes' =>'Yes','No' =>'No']),'', [
                                            'class' => 'form-select',
                                            'id' => 'input_peak_fare',
                                            'ng-model' => 'apply_peak'
                                        ]) !!}
                                        <span class="text-danger" ng-hide="apply_peak != ''">{{ $errors->first('apply_peak') }}</span>
                                        <span class="text-danger" ng-hide="apply_peak == 'Yes' || apply_night =='Yes'">{{ $errors->first('not_fare') }}</span>
                                    </div>
                                </div>

                                <!-- Peak Fare Details -->
                                <div class="mb-4" ng-show="apply_peak == 'Yes'">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">{{ __('messages.admin.manage_fares.peak_fare_details') }}</h6>
                                        </div>
                                        <div class="card-body" ng-init="peak_fare_details = {{json_encode(old('peak_fare_details') ?: array(0=>[])) }};errors = {{json_encode($errors->getMessages())}};removed_fares=''">
                                            <div class="row g-3" ng-repeat="fare_detail in peak_fare_details">
                                                <div class="col-md-3">
                                                    <label class="form-label small">{{ __('messages.admin.manage_fares.form.day') }}</label>
                                                    <select name="peak_fare_details[@{{$index}}][day]" ng-model="fare_detail.day" 
                                                            class="form-select form-select-sm" 
                                                            ng-change="update_day($index,fare_detail.day)" 
                                                            data-index="@{{ $index }}">
                                                        <option value="" disabled>{{ __('messages.blade_static.select_85982229') }}</option>
                                                        <option ng-repeat="(key, value) in day_options" value="@{{ key }}" 
                                                                ng-selected="key == fare_detail.day" 
                                                                ng-disabled="ifDayDisabled($parent.$index,key)">
                                                            @{{value}}
                                                        </option>
                                                    </select>
                                                    <span class="text-danger small">@{{ errors['peak_fare_details.'+$index+'.day'][0] }}</span>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label small">{{ __('messages.admin.manage_fares.form.start_time') }}</label>
                                                    <select name="peak_fare_details[@{{$index}}][start_time]" ng-model="fare_detail.start_time" 
                                                            class="form-select form-select-sm" 
                                                            ng-change="update_time($index,fare_detail.day)" 
                                                            ng-init="update_time($index,fare_detail.day)">
                                                        <option value="" disabled>{{ __('messages.blade_static.select_85982229') }}</option>
                                                        <option ng-repeat="(key, value) in time_options" value="@{{ key }}" 
                                                                ng-selected="key == fare_detail.start_time" 
                                                                ng-disabled="checkIfDisabled($parent.$index,fare_detail.day,key,'start_time')">
                                                            @{{value}}
                                                        </option>
                                                    </select>
                                                    <span class="text-danger small">@{{ errors['peak_fare_details.'+$index+'.start_time'][0] }}</span>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label small">{{ __('messages.admin.manage_fares.form.end_time') }}</label>
                                                    <select name="peak_fare_details[@{{$index}}][end_time]" ng-model="fare_detail.end_time" 
                                                            class="form-select form-select-sm" 
                                                            ng-change="update_time($index,fare_detail.day)">
                                                        <option value="" disabled>{{ __('messages.blade_static.select_85982229') }}</option>
                                                        <option ng-repeat="(key, value) in time_options" value="@{{ key }}" 
                                                                ng-selected="key == fare_detail.end_time" 
                                                                ng-disabled="key <= fare_detail.start_time || checkIfDisabled($parent.$index,fare_detail.day,key,'end_time')">
                                                            @{{value}}
                                                        </option>
                                                    </select>
                                                    <span class="text-danger small">@{{ errors['peak_fare_details.'+$index+'.end_time'][0] }}</span>
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_fares.form.peak_fare') }} x
                                                        <i class="fas fa-info-circle text-info" title="{{ __('messages.blade_static.normal_fare_100_10_peak_fare_x_2_10_peak_time_pricing_x2_41943bb4') }}"></i>
                                                    </label>
                                                    <input type="text" name="peak_fare_details[@{{$index}}][price]" ng-model="fare_detail.price" 
                                                           class="form-control form-control-sm" placeholder="{{ __('messages.blade_static.multiplier_b032583d') }}">
                                                    <span class="text-danger small">@{{ errors['peak_fare_details.'+$index+'.price'][0] }}</span>
                                                </div>

                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            ng-click="remove_price_rule('peak', $index,fare_detail.day)" 
                                                            ng-if="peak_fare_details.length > 1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="text-end mt-3">
                                                <button type="button" class="btn btn-success btn-sm" ng-click="add_price_rule('peak')">
                                                    <i class="fas fa-plus"></i> {{ __('messages.blade_static.add_peak_fare_379afff6') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Night Fare Section -->
                                <div class="mb-4">
                                    <label class="form-label" for="input_night_fare">
                                        {{ __('messages.admin.manage_fares.form.apply_night_fare') }} <em class="text-danger">*</em>
                                    </label>
                                    <div ng-init="apply_night='{{ old('apply_night','') }}'">
                                        {!! Form::select('apply_night', array_merge([''=>'Select','Yes' =>'Yes','No' =>'No']), '', [
                                            'class' => 'form-select',
                                            'id' => 'input_night_fare',
                                            'ng-model' => 'apply_night'
                                        ]) !!}
                                        <span class="text-danger" ng-hide="apply_night != ''">{{ $errors->first('apply_night') }}</span>
                                    </div>
                                </div>

                                <!-- Night Fare Details -->
                                <div class="mb-4" ng-show="apply_night == 'Yes'">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">{{ __('messages.admin.manage_fares.night_fare_details') }}</h6>
                                        </div>
                                        <div class="card-body" ng-init="night_fare_details = {{json_encode(old('night_fare_details') ?: array()) }};errors = {{json_encode($errors->getMessages())}};">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label small">{{ __('messages.admin.manage_fares.form.start_time') }}</label>
                                                    <select name="night_fare_details[start_time]" ng-model="night_fare_details.start_time" 
                                                            class="form-select form-select-sm" 
                                                            ng-change="updateNightTimeOptions();update_night_fare_time();">
                                                        <option value="" disabled>{{ __('messages.blade_static.select_85982229') }}</option>
                                                        <option ng-repeat="(key, value) in time_options" value="@{{ key }}" 
                                                                ng-selected="key == night_fare_details.start_time">
                                                            @{{value}}
                                                        </option>
                                                    </select>
                                                    <span class="text-danger small">@{{ errors['night_fare_details.start_time'][0] }}</span>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label small">{{ __('messages.admin.manage_fares.form.end_time') }}</label>
                                                    <select name="night_fare_details[end_time]" ng-model="night_fare_details.end_time" 
                                                            class="form-select form-select-sm">
                                                        <option value="" disabled>{{ __('messages.blade_static.select_85982229') }}</option>
                                                        <option ng-repeat="(key, value) in before_times" value="@{{ key }}" 
                                                                ng-selected="key == night_fare_details.end_time">
                                                            @{{value}} @{{ (key <= night_fare_details.start_time) ? '*' :'' }}
                                                        </option>
                                                        <option ng-repeat="(key, value) in after_times" value="@{{ key }}" 
                                                                ng-selected="key == night_fare_details.end_time">
                                                            @{{value}} @{{ (key <= night_fare_details.start_time) ? '*' :'' }}
                                                        </option>
                                                    </select>
                                                    <span class="text-danger small">@{{ errors['night_fare_details.end_time'][0] }}</span>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_fares.form.night_fare') }} x
                                                        <i class="fas fa-info-circle text-info" title="{{ __('messages.blade_static.normal_fare_100_10_night_fare_x_2_10_night_time_pricing_f94f657c') }}"></i>
                                                    </label>
                                                    <input type="text" name="night_fare_details[price]" ng-model="night_fare_details.price" 
                                                           class="form-control form-control-sm" placeholder="{{ __('messages.blade_static.multiplier_b032583d') }}">
                                                    <span class="text-danger small">@{{ errors['night_fare_details.price'][0] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                    </div>
                </div>
            </div><!-- End Right Col -->
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
    </main>
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection