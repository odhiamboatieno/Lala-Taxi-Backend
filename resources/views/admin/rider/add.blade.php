@extends('admin.template')

@section('title-sub', 'Add Rider')
@section('pagetitle', 'Add Rider')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Google Places Autocomplete (if needed) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="destination_admin">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/add_rider', 'class' => 'row g-5']) !!}
                            {!! Form::hidden('user_type', 'Rider') !!}

                            <!-- === PERSONAL INFO === -->
                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_riders_page.form.personal_info') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_first_name">
                                    {{ __('messages.admin.manage_riders_page.form.first_name') }} <em class="text-danger">*</em>
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
                                    {{ __('messages.admin.manage_riders_page.form.last_name') }} <em class="text-danger">*</em>
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
                                    {{ __('messages.admin.manage_riders_page.form.email') }} <em class="text-danger">*</em>
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
                                    {{ __('messages.admin.manage_riders_page.form.password') }} <em class="text-danger">*</em>
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
                                    {{ __('messages.admin.manage_riders_page.form.country_code') }} <em class="text-danger">*</em>
                                </label>
                                <select class="form-select" id="input_country_code" name="country_code">
                                    @foreach($country_code_option as $country_code)
                                        <option value="{{ $country_code->phone_code }}"
                                                data-id="{{ $country_code->id }}"
                                                {{ old('country_id') == $country_code->id ? 'selected' : '' }}>
                                            {{ $country_code->long_name }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! Form::hidden('country_id', old('country_id'), ['id' => 'country_id']) !!}
                                <span class="text-danger">{{ $errors->first('country_code') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_riders_page.form.gender') }} <em class="text-danger">*</em>
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
                                    {{ __('messages.admin.manage_riders_page.form.mobile_number') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('mobile_number', old('mobile_number'), [
                                    'class' => 'form-control',
                                    'id' => 'mobile_number',
                                    'placeholder' => 'Mobile Number'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_riders_page.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status'), [
                                    'class' => 'form-select',
                                    'id' => 'input_status'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_rider_level">{{ __('messages.admin.manage_riders_page.customer_level') }}</label>
                                {!! Form::select('rider_level_id', $rider_level_options ?? [], old('rider_level_id'), [
                                    'class' => 'form-select',
                                    'id' => 'input_rider_level',
                                    'placeholder' => __('messages.admin.manage_riders_page.auto_assign_after_save')
                                ]) !!}
                            </div>


                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb & Info -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                                                   <!-- === LOCATION INFO === -->
                            <div class="col-12 mt-4">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_riders_page.form.location_info') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_home_location">
                                    {{ __('messages.admin.manage_riders_page.form.home_location') }}
                                </label>
                                <div class="autocomplete-input-container position-relative">
                                    <div class="autocomplete-input">
                                        {!! Form::text('home_location', old('home_location'), [
                                            'class' => 'form-control',
                                            'id' => 'input_home_location',
                                            'placeholder' => 'Home Location',
                                            'autocomplete' => 'off'
                                        ]) !!}
                                    </div>
                                    <ul class="autocomplete-results home-autocomplete-results list-group position-absolute w-100 mt-1 shadow-sm" style="z-index: 1050; display: none;"></ul>
                                </div>
                                <span class="text-danger">{{ $errors->first('home_location') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_work_location">
                                    {{ __('messages.admin.manage_riders_page.form.office_location') }}
                                </label>
                                <div class="autocomplete-input-container position-relative">
                                    <div class="autocomplete-input">
                                        {!! Form::text('work_location', old('work_location'), [
                                            'class' => 'form-control',
                                            'id' => 'input_work_location',
                                            'placeholder' => 'Work Location',
                                            'autocomplete' => 'off'
                                        ]) !!}
                                    </div>
                                    <ul class="autocomplete-results work-autocomplete-results list-group position-absolute w-100 mt-1 shadow-sm" style="z-index: 1050; display: none;"></ul>
                                </div>
                                <span class="text-danger">{{ $errors->first('work_location') }}</span>
                            </div>

                            <!-- Hidden Lat/Lng Fields -->
                            {!! Form::hidden('home_latitude', old('home_latitude'), ['id' => 'home_latitude']) !!}
                            {!! Form::hidden('home_longitude', old('home_longitude'), ['id' => 'home_longitude']) !!}
                            {!! Form::hidden('work_latitude', old('work_latitude'), ['id' => 'work_latitude']) !!}
                            {!! Form::hidden('work_longitude', old('work_longitude'), ['id' => 'work_longitude']) !!}

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
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- jQuery Autocomplete -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <!-- Country Code Sync -->
    <script>
        document.getElementById('input_country_code').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const countryId = selectedOption.getAttribute('data-id');
            document.getElementById('country_id').value = countryId;
        });

        // Trigger on load if old() exists
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('input_country_code');
            if (select.value) {
                const event = new Event('change');
                select.dispatchEvent(event);
            }
        });
    </script>

    <!-- Autocomplete Initialization (Preserve Existing Behavior) -->
    <script>
        $(document).ready(function() {
            // Home Location
            $('#input_home_location').autoComplete({
                minChars: 2,
                source: function(term, response) {
                    $.getJSON('/api/places-autocomplete', { q: term }, function(data) {
                        response(data);
                    });
                },
                renderItem: function(item, search) {
                    return '<div class="autocomplete-suggestion" data-val="' + item.description + '" data-lat="' + item.lat + '" data-lng="' + item.lng + '">{{ __('messages.blade_static.item_description_9b2f16ea') }}</div>';
                },
                onSelect: function(e, term, item) {
                    $('#input_home_location').val(item.data('val'));
                    $('#home_latitude').val(item.data('lat'));
                    $('#home_longitude').val(item.data('lng'));
                }
            });

            // Work Location
            $('#input_work_location').autoComplete({
                minChars: 2,
                source: function(term, response) {
                    $.getJSON('/api/places-autocomplete', { q: term }, function(data) {
                        response(data);
                    });
                },
                renderItem: function(item, search) {
                    return '<div class="autocomplete-suggestion" data-val="' + item.description + '" data-lat="' + item.lat + '" data-lng="' + item.lng + '">{{ __('messages.blade_static.item_description_9b2f16ea') }}</div>';
                },
                onSelect: function(e, term, item) {
                    $('#input_work_location').val(item.data('val'));
                    $('#work_latitude').val(item.data('lat'));
                    $('#work_longitude').val(item.data('lng'));
                }
            });
        });
    </script>
@endsection
