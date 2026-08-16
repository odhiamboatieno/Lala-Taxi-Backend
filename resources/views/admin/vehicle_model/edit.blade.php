@extends('admin.template')

@section('title-sub', __('messages.admin.manage_vehicle_model.edit_vehicle_model'))
@section('pagetitle', __('messages.admin.manage_vehicle_model.edit_vehicle_model'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
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
                            {!! Form::open(['url' => LOGIN_USER_TYPE.'/edit-vehicle_model/'.$result->id, 'class' => 'row g-5', 'files' => true]) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_vehicle_model.edit_form_title') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_vehicle_make">
                                    {{ __('messages.admin.manage_vehicle_model.form.vehicle_make') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('vehicle_make_id', $make, $result->vehicle_make_id, [
                                    'class' => 'form-select',
                                    'id' => 'input_vehicle_make',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_make_id') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_model_name">
                                    {{ __('messages.admin.manage_vehicle_model.form.model') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('model_name', $result->model_name, [
                                    'class' => 'form-control',
                                    'id' => 'input_model_name',
                                    'placeholder' => 'Vehicle Model Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('model_name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_vehicle_model.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], $result->status, [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => __('messages.admin.manage_vehicle_model.form.status')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" name="cancel" value="cancel" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </button>
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
</main>    
@endsection

@section('js')
    <!-- Select js -->
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="assets/js/form/forms-select.init.js"></script>

    <!-- App js -->
    <script type="module" src="assets/js/app.js"></script>
@endsection