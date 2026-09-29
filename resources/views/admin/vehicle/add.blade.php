@extends('admin.template')

@section('title-sub', __('messages.admin.manage_vehicles.add_vehicles'))
@section('pagetitle', __('messages.admin.manage_vehicles.add_vehicles'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="vehicle_management" ng-cloak ng-init='vehicle_id=0'>

        <div class="row">
            {!! Form::open(['url' => LOGIN_USER_TYPE.'/add_vehicle', 'files' => true, 'id' => 'vehicle_form']) !!}
            {!! Form::hidden('user_country_code', '', ['id' => 'user_country_code']) !!}
            {!! Form::hidden('user_gender', '', ['id' => 'user_gender']) !!}

            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_vehicles.add_vehicles') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Company Selection -->
                            @if (LOGIN_USER_TYPE != 'company')
                                <div class="col-12">
                                    <label class="form-label" for="input_company_name">
                                        {{ __('messages.admin.manage_vehicles.form.company_name') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::select('company_name', $company, '', [
                                        'class' => 'form-select',
                                        'id' => 'input_company_name',
                                        'placeholder' => 'Select',
                                        'ng-model' => 'company_name',
                                        'ng-change' => 'get_driver()'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('company_name') }}</span>
                                </div>
                            @else
                                <span ng-init='company_name="{{Auth::guard("company")->user()->id}}";vehcileIdList=[];get_driver()'></span>
                            @endif

                            <!-- Driver Selection -->
                            <div class="col-12" ng-init="selectedDriver = '';">
                                <label class="form-label" for="input_driver_name">
                                    {{ __('messages.admin.manage_vehicles.form.driver_name') }} <em class="text-danger">*</em>
                                </label>
                                <span class="loading form-control" id="driver_loading" style="display: none;"><br></span>
                                <select class="form-select" name="driver_name" id="input_driver_name" ng-model="selectedDriver" ng-change="updateVehicleType()" ng-cloak>
                                    <option value="">{{ __('messages.blade_static.select_85982229') }}</option>
                                    <option ng-repeat="driver in drivers" value="@{{driver.id}}">@{{driver.first_name}} @{{driver.last_name}} - @{{driver.id}}</option>
                                </select>
                                <span class="text-danger" id="driver-error">{{ $errors->first('driver_name') }}</span>
                            </div>

                            <!-- Status -->
                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_vehicles.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], '', [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <!-- Vehicle Make & Model -->
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle_make">
                                    {{ __('messages.admin.manage_vehicles.form.make') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('vehicle_make_id', $make, '', [
                                    'class' => 'form-select',
                                    'id' => 'vehicle_make',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_make_id') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="vehicle_model">
                                    {{ __('messages.admin.manage_vehicles.form.model') }} <em class="text-danger">*</em>
                                </label>
                                <span class="loading form-control" id="model_loading" style="display: none;"><br></span>
                                {!! Form::select('vehicle_model_id', [], '', [
                                    'class' => 'form-select',
                                    'id' => 'vehicle_model',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_model_id') }}</span>
                            </div>

                            <!-- Vehicle Type -->
                            <div class="col-12">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_vehicles.form.vehicle_type') }} <em class="text-danger">*</em>
                                </label>
                                <div class="row">
                                    @foreach($car_type as $type)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="vehicle_type[]" id="vehicle_type_{{ $type->id }}" 
                                                       class="form-check-input vehicle_type" value="{{ $type->id }}" 
                                                       data-error-placement="container" data-error-container="#vehicle_type_error"/>
                                                <label class="form-check-label" for="vehicle_type_{{ $type->id }}">
                                                    {{ $type->car_name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-danger mt-2" id="vehicle_type_error"></div>
                                <span class="text-danger">{{ $errors->first('vehicle_type') }}</span>
                            </div>

                            <!-- Default Vehicle -->
                            <div class="col-12">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_vehicles.form.default') }} <em class="text-danger">*</em>
                                </label>
                                <div class="d-flex gap-4 mb-2">
                                    <div class="form-check">
                                        {{ Form::radio('default', '1', false, [
                                            'class' => 'form-check-input default',
                                            'id' => 'default_yes',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#default_error'
                                        ]) }}
                                        <label class="form-check-label" for="default_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('default', '0', false, [
                                            'class' => 'form-check-input default',
                                            'id' => 'default_no',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#default_error'
                                        ]) }}
                                        <label class="form-check-label" for="default_no">{{ __('messages.blade_static.no_816c52fd') }}</label>
                                    </div>
                                </div>
                                <div class="text-danger" id="default_error"></div>
                                <small class="text-muted">{{ __('messages.admin.manage_vehicles.form.default_note') }}</small>
                            </div>

                            <!-- Accessibility Options -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_vehicles.form.handicap_accessibility_available') }} <em class="text-danger">*</em>
                                </label>
                                <div class="d-flex gap-4 mb-3">
                                    <div class="form-check">
                                        {{ Form::radio('handicap', '1', false, [
                                            'class' => 'form-check-input handicap',
                                            'id' => 'handicap_yes',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#handicap_error'
                                        ]) }}
                                        <label class="form-check-label" for="handicap_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('handicap', '0', false, [
                                            'class' => 'form-check-input handicap',
                                            'id' => 'handicap_no',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#handicap_error'
                                        ]) }}
                                        <label class="form-check-label" for="handicap_no">{{ __('messages.blade_static.no_816c52fd') }}</label>
                                    </div>
                                </div>
                                <div class="text-danger" id="handicap_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_vehicles.form.child_seat_accessibility_available') }} <em class="text-danger">*</em>
                                </label>
                                <div class="d-flex gap-4 mb-3">
                                    <div class="form-check">
                                        {{ Form::radio('child_seat', '1', false, [
                                            'class' => 'form-check-input child_seat',
                                            'id' => 'child_seat_yes',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#child_seat_error'
                                        ]) }}
                                        <label class="form-check-label" for="child_seat_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('child_seat', '0', false, [
                                            'class' => 'form-check-input child_seat',
                                            'id' => 'child_seat_no',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#child_seat_error'
                                        ]) }}
                                        <label class="form-check-label" for="child_seat_no">{{ __('messages.blade_static.no_816c52fd') }}</label>
                                    </div>
                                </div>
                                <div class="text-danger" id="child_seat_error"></div>
                            </div>

                            <!-- Request From -->
                            <div class="col-12">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_vehicles.form.request_form') }} <em class="text-danger">*</em>
                                </label>
                                <div class="d-flex gap-4 mb-2">
                                    <div class="form-check">
                                        {{ Form::radio('request_from', '1', false, [
                                            'class' => 'form-check-input request_from',
                                            'id' => 'request_from_female',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#request_from_error'
                                        ]) }}
                                        <label class="form-check-label" for="request_from_female">{{ __('messages.blade_static.female_b7c17e97') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('request_from', '0', false, [
                                            'class' => 'form-check-input request_from',
                                            'id' => 'request_from_both',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#request_from_error'
                                        ]) }}
                                        <label class="form-check-label" for="request_from_both">{{ __('messages.blade_static.both_1f469838') }}</label>
                                    </div>
                                </div>
                                <div class="text-danger" id="request_from_error"></div>
                                <small class="text-muted">{{ __('messages.admin.manage_vehicles.form.request_form_note') }}</small>
                            </div>

                            <!-- Vehicle Details -->
                            <div class="col-md-4">
                                <label class="form-label" for="vehicle_number">
                                    {{ __('messages.admin.manage_vehicles.form.vehicle_number') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('vehicle_number', '', [
                                    'class' => 'form-control',
                                    'id' => 'vehicle_number',
                                    'placeholder' => __('messages.admin.manage_vehicles.form.vehicle_number')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_number') }}</span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="color">
                                    {{ __('messages.admin.manage_vehicles.form.color') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('color', '', [
                                    'class' => 'form-control',
                                    'id' => 'color',
                                    'placeholder' => __('messages.admin.manage_vehicles.form.color')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('color') }}</span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="year">
                                    {{ __('messages.admin.manage_vehicles.form.year') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('year', '', [
                                    'class' => 'form-control',
                                    'id' => 'year',
                                    'placeholder' => __('messages.admin.manage_vehicles.form.year'),
                                    'autocomplete' => 'off'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('year') }}</span>
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" id="vehicle_id" value="">

                            <!-- Vehicle Documents -->
                            <div id="vehicle-documents-section">
                                <div class="text-muted">Select a driver to load documents</div>
                            </div>
                            <div class="loading d-none" id="vehicle_loading"></div>

                            <!-- Buttons -->
                            <div class="col-12 text-end mt-4">
                                <a href="{{url(LOGIN_USER_TYPE.'/vehicle')}}" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary" name="submit" value="submit">
                                    {{ __('messages.admin.submit') }}
                                </button>
                            </div>

                    </div>
                </div>
            </div><!-- End Right Col -->

            {!! Form::close() !!}
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ url('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ url('assets/js/form/forms-select.init.js') }}"></script>

    <!-- Datepicker js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>

    <!-- App js -->
    <script type="module" src="{{ url('assets/js/app.js') }}"></script>

    <script>
        $("#year").datepicker({
            format: "yyyy",
            viewMode: "years", 
            minViewMode: "years",
            autoclose: true,
            startDate: '1950',
            endDate: '<?php echo date('Y'); ?>'
        });

        // ── Driver change → dynamic documents ──
        document.getElementById('input_driver_name')?.addEventListener('change', function () {
            var driverId = this.value;
            var section = document.getElementById('vehicle-documents-section');
            var loading = document.getElementById('vehicle_loading');
            if (!driverId) {
                if (section) section.innerHTML = '<div class="text-muted">Select a driver to load documents</div>';
                return;
            }
            if (loading) loading.classList.remove('d-none');
            var loginType = '{{ LOGIN_USER_TYPE }}';
            var formData = new FormData();
            formData.append('document_for', 'Vehicle');
            formData.append('country_code', document.getElementById('user_country_code')?.value || '');
            formData.append('user_id', driverId);
            formData.append('vehicle_id', document.getElementById('vehicle_id')?.value || '0');
            fetch('/' + loginType + '/get_documents_html', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'text/html' },
                body: formData
            }).then(function (r) { return r.text(); }).then(function (html) {
                if (section) section.innerHTML = html;
                if (loading) loading.classList.add('d-none');
                // Re-init datepickers
                document.querySelectorAll('.document_expired.air-datepicker:not(.air-datepicker--initialized)').forEach(function (el) {
                    if (typeof AirDatepicker !== 'undefined') {
                        new AirDatepicker(el, { autoClose: true, minDate: new Date(), dateFormat: 'yyyy-mm-dd' });
                        el.classList.add('air-datepicker--initialized');
                    }
                });
            }).catch(function () {
                if (loading) loading.classList.add('d-none');
            });
        });
    </script>
@endsection
