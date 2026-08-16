@extends('admin.template')

@section('title-sub', 'Add Country')
@section('pagetitle', 'Add Country')

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
                            {!! Form::open(['url' => 'admin/add_country', 'class' => 'row g-5']) !!}

                            <!-- === COUNTRY DETAILS === -->
                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_country.form.country_details') }}</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="input_short_name">
                                    {{ __('messages.admin.manage_country.form.short_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('short_name', old('short_name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_short_name',
                                    'placeholder' => 'e.g. US'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('short_name') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="input_long_name">
                                    {{ __('messages.admin.manage_country.form.long_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('long_name', old('long_name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_long_name',
                                    'placeholder' => 'e.g. United States'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('long_name') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="input_iso3">
                                    {{ __('messages.admin.manage_country.form.iso3') }}
                                </label>
                                {!! Form::text('iso3', old('iso3'), [
                                    'class' => 'form-control',
                                    'id' => 'input_iso3',
                                    'placeholder' => 'e.g. USA'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('iso3') }}</span>
                            </div>

                        
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb & Info -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                           <div class="col-md-12">
                                <label class="form-label" for="input_num_code">
                                    {{ __('messages.admin.manage_country.form.num_code') }}
                                </label>
                                {!! Form::text('num_code', old('num_code'), [
                                    'class' => 'form-control',
                                    'id' => 'input_num_code',
                                    'placeholder' => 'e.g. 840'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('num_code') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="input_phone_code">
                                    {{ __('messages.admin.manage_country.form.phone_code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('phone_code', old('phone_code'), [
                                    'class' => 'form-control',
                                    'id' => 'input_phone_code',
                                    'placeholder' => 'e.g. +1'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('phone_code') }}</span>
                            </div>

                            <!-- === MANDATORY & BUTTONS === -->
                            <div class="col-12 mt-4">
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
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