@extends('template_footeronly')
@section('main')
<style>
    .company-wizard {
        min-height: 100vh;
        background: var(--color-as-f8fafc);
        color: var(--color-as-111827);
    }
    .wizard-hero {
        background: var(--color-as-rgba-15-118-110-0-92) url("{{ url('images/icon/become_company.png') }}") center/cover no-repeat;
        color: var(--color-white);
        padding: 32px 0 88px;
    }
    .wizard-container { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
    .wizard-nav { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 56px; }
    .wizard-nav img { width: 132px; max-height: 56px; object-fit: contain; filter: brightness(0) invert(1); }
    .wizard-nav a { color: var(--color-white); font-weight: 800; text-decoration: none; border: 1px solid var(--color-as-rgba-255-255-255-0-34); border-radius: 0; padding: 12px 16px; }
    .wizard-hero h1 { font-size: clamp(38px, 7vw, 72px); line-height: 1; letter-spacing: 0; max-width: 760px; margin: 0 0 18px; }
    .wizard-hero p { color: var(--color-as-rgba-255-255-255-0-84); line-height: 1.7; font-size: 18px; max-width: 700px; margin: 0; }
    .wizard-shell { margin-top: -58px; display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 22px; align-items: start; }
    .wizard-card { background: var(--color-white); border: 1px solid var(--color-as-e5e7eb); border-radius: 0; box-shadow: 0 20px 60px var(--color-as-rgba-15-23-42-0-10); }
    .wizard-side { padding: 24px; }
    .wizard-stepper { list-style: none; padding: 0; margin: 0; }
    .wizard-stepper li { display: flex; gap: 12px; padding: 14px 0; color: var(--color-as-64748b); font-weight: 700; }
    .wizard-stepper span { width: 30px; height: 30px; border-radius: 0; display: inline-flex; align-items: center; justify-content: center; background: var(--color-as-e2e8f0); color: var(--color-as-334155); flex: 0 0 auto; }
    .wizard-stepper li.is-active { color: var(--color-as-0f766e); }
    .wizard-stepper li.is-active span { background: var(--color-as-ccfbf1); color: var(--color-as-0f766e); }
    .wizard-form { padding: 28px; }
    .wizard-panel { display: none; }
    .wizard-panel.is-active { display: block; }
    .wizard-panel h2 { font-size: 28px; margin: 0 0 8px; letter-spacing: 0; }
    .wizard-panel p { color: var(--color-as-64748b); line-height: 1.6; margin: 0 0 24px; }
    .field-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .field { margin-bottom: 16px; }
    .field label { display: block; margin-bottom: 8px; font-weight: 800; color: var(--color-as-334155); }
    .field input, .field select { width: 100%; min-height: 48px; border: 1px solid var(--color-as-dbe3ef); border-radius: 0; padding: 0 14px; font-size: 15px; background: var(--color-white); }
    .phone-grid { display: grid; grid-template-columns: 180px minmax(0, 1fr); gap: 10px; }
    .text-danger { color: var(--color-error); font-size: 13px; display: block; margin-top: 6px; }
    .wizard-actions { display: flex; justify-content: space-between; gap: 12px; margin-top: 24px; border-top: 1px solid var(--color-as-e5e7eb); padding-top: 22px; }
    .wizard-btn { min-height: 48px; border-radius: 0; border: 1px solid var(--color-as-dbe3ef); padding: 0 18px; font-weight: 800; background: var(--color-white); cursor: pointer; }
    .wizard-btn-primary { background: var(--color-as-0f766e); color: var(--color-white); border-color: var(--color-as-0f766e); }
    .benefits { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; padding: 54px 0; }
    .benefit { background: var(--color-white); border: 1px solid var(--color-as-e5e7eb); border-radius: 0; padding: 22px; }
    .benefit img { width: 72px; height: 72px; object-fit: contain; margin-bottom: 16px; }
    .benefit h3 { margin: 0 0 8px; }
    .benefit p { margin: 0; color: var(--color-as-64748b); line-height: 1.6; }
    @media (max-width: 920px) {
        .wizard-shell, .field-grid, .benefits { grid-template-columns: 1fr; }
        .phone-grid { grid-template-columns: 1fr; }
        .wizard-side { display: none; }
    }
</style>

<div class="flash-container">
    @if(Session::has('message'))
        <div class="alert text-center participant-alert" style="background:var(--color-as-1fbad6);color:var(--color-white);margin-bottom:0;" role="alert">
            {!! Session::get('message') !!}
        </div>
    @endif
</div>

<div class="company-wizard text--left" ng-controller="facebook_account_kit">
    @include('user.otp_popup')

    <section class="wizard-hero">
        <div class="wizard-container">
            <div class="wizard-nav">
                <a href="{{ url('/') }}"><img src="{{ $logo_url }}" alt="{{ $site_name }}"></a>
                <a href="{{ url('signin_company') }}">{{ trans('messages.ride.already_have_account') }}</a>
            </div>
            <h1>{{ __('messages.blade_static.register_your_transport_company_3f852246') }}</h1>
            <p>{{ __('messages.blade_static.create_your_company_workspace_then_add_profile_details_d_48a85552') }}</p>
        </div>
    </section>

    <div class="wizard-container wizard-shell">
        <aside class="wizard-card wizard-side">
            <ol class="wizard-stepper">
                <li class="is-active" data-step-label="1"><span>1</span> {{ __('messages.blade_static.company_details_c9be34ce') }}</li>
                <li data-step-label="2"><span>2</span> {{ __('messages.blade_static.contact_access_a63bbfdc') }}</li>
                <li data-step-label="3"><span>3</span> {{ __('messages.blade_static.review_e29a79fe') }}</li>
            </ol>
        </aside>

        <section class="wizard-card wizard-form">
            {{ Form::open(['url' => 'company_register', 'id' => 'form']) }}
            {{ csrf_field() }}
            {!! Form::hidden('request_type', '', ['id' => 'request_type']) !!}
            {!! Form::hidden('otp', '', ['id' => 'otp']) !!}
            {!! Form::hidden('user_type', 'Company') !!}
            {!! Form::hidden('step', 'basics') !!}
            {!! Form::hidden('code', '', ['id' => 'code']) !!}

            <div class="wizard-panel is-active" data-step="1">
                <h2>{{ __('messages.blade_static.company_details_c9be34ce') }}</h2>
                <p>{{ __('messages.blade_static.start_with_the_public_information_for_your_company_accou_3ab0c275') }}</p>
                <div class="field-grid">
                    <div class="field">
                        <label for="company-name">{{ trans('messages.profile.name') }}</label>
                        {!! Form::text('name', old('name'), ['id' => 'company-name', 'placeholder' => 'Company name']) !!}
                        <span class="text-danger name_error">{{ $errors->first('name') }}</span>
                    </div>
                    <div class="field">
                        <label for="company-email">{{ trans('messages.user.email') }}</label>
                        {!! Form::email('email', old('email'), ['id' => 'company-email', 'placeholder' => 'company@example.com']) !!}
                        <span class="text-danger email_error">{{ $errors->first('email') }}</span>
                    </div>
                </div>
                <div class="field">
                    <label for="home_address">{{ __('messages.blade_static.operating_city_eeaecfb8') }}</label>
                    {!! Form::text('home_address', old('home_address'), ['id' => 'home_address', 'placeholder' => trans('messages.profile.profile_city'), 'autocomplete' => 'off']) !!}
                    <input type="hidden" name="city" id="city" value="{{ old('city') }}">
                    <input type="hidden" name="state" id="state" value="{{ old('state') }}">
                    <input type="hidden" name="country" id="country" value="{{ old('country') }}">
                    <input type="hidden" name="address_line1" id="address_line1" value="{{ old('address_line1') }}">
                    <input type="hidden" name="address_line2" id="address_line2" value="{{ old('address_line2') }}">
                    <input type="hidden" name="postal_code" id="postal_code" value="{{ old('postal_code') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    <span class="text-danger home_address_error">{{ $errors->first('home_address') }}</span>
                </div>
            </div>

            <div class="wizard-panel" data-step="2">
                <h2>{{ __('messages.blade_static.contact_and_password_2ef7c1a6') }}</h2>
                <p>{{ __('messages.blade_static.this_mobile_number_is_used_for_verification_and_company_bd498268') }}</p>
                <div class="field">
                    <label>{{ trans('messages.user.mobile') }}</label>
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
                    <span class="text-danger country_code_error">{{ $errors->first('country_code') }}</span>
                    <span class="text-danger mobile_number_error">{{ $errors->first('mobile_number') }}</span>
                </div>
                <div class="field">
                    <label for="password">{{ trans('messages.user.paswrd') }}</label>
                    {!! Form::password('password', ['id' => 'password', 'placeholder' => trans('messages.user.paswrd')]) !!}
                    <span class="text-danger password_error">{{ $errors->first('password') }}</span>
                </div>
            </div>

            <div class="wizard-panel" data-step="3">
                <h2>{{ __('messages.blade_static.submit_for_review_f7710dc5') }}</h2>
                <p>{{ __('messages.blade_static.your_company_account_will_be_created_immediately_complet_9f7736d0') }}</p>
                <div class="benefit" style="margin-bottom: 0;">
                    <h3>{{ __('messages.blade_static.what_happens_next_4ef9217b') }}</h3>
                    <p>{{ __('messages.blade_static.you_will_be_redirected_to_the_company_profile_page_to_ad_0b30fa1b') }}</p>
                </div>
            </div>

            @php
                $submit_method = site_settings('otp_verification') ? 'send_otp' : 'check_otp';
            @endphp
            <div class="wizard-actions">
                <button class="wizard-btn" type="button" data-wizard-back>{{ __('messages.blade_static.back_b52b36b7') }}</button>
                <button class="wizard-btn wizard-btn-primary" type="button" data-wizard-next>{{ __('messages.blade_static.next_bc981983') }}</button>
                <button class="wizard-btn wizard-btn-primary" id="submit-btn" name="step" value="basics" ng-click="showPopup('{{ $submit_method }}');" type="button" data-wizard-submit style="display:none;">{{ trans('messages.user.submit') }}</button>
            </div>
            {{ Form::close() }}
        </section>
    </div>

    <section class="wizard-container benefits">
        <article class="benefit">
            <img src="{{ url('images/new/company.svg') }}" alt="{{ __('messages.blade_static.company_dashboard_4c6a5088') }}">
            <h3>{{ __('messages.blade_static.company_dashboard_4c6a5088') }}</h3>
            <p>{{ __('messages.blade_static.manage_fleet_operations_bookings_and_driver_activity_fro_e606df78') }}</p>
        </article>
        <article class="benefit">
            <img src="{{ url('images/new/driver.svg') }}" alt="{{ __('messages.blade_static.drivers_a40ad45b') }}">
            <h3>{{ __('messages.blade_static.driver_network_f50c65e0') }}</h3>
            <p>{{ __('messages.blade_static.add_drivers_and_vehicles_as_your_company_grows_across_ro_64ec9676') }}</p>
        </article>
        <article class="benefit">
            <img src="{{ url('images/new/money.svg') }}" alt="{{ __('messages.blade_static.payouts_e0e7ea13') }}">
            <h3>{{ __('messages.blade_static.payout_control_5c001752') }}</h3>
            <p>{{ __('messages.blade_static.track_earnings_pending_payouts_and_company_settlement_de_d0006cd7') }}</p>
        </article>
    </section>
</div>

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
</main>
@stop
