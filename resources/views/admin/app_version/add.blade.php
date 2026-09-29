@extends('admin.template')

@section('title', 'Add App Version | FabKin Admin & Dashboards Template')
@section('title-sub', 'Add App Version')
@section('pagetitle', 'Add Mobile App Version')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/add_app_version', 'class' => 'row g-5']) !!}

                            <!-- === APP VERSION DETAILS === -->
                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_mobile_app_version.form.app_version_details') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_version">
                                    {{ __('messages.admin.manage_mobile_app_version.form.version') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('version', old('version'), [
                                    'class' => 'form-control',
                                    'id' => 'input_version',
                                    'placeholder' => 'e.g. 2.1.5'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('version') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_device_type">
                                    {{ __('messages.admin.manage_mobile_app_version.form.device_type') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('device_type', [
                                    '1' => 'Apple',
                                    '2' => 'Android'
                                ], old('device_type'), [
                                    'class' => 'form-select',
                                    'id' => 'input_device_type',
                                    'placeholder' => 'Select Device Type'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('device_type') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_user_type">
                                    {{ __('messages.admin.manage_mobile_app_version.form.user_type') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('user_type', [
                                    '0' => 'Rider',
                                    '1' => 'Driver'
                                ], old('user_type'), [
                                    'class' => 'form-select',
                                    'id' => 'input_user_type',
                                    'placeholder' => 'Select User Type'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('user_type') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_force_update">
                                    {{ __('messages.admin.manage_mobile_app_version.form.force_update') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('force_update', [
                                    '1' => 'Yes',
                                    '0' => 'No'
                                ], old('force_update'), [
                                    'class' => 'form-select',
                                    'id' => 'input_force_update',
                                    'placeholder' => 'Select Option'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('force_update') }}</span>
                            </div>

                            <!-- === MANDATORY & BUTTONS === -->
                            <div class="col-12 mt-4">
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-12 text-end">
                                <a href="{{ url('admin/mobile_app_version') }}" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </a>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">
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
    </main><!-- End layout-wrapper -->
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection