@extends('admin.template')

@section('title-sub', __('messages.admin.manage_vehicles.edit_vehicles'))
@section('pagetitle', __('messages.admin.manage_vehicles.edit_vehicles'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
    <!-- Datepicker css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="vehicle_management" ng-cloak ng-init='vehicle_id="{{ $result->id }}"'>

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open([
                                'url' => LOGIN_USER_TYPE . '/edit_vehicle/' . $result->id,
                                'class' => 'row g-5 vehicle_form',
                                'files' => true,
                                'id' => 'vehicle_form',
                            ]) !!}
                            {!! Form::hidden('user_country_code', @$result->user->country->phone_code, ['id' => 'user_country_code']) !!}
                            {!! Form::hidden('user_gender', $result->user->gender, ['id' => 'user_gender']) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_vehicles.edit_vehicle_form') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Company Information -->
                            @if (LOGIN_USER_TYPE != 'company')
                                <div class="col-12" ng-init='company_name = "{{ $result->company_id }}"'>
                                    <label class="form-label" for="input_company_name">
                                        {{ __('messages.admin.manage_vehicles.form.company_name') }} <em class="text-danger">*</em>
                                    </label>
                                    <div ng-init='get_driver()'>
                                        {!! Form::text('company_name', $company->name, [
                                            'class' => 'form-control',
                                            'id' => 'input_company_name',
                                            'readonly' => 'true',
                                        ]) !!}
                                    </div>
                                </div>
                            @else
                                <span ng-init='company_name="{{ Auth::guard('company')->user()->id }}";get_driver()'></span>
                            @endif

                            <!-- Driver Information -->
                            <div class="col-12" ng-init='driver_name = "{{ $result->user_id }}";selectedDriver={{ $result->user_id }}'>
                                <label class="form-label" for="driver">
                                    {{ __('messages.admin.manage_vehicles.form.driver_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::hidden('driver_name', $result->user_id) !!}
                                {!! Form::text(
                                    'driver',
                                    $result->user->first_name . ' ' . $result->user->last_name . ' - ' . $result->user->id,
                                    ['class' => 'form-control', 'id' => 'driver', 'readonly' => 'true'],
                                ) !!}
                            </div>

                            <!-- Status -->
                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_vehicles.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], $result->status, [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select',
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <!-- Vehicle Make & Model -->
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle_make">
                                    {{ __('messages.admin.manage_vehicles.form.make') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('vehicle_make_id', $make, $result->vehicle_make_id, [
                                    'class' => 'form-select',
                                    'id' => 'vehicle_make',
                                    'placeholder' => 'Select',
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_make_id') }}</span>
                            </div>

                            <div class="col-md-6" ng-init='vehicle_model_id="{{ $result->vehicle_model_id }}";'>
                                <label class="form-label" for="vehicle_model">
                                    {{ __('messages.admin.manage_vehicles.form.model') }} <em class="text-danger">*</em>
                                </label>
                                <span class="loading form-control" id="model_loading" style="display: none;"></span>
                                {!! Form::select('vehicle_model_id', [], '', [
                                    'class' => 'form-select',
                                    'id' => 'vehicle_model',
                                    'placeholder' => 'Select',
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_model_id') }}</span>
                            </div>

                            <!-- Vehicle Type -->
                            <div class="col-12">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_vehicles.form.vehicle_type') }} <em class="text-danger">*</em>
                                </label>
                                <div class="row">
                                    @php $vehicle_types = explode(',', $result->vehicle_id); @endphp
                                    @foreach ($car_type as $type)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="vehicle_type[]" id="vehicle_type_{{ $type->id }}" 
                                                       class="form-check-input vehicle_type" value="{{ $type->id }}"
                                                       data-error-placement="container" data-error-container="#vehicle_type_error"
                                                       {{ in_array($type->id, $vehicle_types) ? 'checked' : '' }} />
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
                                        {{ Form::radio('default', '1', $result->default_type == '1' ? true : false, [
                                            'class' => 'form-check-input default',
                                            'id' => 'default_yes',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#default_error'
                                        ]) }}
                                        <label class="form-check-label" for="default_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('default', '0', $result->default_type == '0' ? true : false, [
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
                                        {{ Form::radio('handicap', '1', in_array('2', $options) ? true : false, [
                                            'class' => 'form-check-input handicap',
                                            'id' => 'handicap_yes',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#handicap_error'
                                        ]) }}
                                        <label class="form-check-label" for="handicap_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('handicap', '0', !in_array('2', $options) ? true : false, [
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
                                        {{ Form::radio('child_seat', '1', in_array('3', $options) ? true : false, [
                                            'class' => 'form-check-input child_seat',
                                            'id' => 'child_seat_yes',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#child_seat_error'
                                        ]) }}
                                        <label class="form-check-label" for="child_seat_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('child_seat', '0', !in_array('3', $options) ? true : false, [
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
                                        {{ Form::radio('request_from', '1', in_array('1', $options) ? true : false, [
                                            'class' => 'form-check-input request_from',
                                            'id' => 'request_from_female',
                                            'data-error-placement' => 'container',
                                            'data-error-container' => '#request_from_error'
                                        ]) }}
                                        <label class="form-check-label" for="request_from_female">{{ __('messages.blade_static.female_b7c17e97') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('request_from', '0', !in_array('1', $options) ? true : false, [
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
                                {!! Form::text('vehicle_number', @$result->vehicle_number, [
                                    'class' => 'form-control',
                                    'id' => 'vehicle_number',
                                    'placeholder' => __('messages.admin.manage_vehicles.form.vehicle_number'),
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_number') }}</span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="color">
                                    {{ __('messages.admin.manage_vehicles.form.color') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('color', @$result->color, [
                                    'class' => 'form-control',
                                    'id' => 'color',
                                    'placeholder' => __('messages.admin.manage_vehicles.form.color'),
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('color') }}</span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="year">
                                    {{ __('messages.admin.manage_vehicles.form.year') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('year', @$result->year, [
                                    'class' => 'form-control',
                                    'id' => 'year',
                                    'placeholder' => __('messages.admin.manage_vehicles.form.year'),
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('year') }}</span>
                            </div>

                            <input type="hidden" id="vehicle_id" value="{{ $result->id }}">

                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                            <p ng-init='vehicle_doc="";errors=@json($errors->getMessages());'></p>

                            <!-- Vehicle Documents -->
                            <div class="col-12">
                                <div class="loading d-none" id="vehicle_loading"></div>
                            </div>

                            <div class="col-12" ng-repeat="doc in vehicle_doc" ng-cloak ng-if="vehicle_doc">
                                <div class="border-top pt-4 mt-4">
                                    <h6 class="text-primary mb-3">@{{doc.document_name}}</h6>
                                    
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <label class="form-label">@{{doc.document_name}} <em class="text-danger">*</em></label>
                                            <input type="file" name="file_@{{doc.id}}" class="form-control">
                                            <span class="text-danger">@{{ errors['file_'+doc.id][0] }}</span>
                                            
                                            <div class="mt-2">
                                                <div class="license-img" ng-if="doc.document">
                                                    <a href="@{{doc.document}}" target="_blank" class="d-inline-block">
                                                        <img style="width: auto;height: 100px;object-fit: contain;padding-top: 5px" 
                                                             ng-src="@{{doc.document}}" alt="@{{doc.document_name}}">
                                                    </a>
                                                </div>
                                                <div class="license-img" ng-if="!doc.document">
                                                    <img style="width: auto;height: 100px;object-fit: contain;padding-top: 5px" 
                                                         src="{{ url('images/driver_doc.png') }}" alt="{{ __('messages.blade_static.default_document_c87940eb') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12" ng-if="doc.expiry_required=='1'">
                                            <label class="form-label">{{ __('messages.admin.manage_vehicles.form.expire_date') }} <em class="text-danger">*</em></label>
                                            <input type="text" name="expired_date_@{{doc.id}}" min="{{ date('Y-m-d') }}" 
                                                   value="@{{doc.expired_date}}" class="form-control document_expired" 
                                                   placeholder="{{ __('messages.blade_static.expire_date_b5b8f80b') }}" autocomplete="off">
                                            <span class="text-danger">@{{ errors['expired_date_'+doc.id][0] }}</span>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">@{{doc.document_name}} {{ __('messages.admin.manage_vehicles.form.status') }} <em class="text-danger">*</em></label>
                                            <select class="form-select" name='@{{doc.doc_name}}_status'>
                                                <option value="0" ng-selected="doc.status==0">{{ __('messages.blade_static.pending_96f608c1') }}</option>
                                                <option value="1" ng-selected="doc.status==1">{{ __('messages.blade_static.approved_41b81eb8') }}</option>
                                                <option value="2" ng-selected="doc.status==2">{{ __('messages.blade_static.rejected_27eeb7a2') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 text-end">
                                <a href="{{ url(LOGIN_USER_TYPE . '/vehicle') }}" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary" name="submit" value="submit">
                                    {{ __('messages.admin.submit') }}
                                </button>
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
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="assets/js/form/forms-select.init.js"></script>

    <!-- Datepicker js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>

    <!-- App js -->
    <script type="module" src="assets/js/app.js"></script>

    <script>
        $("#year").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: true,
            startDate: '1950',
            endDate: '<?php echo date('Y'); ?>'
        });
    </script>
@endsection