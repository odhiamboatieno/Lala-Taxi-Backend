@extends('marketing.auth-layout')
@section('title', __('messages.blade_static.register_your_transport_company_3f852246') . ' · ' . $site_name)

@section('main')
@if(Session::has('message'))
    <div class="site-container flash-container" style="padding-top:24px;">
        <div class="site-alert" role="alert">{!! Session::get('message') !!}</div>
    </div>
@endif

<div class="company-wizard" ng-controller="facebook_account_kit">
    @include('user.otp_popup')

    <section class="center-hero">
        <p class="site-eyebrow">{{ trans('messages.header.signin') }}</p>
        <h1>{{ __('messages.blade_static.register_your_transport_company_3f852246') }}</h1>
        <p class="site-lead">{{ __('messages.blade_static.create_your_company_workspace_then_add_profile_details_d_48a85552') }}</p>
        <div class="site-actions" style="justify-content:center;">
            <a class="site-btn site-btn--outline" href="{{ url('signin_company') }}">{{ trans('messages.ride.already_have_account') }}</a>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container wizard-shell">
            <aside class="wizard-side">
                <ol class="wizard-stepper">
                    <li class="is-active" data-step-label="1"><span>1</span> {{ __('messages.blade_static.company_details_c9be34ce') }}</li>
                    <li data-step-label="2"><span>2</span> {{ __('messages.blade_static.contact_access_a63bbfdc') }}</li>
                    <li data-step-label="3"><span>3</span> {{ __('messages.blade_static.review_e29a79fe') }}</li>
                </ol>
            </aside>

            <section class="wizard-form">
                {{ Form::open(['url' => 'company_register', 'id' => 'form']) }}
                {{ csrf_field() }}
                {!! Form::hidden('request_type', '', ['id' => 'request_type']) !!}
                {!! Form::hidden('otp', '', ['id' => 'otp']) !!}
                {!! Form::hidden('user_type', 'Company') !!}
                {!! Form::hidden('step', 'basics') !!}
                {!! Form::hidden('code', '', ['id' => 'code']) !!}

                <div class="wizard-panel is-active" data-step="1">
                    <h2>{{ __('messages.blade_static.company_details_c9be34ce') }}</h2>
                    <p class="site-lead" style="max-width:none;">{{ __('messages.blade_static.start_with_the_public_information_for_your_company_accou_3ab0c275') }}</p>
                    <div class="field-grid">
                        <div class="site-field">
                            <label for="company-name">{{ trans('messages.profile.name') }}</label>
                            {!! Form::text('name', old('name'), ['id' => 'company-name', 'placeholder' => 'Company name']) !!}
                            <span class="field-error text-danger name_error">{{ $errors->first('name') }}</span>
                        </div>
                        <div class="site-field">
                            <label for="company-email">{{ trans('messages.user.email') }}</label>
                            {!! Form::email('email', old('email'), ['id' => 'company-email', 'placeholder' => 'company@example.com']) !!}
                            <span class="field-error text-danger email_error">{{ $errors->first('email') }}</span>
                        </div>
                    </div>
                    <div class="site-field">
                        <label for="home_address">{{ __('messages.blade_static.operating_city_eeaecfb8') }}</label>
                        <div class="autocomplete-shell">
                            {!! Form::text('home_address', old('home_address'), ['id' => 'home_address', 'placeholder' => trans('messages.profile.profile_city'), 'autocomplete' => 'off']) !!}
                            <ul class="autocomplete-results home_address"></ul>
                        </div>
                        <input type="hidden" name="city" id="city" value="{{ old('city') }}">
                        <input type="hidden" name="state" id="state" value="{{ old('state') }}">
                        <input type="hidden" name="country" id="country" value="{{ old('country') }}">
                        <input type="hidden" name="address_line1" id="address_line1" value="{{ old('address_line1') }}">
                        <input type="hidden" name="address_line2" id="address_line2" value="{{ old('address_line2') }}">
                        <input type="hidden" name="postal_code" id="postal_code" value="{{ old('postal_code') }}">
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                        <span class="field-error text-danger home_address_error">{{ $errors->first('home_address') }}</span>
                    </div>
                </div>

                <div class="wizard-panel" data-step="2">
                    <h2>{{ __('messages.blade_static.contact_and_password_2ef7c1a6') }}</h2>
                    <p class="site-lead" style="max-width:none;">{{ __('messages.blade_static.this_mobile_number_is_used_for_verification_and_company_bd498268') }}</p>
                    <div class="site-field">
                        <label for="mobile_country">{{ trans('messages.user.mobile') }}</label>
                        <div class="phone-grid">
                            <select name="country_code" id="mobile_country">
                                @foreach($country as $value)
                                    <option value="{{ $value->phone_code }}" data-id="{{ $value->id }}" data-value="+{{ $value->phone_code }}" {{ ($value->id == (old('country_id') ?: 1)) ? 'selected' : '' }}>
                                        +{{ $value->phone_code }} {{ $value->long_name }}
                                    </option>
                                @endforeach
                            </select>
                            {!! Form::tel('mobile_number', old('mobile_number', $phone_number ?? ''), ['id' => 'mobile', 'placeholder' => trans('messages.profile.mobile')]) !!}
                        </div>
                        {!! Form::hidden('country_id', old('country_id'), ['id' => 'country_id']) !!}
                        <span class="field-error text-danger country_code_error">{{ $errors->first('country_code') }}</span>
                        <span class="field-error text-danger mobile_number_error">{{ $errors->first('mobile_number') }}</span>
                    </div>
                    <div class="site-field">
                        <label for="password">{{ trans('messages.user.paswrd') }}</label>
                        {!! Form::password('password', ['id' => 'password', 'placeholder' => trans('messages.user.paswrd')]) !!}
                        <span class="field-error text-danger password_error">{{ $errors->first('password') }}</span>
                    </div>
                </div>

                <div class="wizard-panel" data-step="3">
                    <h2>{{ __('messages.blade_static.submit_for_review_f7710dc5') }}</h2>
                    <p class="site-lead" style="max-width:none;">{{ __('messages.blade_static.your_company_account_will_be_created_immediately_complet_9f7736d0') }}</p>
                    <div class="wizard-note">
                        <h3>{{ __('messages.blade_static.what_happens_next_4ef9217b') }}</h3>
                        <p>{{ __('messages.blade_static.you_will_be_redirected_to_the_company_profile_page_to_ad_0b30fa1b') }}</p>
                    </div>
                </div>

                @php
                    $submit_method = site_settings('otp_verification') ? 'send_otp' : 'check_otp';
                @endphp
                <div class="wizard-actions">
                    <button class="site-btn site-btn--outline" type="button" data-wizard-back>{{ __('messages.blade_static.back_b52b36b7') }}</button>
                    <button class="site-btn site-btn--gold" type="button" data-wizard-next>{{ __('messages.blade_static.next_bc981983') }}</button>
                    <button class="site-btn site-btn--gold" id="submit-btn" name="step" value="basics" ng-click="showPopup('{{ $submit_method }}');" type="button" data-wizard-submit style="display:none;">{{ trans('messages.user.submit') }}</button>
                </div>
                {{ Form::close() }}
            </section>
        </div>
    </section>

    <section class="service-band">
        <div class="site-container">
            <div class="service-grid">
                <article class="service-item">
                    <i data-lucide="building-2" aria-hidden="true"></i>
                    <h3>{{ __('messages.blade_static.company_dashboard_4c6a5088') }}</h3>
                    <p>{{ __('messages.blade_static.manage_fleet_operations_bookings_and_driver_activity_fro_e606df78') }}</p>
                </article>
                <article class="service-item">
                    <i data-lucide="car-front" aria-hidden="true"></i>
                    <h3>{{ __('messages.blade_static.driver_network_f50c65e0') }}</h3>
                    <p>{{ __('messages.blade_static.add_drivers_and_vehicles_as_your_company_grows_across_ro_64ec9676') }}</p>
                </article>
                <article class="service-item">
                    <i data-lucide="wallet" aria-hidden="true"></i>
                    <h3>{{ __('messages.blade_static.payout_control_5c001752') }}</h3>
                    <p>{{ __('messages.blade_static.track_earnings_pending_payouts_and_company_settlement_de_d0006cd7') }}</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .wizard-shell { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 32px; align-items: start; }
    .wizard-stepper { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
    .wizard-stepper li { display: flex; gap: 14px; align-items: center; padding: 14px 16px; border: 1px solid var(--site-line); border-radius: var(--site-radius-field); color: var(--site-muted); font-weight: 650; }
    .wizard-stepper span { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: var(--site-surface); color: var(--site-ink); flex: 0 0 auto; }
    .wizard-stepper li.is-active { border-color: var(--site-ink); color: var(--site-ink); }
    .wizard-stepper li.is-active span { background: var(--site-gold); color: var(--site-ink); }
    .wizard-form { border: 1px solid var(--site-line); border-radius: var(--site-radius-image); padding: 36px; background: var(--site-white); }
    .wizard-panel { display: none; }
    .wizard-panel.is-active { display: block; }
    .wizard-panel h2 { font-size: 32px; margin-bottom: 12px; }
    .field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .phone-grid { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: 10px; }
    .wizard-actions { display: flex; justify-content: space-between; gap: 12px; margin-top: 28px; border-top: 1px solid var(--site-line); padding-top: 22px; }
    .wizard-note { padding: 24px; background: var(--site-surface); border-radius: var(--site-radius-field); }
    .wizard-note p { margin-top: 10px; }
    .site-field select { width: 100%; min-height: 54px; padding: 12px 16px; color: var(--site-ink); background: var(--site-white); border: 1px solid var(--site-line); border-radius: var(--site-radius-field); }
    @media (max-width: 900px) {
        .wizard-shell, .field-grid { grid-template-columns: 1fr; }
        .phone-grid { grid-template-columns: 1fr; }
        .wizard-form { padding: 24px; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const panels = Array.from(document.querySelectorAll('[data-step]'));
        const labels = Array.from(document.querySelectorAll('[data-step-label]'));
        const back = document.querySelector('[data-wizard-back]');
        const next = document.querySelector('[data-wizard-next]');
        const submit = document.querySelector('[data-wizard-submit]');
        const countrySelect = document.querySelector('#mobile_country');
        const countryId = document.querySelector('#country_id');
        let step = 1;

        const syncCountry = () => {
            if (!countrySelect || !countryId) {
                return;
            }
            countryId.value = countrySelect.options[countrySelect.selectedIndex].dataset.id || '';
        };

        const render = () => {
            panels.forEach(panel => panel.classList.toggle('is-active', Number(panel.dataset.step) === step));
            labels.forEach(label => label.classList.toggle('is-active', Number(label.dataset.stepLabel) === step));
            back.style.visibility = step === 1 ? 'hidden' : 'visible';
            next.style.display = step === panels.length ? 'none' : 'inline-flex';
            submit.style.display = step === panels.length ? 'inline-flex' : 'none';
        };

        next.addEventListener('click', () => {
            step = Math.min(step + 1, panels.length);
            render();
        });
        back.addEventListener('click', () => {
            step = Math.max(step - 1, 1);
            render();
        });
        countrySelect && countrySelect.addEventListener('change', syncCountry);
        syncCountry();
        render();
    });
</script>
@endpush
