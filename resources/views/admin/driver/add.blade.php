@extends('admin.template')

@section('title', 'Add Driver | FabKin Admin & Dashboards Template')
@section('title-sub', 'Add Driver')
@section('pagetitle', 'Add Driver')

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
            {!! Form::open(['url' => LOGIN_USER_TYPE.'/add_driver', 'files' => true]) !!}
            {{ Form::hidden('user_id', '', ['id' => 'user_id']) }}
            {!! Form::hidden('user_type', 'Driver') !!}

            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">

                            <!-- === PERSONAL INFO === -->
                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_drivers_page.form.personal_info') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_first_name">
                                    {{ __('messages.admin.manage_drivers_page.form.first_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('first_name', old('first_name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_first_name',
                                    'placeholder' => 'First Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('first_name') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_last_name">
                                    {{ __('messages.admin.manage_drivers_page.form.last_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('last_name', old('last_name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_last_name',
                                    'placeholder' => 'Last Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('last_name') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_email">
                                    {{ __('messages.admin.manage_drivers_page.form.email') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('email', old('email'), [
                                    'class' => 'form-control',
                                    'id' => 'input_email',
                                    'placeholder' => 'Email'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_password">
                                    {{ __('messages.admin.manage_drivers_page.form.password') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::password('password', [
                                    'class' => 'form-control',
                                    'id' => 'input_password',
                                    'placeholder' => 'Password'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_country_code">
                                    {{ __('messages.admin.manage_drivers_page.form.country_code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('country_id', $country_code_option, old('country_id'), [
                                    'class' => 'form-select',
                                    'id' => 'input_country_code',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('country_id') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_drivers_page.form.gender') }} <em class="text-danger">*</em>
                                </label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        {{ Form::radio('gender', '1', old('gender') == '1', ['class' => 'form-check-input', 'id' => 'g_male']) }}
                                        <label class="form-check-label" for="g_male">
                                            {{ __('messages.profile.male') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        {{ Form::radio('gender', '2', old('gender') == '2', ['class' => 'form-check-input', 'id' => 'g_female']) }}
                                        <label class="form-check-label" for="g_female">
                                            {{ __('messages.profile.female') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="text-danger">{{ $errors->first('gender') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="mobile_number">
                                    {{ __('messages.admin.manage_drivers_page.form.mobile_number') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('mobile_number', old('mobile_number'), [
                                    'class' => 'form-control',
                                    'id' => 'mobile_number',
                                    'placeholder' => 'Mobile Number'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
                            </div>

                            @if (LOGIN_USER_TYPE != 'company')
                            <div class="col-md-6">
                                <label class="form-label" for="input_company_name">
                                    {{ __('messages.admin.manage_drivers_page.form.company_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('company_name', $company, old('company_name'), [
                                    'class' => 'form-select',
                                    'id' => 'input_company_name',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('company_name') }}</span>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label" for="input_driver_level">
                                    Driver Level
                                </label>
                                {!! Form::select('driver_level_id', $driver_level_options ?? [], old('driver_level_id'), [
                                    'class' => 'form-select',
                                    'id' => 'input_driver_level',
                                    'placeholder' => __('messages.admin.manage_drivers_page.auto_assign_after_save')
                                ]) !!}
                                <small class="text-muted">{{ __('messages.admin.manage_drivers_page.auto_assign_level_hint') }}</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_drivers_page.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive', 'Pending' => 'Pending', 'Car_details' => 'Car details', 'Document_details' => 'Document details'], old('status', 'Pending'), ['class' => 'form-select', 'id' => 'input_status']) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <!-- === ADDRESS INFO === -->
                            <div class="col-12 mt-4">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_drivers_page.form.address_info') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="address_line1">
                                    {{ __('messages.admin.manage_drivers_page.form.address_line1') }}
                                </label>
                                {!! Form::text('address_line1', old('address_line1'), [
                                    'class' => 'form-control',
                                    'id' => 'address_line1',
                                    'placeholder' => 'Address Line 1'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('address_line1') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="address_line2">
                                    {{ __('messages.admin.manage_drivers_page.form.address_line2') }}
                                </label>
                                {!! Form::text('address_line2', old('address_line2'), [
                                    'class' => 'form-control',
                                    'id' => 'address_line2',
                                    'placeholder' => 'Address Line 2'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('address_line2') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="city">
                                    {{ __('messages.admin.manage_drivers_page.form.city') }}
                                </label>
                                {!! Form::text('city', old('city'), [
                                    'class' => 'form-control',
                                    'id' => 'city',
                                    'placeholder' => 'City'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('city') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="state">
                                    {{ __('messages.admin.manage_drivers_page.form.state') }}
                                </label>
                                {!! Form::text('state', old('state'), [
                                    'class' => 'form-control',
                                    'id' => 'state',
                                    'placeholder' => 'State'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('state') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="postal_code">
                                    {{ __('messages.admin.manage_drivers_page.form.postal_code') }}
                                </label>
                                {!! Form::text('postal_code', old('postal_code'), [
                                    'class' => 'form-control',
                                    'id' => 'postal_code',
                                    'placeholder' => 'Postal Code'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb & Info -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <!-- === DOCUMENTS === -->
                        <div id="documents-section">
                            @include('admin.partials.driver_documents', ['documents' => $driver_doc])
                        </div>
                        <div class="loading d-none" id="document_loading"></div>

                        <!-- === BANK DETAILS === -->
                            @if(LOGIN_USER_TYPE != 'company' || Auth::guard('company')->user()->id != 1)
                            <div class="col-12 mt-4">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_drivers_page.form.bank_details') }}</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="account_holder_name">
                                    {{ __('messages.admin.manage_drivers_page.form.account_holder_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('account_holder_name', old('account_holder_name'), [
                                    'class' => 'form-control',
                                    'id' => 'account_holder_name',
                                    'placeholder' => 'Account Holder Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('account_holder_name') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="account_number">
                                    {{ __('messages.admin.manage_drivers_page.form.account_number') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('account_number', old('account_number'), [
                                    'class' => 'form-control',
                                    'id' => 'account_number',
                                    'placeholder' => 'Account Number'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('account_number') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="bank_name">
                                    {{ __('messages.admin.manage_drivers_page.form.name_of_bank') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('bank_name', old('bank_name'), [
                                    'class' => 'form-control',
                                    'id' => 'bank_name',
                                    'placeholder' => 'Name of Bank'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('bank_name') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="bank_location">
                                    {{ __('messages.admin.manage_drivers_page.form.bank_location') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('bank_location', old('bank_location'), [
                                    'class' => 'form-control',
                                    'id' => 'bank_location',
                                    'placeholder' => 'Bank Location'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('bank_location') }}</span>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="bank_code">
                                    {{ __('messages.admin.manage_drivers_page.form.code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('bank_code', old('bank_code'), [
                                    'class' => 'form-control',
                                    'id' => 'bank_code',
                                    'placeholder' => 'BIC/SWIFT Code'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('bank_code') }}</span>
                            </div>
                            @endif

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

                    </div>
                </div>
            </div><!-- End Right Col -->

            {!! Form::close() !!}
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
    </main>
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- Datepicker Js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Datepicker initialization ──
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

        // ── Country code → dynamic documents ──
        var countrySelect = document.getElementById('input_country_code');
        if (countrySelect) {
            countrySelect.addEventListener('change', function () {
                var loading = document.getElementById('document_loading');
                var section = document.getElementById('documents-section');
                if (loading) loading.classList.remove('d-none');
                var login_type = '{{ LOGIN_USER_TYPE }}';
                var formData = new FormData();
                formData.append('document_for', 'Driver');
                formData.append('country_code', this.value);
                formData.append('user_id', document.getElementById('user_id').value || '');
                fetch('/' + login_type + '/get_documents_html', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'text/html' },
                    body: formData
                }).then(function (r) { return r.text(); }).then(function (html) {
                    if (section) section.innerHTML = html;
                    if (loading) loading.classList.add('d-none');
                }).catch(function () {
                    if (loading) loading.classList.add('d-none');
                });
            });
        }

        // ── Company name → bank details ──
        var companySelect = document.getElementById('input_company_name');
        if (companySelect) {
            if (companySelect.value == '1') {
                document.querySelectorAll('.bank_detail').forEach(function (el) { el.style.display = 'none'; });
            }
            companySelect.addEventListener('change', function () {
                document.querySelectorAll('.bank_detail').forEach(function (el) {
                    el.style.display = this.value == '1' ? 'none' : '';
                }, this);
            });
        }
    });
    </script>
@endsection
