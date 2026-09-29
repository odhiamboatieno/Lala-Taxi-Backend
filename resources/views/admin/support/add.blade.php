@extends('admin.template')

@section('title', 'Add Support Link | FabKin Admin & Dashboards Template')
@section('title-sub', 'Add Support')
@section('pagetitle', 'Add Support Link')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper"  ng-controller="driver_management">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => LOGIN_USER_TYPE.'/add_support', 'class' => 'row g-5', 'files' => true, 'id' => 'support_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_support.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', old('name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => __('messages.admin.manage_support.form.name')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_link">
                                    {{ __('messages.admin.manage_support.form.link') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('link', old('link'), [
                                    'class' => 'form-control',
                                    'id' => 'input_link',
                                    'placeholder' => __('messages.admin.manage_support.form.link')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('link') }}</span>
                                <small class="form-text text-muted">
                                    {{ __('messages.admin.manage_support.form.link_note') ?? 'If this is a phone number, include country code (e.g. +49123456789)' }}
                                </small>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_support.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status'), [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_owner">
                                    {{ __('messages.admin.manage_support.form.owner') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('owner', ['Rider' => 'Rider', 'Driver' => 'Driver', 'All' => 'All'], old('owner'), [
                                    'class' => 'form-select',
                                    'id' => 'input_owner',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('owner') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_image">
                                    {{ __('messages.admin.manage_support.form.image') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::file('image', [
                                    'class' => 'form-control',
                                    'id' => 'input_image',
                                    'accept' => 'image/*'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('image') }}</span>
                                <small class="form-text text-muted">
                                    {{ __('messages.blade_static.recommended_512_512_px_png_or_jpg_format_b37f2b9d') }}
                                </small>
                            </div>

                            <!-- Mandatory note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right column: Preview / Help / Actions -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.admin.manage_support.add_support_form') }}</h5>

                        <p class="text-muted mb-4">
                            {{ __('messages.blade_static.add_a_new_support_help_link_website_phone_number_email_s_240e02b2') }}
                        </p>

                        <!-- Optional preview area (you can show uploaded image preview with JS) -->
                        <div class="mb-4" id="image-preview-container" style="display:none;">
                            <h6 class="mb-2">{{ __('messages.blade_static.image_preview_659e19b4') }}</h6>
                            <img id="image-preview" class="img-fluid rounded shadow-sm" style="max-height:180px; object-fit:contain;">
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-5 text-end">
                            <a href="{{ url(LOGIN_USER_TYPE . '/support') }}" class="btn btn-light-primary">
                                {{ __('messages.admin.cancel') }}
                            </a>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                {{ __('messages.admin.submit') }}
                            </button>
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

    <!-- Image preview on file select -->
    <script>
        document.getElementById('input_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('image-preview');
                    const container = document.getElementById('image-preview-container');
                    preview.src = event.target.result;
                    container.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection