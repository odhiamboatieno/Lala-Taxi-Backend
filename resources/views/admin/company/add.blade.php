@extends('admin.template')

@section('title', 'Add Company | FabKin Admin & Dashboards Template')
@section('title-sub', 'Add Company')
@section('pagetitle', 'Add Company')

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
            {!! Form::open(['url'=>'admin/add_company', 'files'=>true, 'id'=>'company_form']) !!}
            {!! Form::hidden('user_type','Company') !!}

            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">

                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_company_page.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', old('name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => 'Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_vat_number">
                                    {{ __('messages.admin.manage_company_page.form.vat_number') }}
                                </label>
                                {!! Form::text('vat_number', old('vat_number'), [
                                    'class' => 'form-control',
                                    'id' => 'input_vat_number',
                                    'placeholder' => 'VAT Number'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('vat_number') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_email">
                                    {{ __('messages.admin.manage_company_page.form.email') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('email', old('email'), [
                                    'class' => 'form-control',
                                    'id' => 'input_email',
                                    'placeholder' => 'Email'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_password">
                                    {{ __('messages.admin.manage_company_page.form.password') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::password('password', [
                                    'class' => 'form-control',
                                    'id' => 'input_password',
                                    'placeholder' => 'Password'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_country_code">
                                    {{ __('messages.admin.manage_company_page.form.country_code') }}<em class="text-danger">*</em>
                                </label>
                                {!! Form::select('country_code', $country_code_option, old('country_code'), [
                                    'class' => 'form-select',
                                    'id' => 'input_country_code',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('country_code') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="mobile_number">
                                    {{ __('messages.admin.manage_company_page.form.mobile_number') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('mobile_number', old('mobile_number'), [
                                    'class' => 'form-control',
                                    'id' => 'mobile_number',
                                    'placeholder' => 'Mobile Number'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_company_page.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive', 'Pending' => 'Pending'], old('status'), [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_service_fee">
                                    {{ __('messages.admin.manage_company_page.form.company_commission') }}<em class="text-danger">*</em>
                                </label>
                                <div class="input-group">
                                    {!! Form::text('company_commission', old('company_commission', 0), [
                                        'class' => 'form-control',
                                        'id' => 'input_service_fee',
                                        'placeholder' => 'Company Commission'
                                    ]) !!}
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('company_commission') }}</span>
                            </div>

                            <!-- Address Section -->
                            <div class="col-12">
                                <label class="form-label" for="address_line">
                                    {{ __('messages.admin.manage_company_page.form.address_line') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('address_line', old('address_line'), [
                                    'class' => 'form-control',
                                    'id' => 'address_line',
                                    'placeholder' => 'Address Line'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('address_line') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="city">
                                    {{ __('messages.admin.manage_company_page.form.city') }}
                                </label>
                                {!! Form::text('city', old('city'), [
                                    'class' => 'form-control',
                                    'id' => 'city',
                                    'placeholder' => 'City'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('city') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="state">
                                    {{ __('messages.admin.manage_company_page.form.state') }}
                                </label>
                                {!! Form::text('state', old('state'), [
                                    'class' => 'form-control',
                                    'id' => 'state',
                                    'placeholder' => 'State'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('state') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="postal_code">
                                    {{ __('messages.admin.manage_company_page.form.postal_code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('postal_code', old('postal_code'), [
                                    'class' => 'form-control',
                                    'id' => 'postal_code',
                                    'placeholder' => 'Postal Code'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                            </div>

                          

                            <!-- Loading Indicator -->
                            <div class="col-12">
                                <div class="loading d-none" id="company_loading"></div>
                            </div>

                            <!-- Mandatory Fields Note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                          

                           
                        </div>
                    </div>
                </div>
            </div><!-- End col -->

            <!-- Right Panel: Breadcrumb & Title -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                         <div class="col-xl-6">
                                <label class="form-label" for="input_profile">
                                    {{ __('messages.admin.manage_company_page.form.profile') }}
                                </label>
                                {!! Form::file('profile', [
                                    'class' => 'form-control',
                                    'id' => 'input_profile',
                                    'accept' => 'image/*'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('profile') }}</span>
                            </div>

                            <!-- Dynamic Company Documents -->
                            <div id="company-documents-section">
                                @include('admin.partials.driver_documents', ['documents' => '[]', 'expireLabel' => __('messages.blade_static.expire_date_16484486')])
                            </div>
                            <div class="loading d-none" id="company_loading"></div>
                            
                              <!-- Action Buttons -->
                            <div class="col-12 mt-4">
                                <div class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE . '/company') }}" class="btn btn-light-primary">
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
        </div><!-- End row -->
        {!! Form::close() !!}
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

    <!-- Custom Datepicker for Angular Fields -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Air Datepicker on dynamically added fields
            const observer = new MutationObserver(function () {
                document.querySelectorAll('.document_expired.air-datepicker:not(.air-datepicker--initialized)').forEach(el => {
                    new AirDatepicker(el, {
                        autoClose: true,
                        minDate: new Date(),
                        dateFormat: 'yyyy-mm-dd'
                    });
                    el.classList.add('air-datepicker--initialized');
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });

            // Initialize any existing on load
            document.querySelectorAll('.document_expired.air-datepicker').forEach(el => {
                if (!el.classList.contains('air-datepicker--initialized')) {
                    new AirDatepicker(el, {
                        autoClose: true,
                        minDate: new Date(),
                        dateFormat: 'yyyy-mm-dd'
                    });
                    el.classList.add('air-datepicker--initialized');
                }
            });
        });
    </script>
@endsection