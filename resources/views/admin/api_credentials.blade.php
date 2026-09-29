@extends('admin.template')

@section('title', 'Edit API Credentials | FabKin Admin & Dashboards Template')
@section('title-sub', 'API Credentials')
@section('pagetitle', 'Edit API Credentials')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">

    <style>
        .credential-group {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--color-as-e9ecef);
        }
        .credential-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .credential-group h6 {
            font-size: 1.1rem;
            margin-bottom: 1.25rem;
            color: var(--color-as-495057);
        }
        .form-text-small {
            font-size: 0.85rem;
            color: var(--color-as-6c757d);
        }
    </style>
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/api_credentials', 'class' => 'row g-5', 'files' => true, 'id' => 'api_credentials_form']) !!}

                            <!-- Mandatory note -->
                            <div class="col-12">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Google Maps -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.google_maps_40cd7fa7') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_google_map_key">
                                        {{ __('messages.blade_static.google_map_key_41623242') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('google_map_key', old('google_map_key', api_credentials('key', 'GoogleMap')), [
                                        'class' => 'form-control',
                                        'id' => 'input_google_map_key',
                                        'placeholder' => 'Google Map KEY'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('google_map_key') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_google_map_server_key">
                                        {{ __('messages.blade_static.google_map_server_key_c7db9202') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('google_map_server_key', old('google_map_server_key', api_credentials('server_key', 'GoogleMap')), [
                                        'class' => 'form-control',
                                        'id' => 'input_google_map_server_key',
                                        'placeholder' => 'Google Map Server Key'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('google_map_server_key') }}</span>
                                </div>
                            </div>

                            <!-- Twilio -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.twilio_sms_1c17d3ba') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_twillo_sid">
                                        {{ __('messages.blade_static.twilio_sid_00377edf') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('twillo_sid', old('twillo_sid', api_credentials('sid', 'Twillo')), [
                                        'class' => 'form-control',
                                        'id' => 'input_twillo_sid',
                                        'placeholder' => 'Twilio SID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('twillo_sid') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_twillo_token">
                                        {{ __('messages.blade_static.twilio_token_5c69dc1d') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('twillo_token', old('twillo_token', api_credentials('token', 'Twillo')), [
                                        'class' => 'form-control',
                                        'id' => 'input_twillo_token',
                                        'placeholder' => 'Twilio Token'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('twillo_token') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_twillo_from">
                                        {{ __('messages.blade_static.twilio_from_number_2086732a') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('twillo_from', old('twillo_from', api_credentials('from', 'Twillo')), [
                                        'class' => 'form-control',
                                        'id' => 'input_twillo_from',
                                        'placeholder' => 'Twilio From Number'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('twillo_from') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_twillo_service_sid">
                                        {{ __('messages.blade_static.twilio_service_sid_424f1d0e') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('twillo_service_sid', old('twillo_service_sid', api_credentials('service_sid', 'Twillo')), [
                                        'class' => 'form-control',
                                        'id' => 'input_twillo_service_sid',
                                        'placeholder' => 'Twilio Service SID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('twillo_service_sid') }}</span>
                                </div>
                            </div>

                            <!-- Facebook -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.facebook_login_bba1c5c5') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_facebook_client_id">
                                        {{ __('messages.blade_static.facebook_client_id_d163b236') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('facebook_client_id', old('facebook_client_id', api_credentials('client_id', 'Facebook')), [
                                        'class' => 'form-control',
                                        'id' => 'input_facebook_client_id',
                                        'placeholder' => 'Facebook Client ID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('facebook_client_id') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_facebook_client_secret">
                                        {{ __('messages.blade_static.facebook_client_secret_3dcdb111') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('facebook_client_secret', old('facebook_client_secret', api_credentials('client_secret', 'Facebook')), [
                                        'class' => 'form-control',
                                        'id' => 'input_facebook_client_secret',
                                        'placeholder' => 'Facebook Client Secret'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('facebook_client_secret') }}</span>
                                </div>
                            </div>

                            <!-- Google Login -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.google_login_2f7074e8') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_google_client">
                                        {{ __('messages.blade_static.google_client_id_c9cb8901') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('google_client', old('google_client', api_credentials('client_id', 'Google')), [
                                        'class' => 'form-control',
                                        'id' => 'input_google_client',
                                        'placeholder' => 'Google Client ID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('google_client') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_google_client_secret">
                                        {{ __('messages.blade_static.google_client_secret_066027c6') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('google_client_secret', old('google_client_secret', api_credentials('client_secret', 'Google')), [
                                        'class' => 'form-control',
                                        'id' => 'input_google_client_secret',
                                        'placeholder' => 'Google Client Secret'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('google_client_secret') }}</span>
                                </div>
                            </div>

                            <!-- Sinch -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.sinch_88d3679a') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_sinch_key">
                                        {{ __('messages.blade_static.sinch_key_1156d5d3') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('sinch_key', old('sinch_key', api_credentials('sinch_key', 'Sinch')), [
                                        'class' => 'form-control',
                                        'id' => 'input_sinch_key',
                                        'placeholder' => 'Sinch Key'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('sinch_key') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_sinch_secret_key">
                                        {{ __('messages.blade_static.sinch_secret_key_88899583') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('sinch_secret_key', old('sinch_secret_key', api_credentials('sinch_secret_key', 'Sinch')), [
                                        'class' => 'form-control',
                                        'id' => 'input_sinch_secret_key',
                                        'placeholder' => 'Sinch Secret Key'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('sinch_secret_key') }}</span>
                                </div>
                            </div>

                            <!-- Apple Login -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.apple_sign_in_c628f10a') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_apple_service_id">
                                        {{ __('messages.blade_static.apple_service_id_750321ed') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('apple_service_id', old('apple_service_id', api_credentials('service_id', 'Apple')), [
                                        'class' => 'form-control',
                                        'id' => 'input_apple_service_id',
                                        'placeholder' => 'Apple Service ID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('apple_service_id') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_apple_team_id">
                                        {{ __('messages.blade_static.apple_team_id_20055b41') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('apple_team_id', old('apple_team_id', api_credentials('team_id', 'Apple')), [
                                        'class' => 'form-control',
                                        'id' => 'input_apple_team_id',
                                        'placeholder' => 'Apple Team ID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('apple_team_id') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_apple_key_id">
                                        {{ __('messages.blade_static.apple_key_id_70f2a808') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('apple_key_id', old('apple_key_id', api_credentials('key_id', 'Apple')), [
                                        'class' => 'form-control',
                                        'id' => 'input_apple_key_id',
                                        'placeholder' => 'Apple Key ID'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('apple_key_id') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_apple_key_file">
                                        {{ __('messages.blade_static.apple_key_file_p8_0bde1977') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::file('apple_key_file', [
                                        'class' => 'form-control',
                                        'id' => 'input_apple_key_file',
                                        'accept' => '.p8,text/plain'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('apple_key_file') }}</span>
                                    <small class="form-text text-muted">
                                        {{ __('messages.blade_static.upload_your_apple_private_key_file_p8_format_75cdd6ca') }}
                                    </small>
                                </div>
                            </div>

                            <!-- Firebase -->
                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.firebase_aa234fb6') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_database_url">
                                        {{ __('messages.blade_static.firebase_database_url_5a868e20') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::text('database_url', old('database_url', api_credentials('database_url', 'Firebase')), [
                                        'class' => 'form-control',
                                        'id' => 'input_database_url',
                                        'placeholder' => 'Firebase Database URL'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('database_url') }}</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_service_account">
                                        {{ __('messages.blade_static.firebase_service_account_file_json_9abf6ed2') }} <em class="text-danger">*</em>
                                    </label>
                                    {!! Form::file('service_account', [
                                        'class' => 'form-control',
                                        'id' => 'input_service_account',
                                        'accept' => 'application/json,.json'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('service_account') }}</span>
                                    <small class="form-text text-muted">
                                        {{ __('messages.blade_static.upload_your_firebase_service_account_json_key_5244978f') }}
                                    </small>
                                </div>
                            </div>

                            <!-- reCAPTCHA (conditional) -->
                            @if(CheckGetInTuchpopup())
                                <div class="col-12 credential-group">
                                    <h6>{{ __('messages.blade_static.recaptcha_82a8ff90') }}</h6>
                                    <div class="mb-4">
                                        <label class="form-label" for="input_recaptcha_site_key">
                                            {{ __('messages.blade_static.recaptcha_site_key_3a1290d5') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('recaptcha_site_key', old('recaptcha_site_key', api_credentials('site_key', 'Recaptcha')), [
                                            'class' => 'form-control',
                                            'id' => 'input_recaptcha_site_key',
                                            'placeholder' => 'reCAPTCHA Site Key'
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('recaptcha_site_key') }}</span>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label" for="input_recaptcha_secret_key">
                                            {{ __('messages.blade_static.recaptcha_secret_key_7973377f') }} <em class="text-danger">*</em>
                                        </label>
                                        {!! Form::text('recaptcha_secret_key', old('recaptcha_secret_key', api_credentials('secret_key', 'Recaptcha')), [
                                            'class' => 'form-control',
                                            'id' => 'input_recaptcha_secret_key',
                                            'placeholder' => 'reCAPTCHA Secret Key'
                                        ]) !!}
                                        <span class="text-danger">{{ $errors->first('recaptcha_secret_key') }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 credential-group">
                                <h6>{{ __('messages.blade_static.ai_matching_fraud_detection_0a028742') }}</h6>
                                <div class="mb-4">
                                    <label class="form-label" for="input_ai_provider">{{ __('messages.blade_static.ai_provider_188b430c') }}</label>
                                    <select class="form-select" name="ai_provider" id="input_ai_provider">
                                        <option value="openai" {{ old('ai_provider', api_credentials('provider', 'AI') ?: 'openai') === 'openai' ? 'selected' : '' }}>{{ __('messages.blade_static.chatgpt_openai_5cf924a9') }}</option>
                                        <option value="deepseek" {{ old('ai_provider', api_credentials('provider', 'AI')) === 'deepseek' ? 'selected' : '' }}>{{ __('messages.blade_static.deepseek_2b893991') }}</option>
                                        <option value="gemini" {{ old('ai_provider', api_credentials('provider', 'AI')) === 'gemini' ? 'selected' : '' }}>{{ __('messages.blade_static.gemini_ddba2c92') }}</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_ai_model">{{ __('messages.blade_static.ai_model_3d5ac060') }}</label>
                                    {!! Form::text('ai_model', old('ai_model', api_credentials('model', 'AI')), [
                                        'class' => 'form-control',
                                        'id' => 'input_ai_model',
                                        'placeholder' => 'gpt-4o-mini, deepseek-chat, gemini-1.5-flash'
                                    ]) !!}
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_openai_api_key">{{ __('messages.blade_static.openai_api_key_ed05045c') }}</label>
                                    {!! Form::text('openai_api_key', old('openai_api_key', api_credentials('api_key', 'OpenAI')), [
                                        'class' => 'form-control',
                                        'id' => 'input_openai_api_key',
                                        'placeholder' => 'OpenAI API Key'
                                    ]) !!}
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_deepseek_api_key">{{ __('messages.blade_static.deepseek_api_key_ce51a09c') }}</label>
                                    {!! Form::text('deepseek_api_key', old('deepseek_api_key', api_credentials('api_key', 'DeepSeek')), [
                                        'class' => 'form-control',
                                        'id' => 'input_deepseek_api_key',
                                        'placeholder' => 'DeepSeek API Key'
                                    ]) !!}
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="input_gemini_api_key">{{ __('messages.blade_static.gemini_api_key_44e797bf') }}</label>
                                    {!! Form::text('gemini_api_key', old('gemini_api_key', api_credentials('api_key', 'Gemini')), [
                                        'class' => 'form-control',
                                        'id' => 'input_gemini_api_key',
                                        'placeholder' => 'Gemini API Key'
                                    ]) !!}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right column: Info & Actions -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.blade_static.api_credentials_management_53f51368') }}</h5>
                        <p class="text-muted mb-4">
                            Update the API keys and credentials used across the application (maps, SMS, social logins, push notifications, etc.).
                            Be careful when changing these values — incorrect keys may break core features.
                        </p>

                        <div class="alert alert-warning">
                            <strong>{{ __('messages.blade_static.important_18dd7e89') }}</strong> {{ __('messages.blade_static.after_updating_credentials_clear_the_application_cache_a_732dcdf5') }}
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-5 text-end">
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                {{ __('messages.blade_static.reset_form_6c3b2103') }}
                            </button>
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

