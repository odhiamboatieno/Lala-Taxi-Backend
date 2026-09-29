@extends('admin.template')

@section('title', 'Edit Meta | FabKin Admin & Dashboards Template')
@section('title-sub', 'Edit Meta')
@section('pagetitle', 'Edit Meta')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
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
                            {!! Form::open(['url' => 'admin/edit_meta/' . $result->id, 'class' => 'row g-5', 'id' => 'meta_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_url">
                                    {{ __('messages.admin.manage_metas.form.page_url') }}
                                </label>
                                {!! Form::text('url', old('url', $result->url), [
                                    'class' => 'form-control',
                                    'id' => 'input_url',
                                    'placeholder' => 'Page URL',
                                    'readonly' => true
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('url') }}</span>
                                <small class="form-text text-muted">
                                    {{ __('messages.blade_static.the_page_url_is_fixed_and_cannot_be_changed_506ab696') }}
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_title">
                                    {{ __('messages.admin.manage_metas.form.page_title') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('title', old('title', $result->title), [
                                    'class' => 'form-control',
                                    'id' => 'input_title',
                                    'placeholder' => 'Page Title'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('title') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_description">
                                    {{ __('messages.admin.manage_metas.form.meta_description') }}
                                </label>
                                {!! Form::textarea('description', old('description', $result->description), [
                                    'class' => 'form-control',
                                    'id' => 'input_description',
                                    'placeholder' => 'Meta Description',
                                    'rows' => 4
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                                <small class="form-text text-muted">
                                    {{ __('messages.blade_static.recommended_length_150_160_characters_415c2bac') }}
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_keywords">
                                    {{ __('messages.admin.manage_metas.form.keywords') }}
                                </label>
                                {!! Form::textarea('keywords', old('keywords', $result->keywords), [
                                    'class' => 'form-control',
                                    'id' => 'input_keywords',
                                    'placeholder' => 'Meta Keywords (comma separated)',
                                    'rows' => 3
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('keywords') }}</span>
                                <small class="form-text text-muted">
                                    {{ __('messages.blade_static.example_travel_booking_hotel_vacation_e2325d6e') }}
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

            <!-- Right column: Info & Actions -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-5">
                            <div class="col-12">
                                <h5 class="card-title mb-4">{{ __('messages.admin.manage_metas.edit_meta_form') }}</h5>
                                <p class="text-muted mb-4">
                                    {{ __('messages.blade_static.update_the_seo_metadata_for_this_page_the_url_is_system_e3e0b89c') }}
                                </p>

                                <!-- Optional: you can add a preview or tips here later -->
                                <!-- Example:
                                <div class="alert alert-info">
                                    <strong>{{ __('messages.blade_static.tip_2ee750e0') }}</strong> {{ __('messages.blade_static.good_meta_titles_are_50_60_characters_descriptions_shoul_5bce4cb6') }}
                                </div>
                                -->
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 mt-5">
                                <div class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE . '/metas') }}" class="btn btn-light-primary">
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
@endsection