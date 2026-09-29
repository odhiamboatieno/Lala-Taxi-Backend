@extends('admin.template')

@section('title-sub', __('messages.admin.manage_vehicle_make.edit_vehicle_make'))
@section('pagetitle', __('messages.admin.manage_vehicle_make.edit_vehicle_make'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="driver_management">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => LOGIN_USER_TYPE.'/edit-vehicle-make/'.$result->id, 'class' => 'row g-5', 'files' => true]) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_vehicle_make.edit_form_title') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_make_name">
                                    {{ __('messages.admin.manage_vehicle_make.form.make') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('make_vehicle_name', $result->make_vehicle_name, [
                                    'class' => 'form-control',
                                    'id' => 'input_make_name',
                                    'placeholder' => 'Make Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('make_vehicle_name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_vehicle_make.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], $result->status, [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-12 text-end">
                                <a href="{{ url(LOGIN_USER_TYPE.'/vehicle_make') }}" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary" name="submit" value="submit">
                                    {{ __('messages.admin.submit') }}
                                </button>
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

           
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