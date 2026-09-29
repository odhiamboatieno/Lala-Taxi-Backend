@extends('admin.template')

@section('title', 'Edit Support Link | FabKin Admin & Dashboards Template')
@section('title-sub', 'Edit Support')
@section('pagetitle', 'Edit Support Link')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="driver_management">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => LOGIN_USER_TYPE.'/edit_support/'.$result->id, 'class' => 'row g-5', 'files' => true, 'id' => 'support_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_support.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', old('name', $result->name), [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => __('messages.admin.manage_support.form.name'),
                                    $editable ?? ''
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_link">
                                    @if($result->id == 1)
                                        {{ __('messages.admin.manage_support.form.number') }}
                                    @else
                                        {{ __('messages.admin.manage_support.form.link') }}
                                    @endif
                                    <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('link', old('link', $result->link), [
                                    'class' => 'form-control',
                                    'id' => 'input_link',
                                    'placeholder' => 'Link / Number'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('link') }}</span>

                                @if($result->id == 1)
                                    <small class="form-text text-muted">
                                        {{ __('messages.admin.manage_support.note') ?? 'Include country code (e.g. +49123456789)' }}
                                    </small>
                                @endif
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_support.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status', $result->status), [
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
                                {!! Form::select('owner', ['Rider' => 'Rider', 'Driver' => 'Driver', 'All' => 'All'], old('owner', $result->owner), [
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

                                <!-- Current image -->
                                @if($result->image)
                                    <div class="mt-3">
                                        <label class="form-label d-block">{{ __('messages.blade_static.current_image_0039d13e') }}</label>
                                        <img src="{{ url('images/support/' . $result->image) }}" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="max-height: 140px; object-fit: contain;"
                                             alt="{{ __('messages.blade_static.current_support_image_aabac959') }}">
                                    </div>
                                @endif

                                <!-- New image preview -->
                                <div class="mt-3" id="image-preview-container" style="display:none;">
                                    <label class="form-label d-block">{{ __('messages.blade_static.new_image_preview_8116ceb8') }}</label>
                                    <img id="image-preview" class="img-fluid rounded shadow-sm" 
                                         style="max-height: 180px; object-fit: contain;">
                                </div>
                            </div>

                            <!-- Mandatory note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right column: Help text & Actions -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.admin.manage_support.edit_support_form') }}</h5>

                        <p class="text-muted mb-4">
                            {{ __('messages.blade_static.update_the_support_help_link_details_you_can_change_the_75266a50') }}
                        </p>

                        <div class="alert alert-info mt-4">
                            <strong>{{ __('messages.blade_static.note_83423c19') }}</strong> {{ __('messages.blade_static.if_this_is_a_phone_number_id_1_always_include_the_countr_4be9a529') }}
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

    <!-- Image preview when selecting new file -->
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