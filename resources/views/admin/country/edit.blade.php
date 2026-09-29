@extends('admin.template')

@section('title', 'Edit Country | FabKin Admin & Dashboards Template')
@section('title-sub', 'Edit Country')
@section('pagetitle', 'Edit Country')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit_country/' . $result->id, 'class' => 'row g-5', 'id' => 'country_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_short_name">
                                    {{ __('messages.admin.manage_country.form.short_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('short_name', old('short_name', $result->short_name), [
                                    'class' => 'form-control',
                                    'id' => 'input_short_name',
                                    'placeholder' => __('messages.admin.manage_country.form.short_name'),
                                    'readonly' => true
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('short_name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_long_name">
                                    {{ __('messages.admin.manage_country.form.long_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('long_name', old('long_name', $result->long_name), [
                                    'class' => 'form-control',
                                    'id' => 'input_long_name',
                                    'placeholder' => __('messages.admin.manage_country.form.long_name')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('long_name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_iso3">
                                    {{ __('messages.admin.manage_country.form.iso3') }}
                                </label>
                                {!! Form::text('iso3', old('iso3', $result->iso3), [
                                    'class' => 'form-control',
                                    'id' => 'input_iso3',
                                    'placeholder' => __('messages.admin.manage_country.form.iso3')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('iso3') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_num_code">
                                    {{ __('messages.admin.manage_country.form.num_code') }}
                                </label>
                                {!! Form::text('num_code', old('num_code', $result->num_code), [
                                    'class' => 'form-control',
                                    'id' => 'input_num_code',
                                    'placeholder' => __('messages.admin.manage_country.form.num_code')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('num_code') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_phone_code">
                                    {{ __('messages.admin.manage_country.form.phone_code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('phone_code', old('phone_code', $result->phone_code), [
                                    'class' => 'form-control',
                                    'id' => 'input_phone_code',
                                    'placeholder' => __('messages.admin.manage_country.form.phone_code')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('phone_code') }}</span>
                            </div>

                            <!-- Mandatory Fields Note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right Panel (can be used for info, preview, actions, etc.) -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-5">
                            <!-- You can add helpful info, flags preview, or notes here in future -->
                            <div class="col-12">
                                <h5 class="card-title mb-4">{{ __('messages.admin.manage_country.edit_country') }}</h5>
                                <p class="text-muted">
                                    {{ __('messages.blade_static.update_the_country_details_below_the_short_name_iso_alph_98dd39b7') }}
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 mt-4">
                                <div class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE . '/country') }}" class="btn btn-light-primary">
                                        {{ __('messages.admin.cancel') }}
                                    </a>
                                    <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                        {{ __('messages.admin.submit') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End right col -->

            {!! Form::close() !!}

        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- Datepicker Js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection