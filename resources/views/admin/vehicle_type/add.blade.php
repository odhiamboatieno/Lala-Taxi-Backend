@extends('admin.template')

@section('title-sub', __('messages.admin.manage_vehicle_type.add_vehicle_type'))
@section('pagetitle', __('messages.admin.manage_vehicle_type.add_vehicle_type'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/add_vehicle_type', 'class' => 'row g-5', 'files' => true]) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_vehicle_type.form_title') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_vehicle_type.form.vehicle_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('vehicle_name', '', [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => 'Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_description">
                                    {{ __('messages.admin.manage_vehicle_type.form.description') }}
                                </label>
                                {!! Form::textarea('description', '', [
                                    'class' => 'form-control',
                                    'id' => 'input_description',
                                    'placeholder' => 'Description',
                                    'rows' => 3
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            </div>

                           
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb & Info -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12">
                                <label class="form-label" for="input_vehicle_back">
                                    {{ __('messages.admin.manage_vehicle_type.form.vehicle_image') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::file('vehicle_image', [
                                    'class' => 'form-control',
                                    'id' => 'input_vehicle_back',
                                    'accept' => 'image/*'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vehicle_image') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_active_image">
                                    {{ __('messages.admin.manage_vehicle_type.form.vehicle_active_image') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::file('active_image', [
                                    'class' => 'form-control',
                                    'id' => 'input_active_image',
                                    'accept' => 'image/*'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('active_image') }}</span>
                            </div>

                            @if(\Illuminate\Support\Facades\Schema::hasColumn('car_type', 'app_image'))
                                <div class="col-12">
                                    <label class="form-label" for="input_app_image">
                                        {{ __('messages.admin.manage_vehicle_type.form.vehicle_app_icon') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::file('app_image', [
                                        'class' => 'form-control',
                                        'id' => 'input_app_image',
                                        'accept' => 'image/*'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('app_image') }}</span>
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label" for="input_pool">
                                    {{ __('messages.admin.manage_vehicle_type.form.is_pool') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('is_pool', ['No' => 'No', 'Yes' => 'Yes'], '', [
                                    'class' => 'form-select',
                                    'id' => 'input_pool'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('is_pool') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_vehicle_type.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], '', [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => __('messages.admin.manage_vehicle_type.form.status')
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
            </div><!-- End Right Col -->
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
