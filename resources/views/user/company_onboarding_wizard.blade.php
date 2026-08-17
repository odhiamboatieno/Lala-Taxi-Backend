@extends('template_footeronly')

@section('main')
@php
    $company = $company ?? null;
    $draft = $draft ?? [];
    $stepIndex = collect($wizardSteps)->pluck('slug')->search($currentStep);
    $stepIndex = $stepIndex === false ? 0 : $stepIndex;
    $status = $status ?? 'draft';
    $countryValue = old('country_id', $company->country_id ?? ($draft['country_id'] ?? $defaultCountryId));
    $uploadedDocumentsById = collect($uploadedDocuments ?? [])->keyBy('id');
@endphp
<style>
    .onboarding-shell{min-height:100vh;background:var(--color-as-f5f7fb);color:var(--color-as-0f172a)}
    .onboarding-wrap{width:min(1180px,calc(100% - 32px));margin:0 auto}
    .onboarding-hero{padding:32px 0 120px;background:var(--color-as-081321);color:var(--color-white)}
    .onboarding-top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:42px}
    .onboarding-top a{color:var(--color-white);text-decoration:none;font-weight:800}
    .onboarding-pill{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius: 0;background:var(--color-as-rgba-255-255-255-0-14);border:1px solid var(--color-as-rgba-255-255-255-0-18);font-size:13px;font-weight:700}
    .onboarding-hero h1{font-size:clamp(2.5rem,6vw,4.8rem);line-height:1.02;margin:0 0 16px;max-width:760px}
    .onboarding-hero p{max-width:720px;color:var(--color-as-rgba-255-255-255-0-84);font-size:18px;line-height:1.7;margin:0}
    .onboarding-grid{display:grid;grid-template-columns:320px minmax(0,1fr);gap:24px;margin-top:-80px;align-items:start}
    .onboarding-card{background:var(--color-white);border:1px solid var(--color-as-rgba-148-163-184-0-16);border-radius: 0;box-shadow:0 24px 70px var(--color-as-rgba-15-23-42-0-10)}
    .onboarding-sidebar{padding:26px;position:sticky;top:24px}
    .onboarding-progress{height:10px;border-radius: 0;background:var(--color-as-e2e8f0);overflow:hidden;margin:18px 0 24px}
    .onboarding-progress span{display:block;height:100%;background:var(--color-as-0f766e)}
    .onboarding-step{display:flex;gap:14px;padding:13px 0;border-bottom:1px solid var(--color-as-rgba-226-232-240-0-75)}
    .onboarding-step:last-child{border-bottom:0}
    .onboarding-step__dot{width:34px;height:34px;border-radius: 0;display:flex;align-items:center;justify-content:center;background:var(--color-as-e2e8f0);color:var(--color-as-475569);font-weight:800;flex:0 0 auto}
    .onboarding-step.is-active .onboarding-step__dot,.onboarding-step.is-complete .onboarding-step__dot{background:var(--color-as-ccfbf1);color:var(--color-as-0f766e)}
    .onboarding-step small{display:block;color:var(--color-as-94a3b8);font-weight:700;margin-bottom:3px}
    .onboarding-step strong{display:block;font-size:15px}
    .onboarding-main{padding:30px}
    .onboarding-main h2{margin:0 0 10px;font-size:34px;letter-spacing:-.03em}
    .onboarding-copy{color:var(--color-as-64748b);line-height:1.75;margin:0 0 24px}
    .field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .field{margin-bottom:18px}
    .field label{display:block;margin-bottom:8px;font-weight:800;color:var(--color-as-334155)}
    .field input,.field select,.field textarea{width:100%;border:1px solid var(--color-as-d9e2ec);border-radius: 0;min-height:52px;padding:12px 16px;font-size:15px;background:var(--color-white)}
    .field textarea{min-height:120px;resize:vertical}
    .field-hint{font-size:13px;color:var(--color-as-94a3b8);margin-top:6px}
    .text-danger{display:block;font-size:13px;margin-top:6px}
    .badge-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px}
    .status-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius: 0;font-size:13px;font-weight:800}
    .status-badge--ok{background:var(--color-as-dcfce7);color:var(--color-as-166534)}
    .status-badge--warn{background:var(--color-as-fef3c7);color:var(--color-as-92400e)}
    .status-badge--danger{background:var(--color-as-fee2e2);color:var(--color-as-b91c1c)}
    .upload-card,.review-card,.otp-card,.doc-card{border:1px solid var(--color-as-rgba-148-163-184-0-18);border-radius: 0;padding:20px;background:var(--color-as-f8fafc)}
    .upload-card img{width:120px;height:120px;border-radius: 0;object-fit:cover;border:1px solid var(--color-as-rgba-148-163-184-0-18);display:block;margin-bottom:16px}
    .otp-actions,.wizard-actions{display:flex;gap:12px;flex-wrap:wrap}
    .wizard-actions{justify-content:space-between;align-items:center;margin-top:28px;padding-top:22px;border-top:1px solid var(--color-as-e2e8f0)}
    .wizard-btn{display:inline-flex;align-items:center;justify-content:center;border-radius: 0;border:1px solid var(--color-as-cbd5e1);padding:13px 18px;font-weight:800;background:var(--color-white);color:var(--color-as-0f172a);text-decoration:none}
    .wizard-btn--primary{background:var(--color-as-0f766e);border:0;color:var(--color-white)}
    .wizard-btn--soft{background:var(--color-as-eff6ff);border-color:var(--color-as-bfdbfe);color:var(--color-as-1d4ed8)}
    .otp-card h3,.review-card h3,.doc-card h3,.upload-card h3{margin:0 0 10px}
    .review-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .review-item{padding:16px;border-radius: 0;background:var(--color-white);border:1px solid var(--color-as-rgba-148-163-184-0-14)}
    .review-item span{display:block;color:var(--color-as-64748b);font-size:12px;text-transform:uppercase;font-weight:800;letter-spacing:.08em;margin-bottom:6px}
    .doc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .doc-card__meta{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 16px}
    .doc-chip{display:inline-flex;align-items:center;padding:7px 11px;border-radius: 0;background:var(--color-as-e2e8f0);color:var(--color-as-334155);font-size:12px;font-weight:800}
    .timeline{display:grid;gap:10px;margin-top:18px}
    .timeline div{padding:12px 14px;border-radius: 0;background:var(--color-as-f8fafc);border:1px solid var(--color-as-rgba-148-163-184-0-14);font-weight:700;color:var(--color-as-475569)}
    .autocomplete-shell{position:relative}
    .autocomplete-results{display:none;position:absolute;z-index:30;top:calc(100% + 8px);left:0;right:0;list-style:none;margin:0;padding:8px;background:var(--color-white);border:1px solid var(--color-as-d9e2ec);border-radius: 0;box-shadow:0 18px 45px var(--color-as-rgba-15-23-42-0-12);max-height:260px;overflow:auto}
    .autocomplete-item{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius: 0;cursor:pointer}
    .autocomplete-item:hover{background:var(--color-as-f8fafc)}
    .map-modal{display:none;position:fixed;inset:0;z-index:2000;background:var(--color-as-rgba-2-6-23-0-62);padding:20px;align-items:center;justify-content:center}
    .map-modal.is-open{display:flex}
    .map-modal__card{width:min(960px,100%);background:var(--color-white);border-radius: 0;overflow:hidden;box-shadow:0 28px 70px var(--color-as-rgba-15-23-42-0-26)}
    .map-modal__head{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid var(--color-as-e2e8f0)}
    .map-modal__body{padding:22px}
    .map-canvas{width:100%;height:420px;border-radius: 0;border:1px solid var(--color-as-d9e2ec)}
    .map-modal__actions{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:18px}
    @media (max-width:960px){.onboarding-grid,.field-grid,.review-grid,.doc-grid{grid-template-columns:1fr}.onboarding-sidebar{position:static}.onboarding-main{padding:22px}}
</style>

<div class="onboarding-shell">
    <section class="onboarding-hero">
        <div class="onboarding-wrap">
            <div class="onboarding-top">
                <a href="{{ url('/') }}">
                    <img src="{{ $logo_url }}" alt="{{ $site_name }}" style="height:48px;max-width:180px;object-fit:contain;filter:brightness(0) invert(1);">
                </a>
                <div class="d-flex align-items-center" style="gap:12px;flex-wrap:wrap;">
                    <span class="onboarding-pill">{{ $company ? ($company->registration_reference ?: 'Draft account') : 'New operator onboarding' }}</span>
                    <a class="onboarding-pill" href="{{ url('signin_company') }}">Company Sign In</a>
                </div>
            </div>
            <h1>Register your company with a polished self-onboarding flow.</h1>
            <p>Set up your operator profile, verify contacts, upload your logo and compliance documents, then submit everything for approval without breaking the existing company workspace.</p>
        </div>
    </section>

    <div class="onboarding-wrap onboarding-grid">
        <aside class="onboarding-card onboarding-sidebar">
            <div class="d-flex justify-content-between align-items-center">
                <strong>Onboarding Progress</strong>
                <span>{{ $stepIndex + 1 }}/{{ count($wizardSteps) }}</span>
            </div>
            <div class="onboarding-progress">
                <span style="width: {{ max(8, (($stepIndex + 1) / max(1, count($wizardSteps))) * 100) }}%;"></span>
            </div>
            @foreach($wizardSteps as $index => $step)
                @php
                    $stepState = $index < $stepIndex ? 'is-complete' : ($step['slug'] === $currentStep ? 'is-active' : '');
                @endphp
                <div class="onboarding-step {{ $stepState }}">
                    <div class="onboarding-step__dot">{{ $index + 1 }}</div>
                    <div>
                        <small>Step {{ $index + 1 }}</small>
                        <strong>{{ $step['title'] }}</strong>
                    </div>
                </div>
            @endforeach

            @if($company)
                <div class="timeline">
                    <div>Phone: {{ $company->phone_verified_at ? 'Verified' : 'Pending' }}</div>
                    <div>Email: {{ $company->email_verified_at ? 'Verified' : 'Pending' }}</div>
                    <div>Logo: {{ $company->profile ? 'Uploaded' : 'Pending' }}</div>
                    <div>Status: {{ ucfirst(str_replace('_', ' ', $status)) }}</div>
                </div>
            @endif
        </aside>

        <section class="onboarding-card onboarding-main">
            @if($errors->any())
                <div class="review-card" style="margin-bottom:20px;border-color:var(--color-as-fecaca);background:var(--color-as-fff7f7);">
                    <h3 style="color:var(--color-as-b91c1c);">Please fix the highlighted issues</h3>
                    <ul style="margin:0;padding-left:18px;color:var(--color-as-7f1d1d);">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($currentStep === 'company-details')
                <h2>Company details</h2>
                <p class="onboarding-copy">Tell us about the operator account you are creating. These details shape the review experience and the company profile that appears inside LalaTaxi.</p>
                {!! Form::open(['url' => 'company/onboarding/step/company-details']) !!}
                    <div class="field-grid">
                        <div class="field">
                            <label>Company legal name</label>
                            {!! Form::text('legal_name', old('legal_name', $company->legal_name ?? ($draft['legal_name'] ?? ''))) !!}
                            <span class="text-danger">{{ $errors->first('legal_name') }}</span>
                        </div>
                        <div class="field">
                            <label>Trading name</label>
                            {!! Form::text('trading_name', old('trading_name', $company->trading_name ?? ($draft['trading_name'] ?? ''))) !!}
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Company registration number</label>
                            {!! Form::text('registration_number', old('registration_number', $company->registration_number ?? ($draft['registration_number'] ?? ''))) !!}
                            <span class="text-danger">{{ $errors->first('registration_number') }}</span>
                        </div>
                        <div class="field">
                            <label>Tax / VAT number</label>
                            {!! Form::text('vat_number', old('vat_number', $company->vat_number ?? ($draft['vat_number'] ?? ''))) !!}
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Company type</label>
                            {!! Form::select('company_type', ['private_hire'=>'Private hire','taxi_operator'=>'Taxi operator','chauffeur'=>'Chauffeur','airport_transfer'=>'Airport transfer','fleet_operator'=>'Fleet operator','other'=>'Other'], old('company_type', $company->business_type ?? ($draft['company_type'] ?? '')), ['placeholder' => 'Select company type']) !!}
                            <span class="text-danger">{{ $errors->first('company_type') }}</span>
                        </div>
                        <div class="field">
                            <label>Operating city / region</label>
                            {!! Form::text('operating_city', old('operating_city', $company->city ?? ($draft['operating_city'] ?? ''))) !!}
                            <span class="text-danger">{{ $errors->first('operating_city') }}</span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Number of vehicles</label>
                            {!! Form::number('vehicles_count', old('vehicles_count', $company->vehicles_count ?? ($draft['vehicles_count'] ?? 0)), ['min' => 0]) !!}
                            <span class="text-danger">{{ $errors->first('vehicles_count') }}</span>
                        </div>
                        <div class="field">
                            <label>Number of drivers</label>
                            {!! Form::number('drivers_count', old('drivers_count', $company->drivers_count ?? ($draft['drivers_count'] ?? 0)), ['min' => 0]) !!}
                            <span class="text-danger">{{ $errors->first('drivers_count') }}</span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Main service area</label>
                            {!! Form::text('service_area', old('service_area', $company->service_area ?? ($draft['service_area'] ?? ''))) !!}
                        </div>
                        <div class="field">
                            <label>Website URL</label>
                            {!! Form::text('website_url', old('website_url', $company->website_url ?? ($draft['website_url'] ?? '')), ['placeholder' => 'https://example.com']) !!}
                        </div>
                    </div>
                    <div class="field">
                        <label>Short company description</label>
                        {!! Form::textarea('short_description', old('short_description', $company->short_description ?? ($draft['short_description'] ?? ''))) !!}
                    </div>
                    <div class="wizard-actions">
                        <span class="field-hint">We save your progress as you move through the wizard.</span>
                        <button class="wizard-btn wizard-btn--primary" type="submit">Save and continue</button>
                    </div>
                {!! Form::close() !!}
            @elseif($currentStep === 'contact-person')
                <h2>Contact person</h2>
                <p class="onboarding-copy">Add the primary operator contact and the credentials that will be used to access the company workspace and continue the onboarding journey later.</p>
                {!! Form::open(['url' => 'company/onboarding/step/contact-person']) !!}
                    <div class="field-grid">
                        <div class="field">
                            <label>First name</label>
                            {!! Form::text('contact_first_name', old('contact_first_name', $company->contact_first_name ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('contact_first_name') }}</span>
                        </div>
                        <div class="field">
                            <label>Last name</label>
                            {!! Form::text('contact_last_name', old('contact_last_name', $company->contact_last_name ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('contact_last_name') }}</span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Position / title</label>
                            {!! Form::text('contact_position', old('contact_position', $company->contact_position ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('contact_position') }}</span>
                        </div>
                        <div class="field">
                            <label>Email address</label>
                            {!! Form::email('email', old('email', $company->email ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Phone country code</label>
                            {!! Form::text('phone_country_code', old('phone_country_code', $company->country_code ?? optional($countries->firstWhere('id', $countryValue))->phone_code)) !!}
                            <span class="text-danger">{{ $errors->first('phone_country_code') }}</span>
                        </div>
                        <div class="field">
                            <label>Phone number</label>
                            {!! Form::text('phone_number', old('phone_number', $company->mobile_number ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('phone_number') }}</span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Alternative phone</label>
                            {!! Form::text('alternative_phone', old('alternative_phone', $company->alternative_phone ?? '')) !!}
                        </div>
                        <div class="field"></div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Password</label>
                            {!! Form::password('password') !!}
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        </div>
                        <div class="field">
                            <label>Confirm password</label>
                            {!! Form::password('password_confirmation') !!}
                        </div>
                    </div>
                    <div class="wizard-actions">
                        <a class="wizard-btn" href="{{ url(($company ? 'company/onboarding' : 'company/register').'?step=company-details') }}">Back</a>
                        <button class="wizard-btn wizard-btn--primary" type="submit">{{ $company ? 'Save and continue' : 'Create draft account' }}</button>
                    </div>
                {!! Form::close() !!}
            @elseif($currentStep === 'address')
                <h2>Business address</h2>
                <p class="onboarding-copy">Set the company address that should appear on the operator profile. The country defaults from site settings when available, so companies are not forced into unnecessary manual re-selection.</p>
                {!! Form::open(['url' => 'company/onboarding/step/address']) !!}
                    <div class="field">
                        <label for="company_address_search">Search company address</label>
                        <div class="autocomplete-shell">
                            {!! Form::text('company_address_search', old('company_address_search', trim(($company->address ?? '').' '.($company->city ?? ''))), ['id' => 'company_address_search', 'placeholder' => 'Start typing your company address', 'autocomplete' => 'off']) !!}
                            <ul class="autocomplete-results company-address-results"></ul>
                        </div>
                        <div class="wizard-actions" style="margin-top:12px;padding-top:0;border-top:0;justify-content:flex-start;">
                            <button class="wizard-btn wizard-btn--soft" type="button" id="open-map-picker">Pin on map</button>
                        </div>
                        <div class="field-hint">Type the office address to autocomplete it, or open the map and drop a pin.</div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Country</label>
                            <select name="country_id">
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ (string) $countryValue === (string) $country->id ? 'selected' : '' }}>{{ $country->long_name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">{{ $errors->first('country_id') }}</span>
                        </div>
                        <div class="field">
                            <label>County / state / region</label>
                            {!! Form::text('state', old('state', $company->state ?? '')) !!}
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>City / town</label>
                            {!! Form::text('city', old('city', $company->city ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('city') }}</span>
                        </div>
                        <div class="field">
                            <label>Postal code</label>
                            {!! Form::text('postal_code', old('postal_code', $company->postal_code ?? '')) !!}
                            <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label>Street address</label>
                            {!! Form::text('address', old('address', $company->address ?? ''), ['id' => 'company_street_address']) !!}
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        </div>
                        <div class="field">
                            <label>Office / building name</label>
                            {!! Form::text('office_building_name', old('office_building_name', $company->office_building_name ?? '')) !!}
                        </div>
                    </div>
                    <div class="field">
                        <label>Map / GPS coordinates</label>
                        {!! Form::text('map_coordinates', old('map_coordinates', $company->map_coordinates ?? ''), ['id' => 'company_map_coordinates', 'readonly' => true]) !!}
                        <div class="field-hint">Coordinates are filled automatically when you select an address or pin a location.</div>
                    </div>
                    <div class="wizard-actions">
                        <a class="wizard-btn" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex - 1]) }}">Back</a>
                        <button class="wizard-btn wizard-btn--primary" type="submit">Save and continue</button>
                    </div>
                {!! Form::close() !!}

                <div class="map-modal" id="company-map-modal" aria-hidden="true">
                    <div class="map-modal__card">
                        <div class="map-modal__head">
                            <div>
                                <h3 style="margin:0;">Pin company location</h3>
                                <div class="field-hint">Drag the marker or click on the map to choose the exact office location.</div>
                            </div>
                            <button class="wizard-btn" type="button" id="close-map-picker">Close</button>
                        </div>
                        <div class="map-modal__body">
                            <div id="company-map-canvas" class="map-canvas"></div>
                            <div class="map-modal__actions">
                                <div class="field-hint" id="company-map-selection">No location pinned yet.</div>
                                <button class="wizard-btn wizard-btn--primary" type="button" id="confirm-map-picker">Use this location</button>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($currentStep === 'phone-verification')
                <h2>Verify phone number</h2>
                <p class="onboarding-copy">Confirm the phone number for the main company contact before submission. OTP expiration and resend windows follow the onboarding configuration.</p>
                <div class="badge-row">
                    <span class="status-badge {{ $company && $company->phone_verified_at ? 'status-badge--ok' : 'status-badge--warn' }}">{{ $company && $company->phone_verified_at ? 'Phone verified' : 'Phone verification pending' }}</span>
                </div>
                <div class="otp-card">
                    <h3>Send verification code</h3>
                    <p class="onboarding-copy">We will send the OTP to +{{ $company->country_code }} {{ $company->mobile_number }}.</p>
                    <div class="otp-actions">
                        {!! Form::open(['url' => 'company/onboarding/send-phone-otp']) !!}
                            <button class="wizard-btn wizard-btn--soft" type="submit">Send / resend OTP</button>
                        {!! Form::close() !!}
                    </div>
                    <hr>
                    {!! Form::open(['url' => 'company/onboarding/verify-phone-otp']) !!}
                        <div class="field">
                            <label>Enter OTP</label>
                            {!! Form::text('otp', '', ['maxlength' => 6]) !!}
                        </div>
                        <div class="wizard-actions">
                            <a class="wizard-btn" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex - 1]) }}">Back</a>
                            <button class="wizard-btn wizard-btn--primary" type="submit">Verify phone</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            @elseif($currentStep === 'email-verification')
                <h2>Verify email address</h2>
                <p class="onboarding-copy">Verify the email used for the company workspace before you can submit the onboarding package for review.</p>
                <div class="badge-row">
                    <span class="status-badge {{ $company && $company->email_verified_at ? 'status-badge--ok' : 'status-badge--warn' }}">{{ $company && $company->email_verified_at ? 'Email verified' : 'Email verification pending' }}</span>
                </div>
                <div class="otp-card">
                    <h3>Email verification</h3>
                    <p class="onboarding-copy">Verification code destination: {{ $company->email }}</p>
                    <div class="otp-actions">
                        {!! Form::open(['url' => 'company/onboarding/send-email-verification']) !!}
                            <button class="wizard-btn wizard-btn--soft" type="submit">Send / resend email code</button>
                        {!! Form::close() !!}
                    </div>
                    <hr>
                    {!! Form::open(['url' => 'company/onboarding/verify-email-otp']) !!}
                        <div class="field">
                            <label>Enter email OTP</label>
                            {!! Form::text('otp', '', ['maxlength' => 6]) !!}
                        </div>
                        <div class="wizard-actions">
                            <a class="wizard-btn" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex - 1]) }}">Back</a>
                            <button class="wizard-btn wizard-btn--primary" type="submit">Verify email</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            @elseif($currentStep === 'logo')
                <h2>Upload company logo</h2>
                <p class="onboarding-copy">Add the operator logo that represents the business throughout the company workspace. You can replace it before final submission.</p>
                <div class="upload-card">
                    <h3>Logo upload</h3>
                    @if($company && $company->profile)
                        <img src="{{ $company->profile }}" alt="{{ $company->name }}">
                    @endif
                    {!! Form::open(['url' => 'company/onboarding/upload-logo', 'files' => true]) !!}
                        <div class="field">
                            <label>Logo file</label>
                            <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp">
                            <span class="text-danger">{{ $errors->first('logo') }}</span>
                            <div class="field-hint">Accepted formats: png, jpg, jpeg, webp.</div>
                        </div>
                        <div class="wizard-actions">
                            <a class="wizard-btn" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex - 1]) }}">Back</a>
                            <button class="wizard-btn wizard-btn--primary" type="submit">{{ $company && $company->profile ? 'Replace logo' : 'Save logo' }}</button>
                        </div>
                    {!! Form::close() !!}

                    @if($company && $company->profile)
                        <div class="wizard-actions" style="margin-top:16px;padding-top:0;border-top:0;">
                            <span class="field-hint">Logo uploaded successfully. Continue to the next step when you are ready.</span>
                            <a class="wizard-btn wizard-btn--primary" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex + 1]) }}">Continue</a>
                        </div>
                    @endif
                </div>
            @elseif($currentStep === 'documents')
                <h2>Upload company documents</h2>
                <p class="onboarding-copy">This step reuses the existing company document configuration and storage flow, so your compliance logic remains intact while onboarding becomes much more guided.</p>
                <div class="doc-grid">
                    @forelse($requiredDocuments as $requiredDocument)
                        @php $uploaded = $uploadedDocumentsById->get($requiredDocument->id); @endphp
                        <div class="doc-card">
                            <h3>{{ $requiredDocument->document_name }}</h3>
                            <div class="doc-card__meta">
                                <span class="doc-chip">Required</span>
                                @if($requiredDocument->expiry_required)
                                    <span class="doc-chip">Expiry required</span>
                                @endif
                                <span class="doc-chip">{{ $uploaded && !empty($uploaded['document']) ? 'Uploaded' : 'Pending upload' }}</span>
                            </div>
                            @if($uploaded && !empty($uploaded['document']))
                                <div class="field-hint" style="margin-bottom:12px;">
                                    <a href="{{ $uploaded['document'] }}" target="_blank">View current file</a>
                                </div>
                            @endif
                            {!! Form::open(['url' => 'company/onboarding/upload-document', 'files' => true]) !!}
                                {!! Form::hidden('document_id', $requiredDocument->id) !!}
                                <div class="field">
                                    <label>Upload or replace document</label>
                                    <input type="file" name="file_{{ $requiredDocument->id }}" accept=".pdf,.png,.jpg,.jpeg">
                                </div>
                                @if($requiredDocument->expiry_required)
                                    <div class="field">
                                        <label>Expiry date</label>
                                        {!! Form::date('expired_date_'.$requiredDocument->id, old('expired_date_'.$requiredDocument->id, $uploaded['expired_date'] ?? '')) !!}
                                    </div>
                                @endif
                                <button class="wizard-btn wizard-btn--soft" type="submit">Save document</button>
                            {!! Form::close() !!}
                        </div>
                    @empty
                        <div class="review-card">
                            <h3>No document rules are configured</h3>
                            <p class="onboarding-copy mb-0">This company does not currently have required onboarding documents for the selected country.</p>
                        </div>
                    @endforelse
                </div>
                <div class="wizard-actions">
                    <a class="wizard-btn" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex - 1]) }}">Back</a>
                    <a class="wizard-btn wizard-btn--primary" href="{{ url('company/onboarding?step=review') }}">Continue to review</a>
                </div>
            @else
                <h2>Review and submit</h2>
                <p class="onboarding-copy">Review the full company onboarding package before it goes to admin. Submission only succeeds when every enabled requirement is complete.</p>
                <div class="badge-row">
                    <span class="status-badge {{ $company && $company->phone_verified_at ? 'status-badge--ok' : 'status-badge--warn' }}">Phone {{ $company && $company->phone_verified_at ? 'verified' : 'pending' }}</span>
                    <span class="status-badge {{ $company && $company->email_verified_at ? 'status-badge--ok' : 'status-badge--warn' }}">Email {{ $company && $company->email_verified_at ? 'verified' : 'pending' }}</span>
                    <span class="status-badge {{ $company && $company->profile ? 'status-badge--ok' : 'status-badge--warn' }}">Logo {{ $company && $company->profile ? 'uploaded' : 'pending' }}</span>
                    <span class="status-badge {{ collect($uploadedDocuments)->count() ? 'status-badge--ok' : 'status-badge--warn' }}">Documents {{ collect($uploadedDocuments)->count() ? 'uploaded' : 'pending' }}</span>
                </div>
                <div class="review-grid">
                    <div class="review-card">
                        <h3>Company</h3>
                        <div class="review-item"><span>Legal name</span>{{ $company->legal_name ?: $company->name }}</div>
                        <div class="review-item"><span>Registration no.</span>{{ $company->registration_number ?: 'Not provided' }}</div>
                        <div class="review-item"><span>Type</span>{{ ucfirst(str_replace('_',' ', $company->business_type ?: 'Not provided')) }}</div>
                        <div class="review-item"><span>Service area</span>{{ $company->service_area ?: 'Not provided' }}</div>
                    </div>
                    <div class="review-card">
                        <h3>Contact and address</h3>
                        <div class="review-item"><span>Contact person</span>{{ trim(($company->contact_first_name ?: '').' '.($company->contact_last_name ?: '')) ?: 'Not provided' }}</div>
                        <div class="review-item"><span>Email</span>{{ $company->email ?: 'Not provided' }}</div>
                        <div class="review-item"><span>Phone</span>{{ $company->country_code ? '+'.$company->country_code.' ' : '' }}{{ $company->mobile_number ?: 'Not provided' }}</div>
                        <div class="review-item"><span>Address</span>{{ $company->address ?: 'Not provided' }}</div>
                    </div>
                </div>
                {!! Form::open(['url' => 'company/onboarding/step/review']) !!}
                    <div class="field mt-4">
                        <label style="display:flex;align-items:flex-start;gap:10px;font-weight:700;">
                            <input type="checkbox" name="declaration" value="1" style="margin-top:4px;min-height:auto;width:auto;">
                            <span>I confirm that the information provided is accurate and I am authorized to register this company.</span>
                        </label>
                        <span class="text-danger">{{ $errors->first('declaration') }}</span>
                    </div>
                    <div class="wizard-actions">
                        <a class="wizard-btn" href="{{ url('company/onboarding?step='.collect($wizardSteps)->pluck('slug')[$stepIndex - 1]) }}">Back</a>
                        <button class="wizard-btn wizard-btn--primary" type="submit">Submit for approval</button>
                    </div>
                {!! Form::close() !!}
            @endif
        </section>
    </div>
</div>
@if($currentStep === 'address')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
            return;
        }

        const searchInput = document.getElementById('company_address_search');
        const streetInput = document.getElementById('company_street_address');
        const cityInput = document.querySelector('input[name="city"]');
        const stateInput = document.querySelector('input[name="state"]');
        const postalInput = document.querySelector('input[name="postal_code"]');
        const officeInput = document.querySelector('input[name="office_building_name"]');
        const countrySelect = document.querySelector('select[name="country_id"]');
        const coordinatesInput = document.getElementById('company_map_coordinates');
        const resultsList = document.querySelector('.company-address-results');
        const modal = document.getElementById('company-map-modal');
        const openMapButton = document.getElementById('open-map-picker');
        const closeMapButton = document.getElementById('close-map-picker');
        const confirmMapButton = document.getElementById('confirm-map-picker');
        const mapSelectionLabel = document.getElementById('company-map-selection');
        const geocoder = new google.maps.Geocoder();
        const autocompleteService = new google.maps.places.AutocompleteService();
        const placeService = new google.maps.places.PlacesService(document.createElement('div'));
        const sessionToken = new google.maps.places.AutocompleteSessionToken();
        let map;
        let marker;
        let selectedLocation = null;

        function debounce(fn, delay) {
            let timer;
            return function () {
                const args = arguments;
                const context = this;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    fn.apply(context, args);
                }, delay);
            };
        }

        function setAddressComponents(place) {
            const components = {
                street_number: '',
                route: '',
                sublocality_level_1: '',
                locality: '',
                administrative_area_level_1: '',
                postal_code: '',
            };

            (place.address_components || []).forEach(function (component) {
                const type = component.types[0];
                if (Object.prototype.hasOwnProperty.call(components, type)) {
                    components[type] = component.long_name || component.short_name || '';
                }
            });

            const streetAddress = [components.street_number, components.route].filter(Boolean).join(' ').trim();
            if (streetInput) {
                streetInput.value = streetAddress || place.formatted_address || '';
            }
            if (cityInput) {
                cityInput.value = components.locality || cityInput.value || '';
            }
            if (stateInput) {
                stateInput.value = components.administrative_area_level_1 || stateInput.value || '';
            }
            if (postalInput) {
                postalInput.value = components.postal_code || postalInput.value || '';
            }
            if (officeInput && !officeInput.value) {
                officeInput.value = components.sublocality_level_1 || '';
            }

            if (searchInput) {
                searchInput.value = place.formatted_address || streetInput.value || '';
            }

            if (countrySelect) {
                const placeCountry = (place.address_components || []).find(function (component) {
                    return component.types.indexOf('country') !== -1;
                });

                if (placeCountry) {
                    const placeCountryCode = placeCountry.short_name;
                    const matchingOption = Array.from(countrySelect.options).find(function (option) {
                        return option.text.toLowerCase().indexOf(placeCountry.long_name.toLowerCase()) !== -1;
                    });
                    if (matchingOption) {
                        countrySelect.value = matchingOption.value;
                    } else if (placeCountryCode) {
                        const codeMatch = Array.from(countrySelect.options).find(function (option) {
                            return option.text.toLowerCase().indexOf(placeCountryCode.toLowerCase()) !== -1;
                        });
                        if (codeMatch) {
                            countrySelect.value = codeMatch.value;
                        }
                    }
                }
            }
        }

        function setCoordinates(latLng) {
            if (!latLng || !coordinatesInput) {
                return;
            }

            const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
            const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
            coordinatesInput.value = lat.toFixed(6) + ',' + lng.toFixed(6);
            selectedLocation = { lat: lat, lng: lng };
            if (mapSelectionLabel) {
                mapSelectionLabel.textContent = 'Pinned coordinates: ' + coordinatesInput.value;
            }
        }

        function updateMap(latLng) {
            if (!map) {
                map = new google.maps.Map(document.getElementById('company-map-canvas'), {
                    center: latLng,
                    zoom: 15,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false,
                });

                marker = new google.maps.Marker({
                    map: map,
                    position: latLng,
                    draggable: true,
                });

                map.addListener('click', function (event) {
                    marker.setPosition(event.latLng);
                    setCoordinates(event.latLng);
                });

                marker.addListener('dragend', function (event) {
                    setCoordinates(event.latLng);
                });
            } else {
                map.setCenter(latLng);
                marker.setPosition(latLng);
            }

            setCoordinates(latLng);
            google.maps.event.trigger(map, 'resize');
        }

        function reverseGeocode(latLng) {
            geocoder.geocode({ location: latLng }, function (results, status) {
                if (status === 'OK' && results && results.length) {
                    const place = results[0];
                    setAddressComponents(place);
                }
            });
        }

        function selectPrediction(placeId) {
            placeService.getDetails({
                placeId: placeId,
                fields: ['formatted_address', 'geometry', 'address_components', 'name'],
            }, function (place, status) {
                if (status !== google.maps.places.PlacesServiceStatus.OK || !place.geometry) {
                    return;
                }

                setAddressComponents(place);
                setCoordinates(place.geometry.location);
                updateMap(place.geometry.location);
                resultsList.style.display = 'none';
            });
        }

        const renderPredictions = function (predictions, status) {
            if (!resultsList) {
                return;
            }

            resultsList.innerHTML = '';
            resultsList.style.display = 'none';

            if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions || !predictions.length) {
                return;
            }

            resultsList.innerHTML = predictions.map(function (prediction) {
                return '<li class="autocomplete-item" data-place-id="' + prediction.place_id + '">' +
                    '<span>' + prediction.description + '</span>' +
                '</li>';
            }).join('');

            resultsList.querySelectorAll('.autocomplete-item').forEach(function (item) {
                item.addEventListener('click', function () {
                    selectPrediction(item.getAttribute('data-place-id'));
                });
            });

            resultsList.style.display = 'block';
        };

        if (searchInput && resultsList) {
            searchInput.addEventListener('input', debounce(function () {
                const value = searchInput.value.trim();
                if (value.length < 3) {
                    resultsList.innerHTML = '';
                    resultsList.style.display = 'none';
                    return;
                }

                autocompleteService.getPlacePredictions({
                    input: value,
                    sessionToken: sessionToken,
                }, renderPredictions);
            }, 350));
        }

        function openMap() {
            modal.classList.add('is-open');

            const currentCoordinates = (coordinatesInput && coordinatesInput.value || '').split(',');
            const currentLat = parseFloat(currentCoordinates[0]);
            const currentLng = parseFloat(currentCoordinates[1]);
            const fallback = (!Number.isNaN(currentLat) && !Number.isNaN(currentLng))
                ? { lat: currentLat, lng: currentLng }
                : { lat: -1.286389, lng: 36.817223 };

            setTimeout(function () {
                updateMap(fallback);
            }, 80);
        }

        function closeMap() {
            modal.classList.remove('is-open');
        }

        if (openMapButton) {
            openMapButton.addEventListener('click', openMap);
        }
        if (closeMapButton) {
            closeMapButton.addEventListener('click', closeMap);
        }
        if (confirmMapButton) {
            confirmMapButton.addEventListener('click', function () {
                if (selectedLocation) {
                    reverseGeocode(selectedLocation);
                }
                closeMap();
            });
        }

        document.addEventListener('click', function (event) {
            if (resultsList && searchInput && !resultsList.contains(event.target) && event.target !== searchInput) {
                resultsList.style.display = 'none';
            }
        });

        if (coordinatesInput && coordinatesInput.value.trim() !== '') {
            const initial = coordinatesInput.value.split(',');
            const initialLat = parseFloat(initial[0]);
            const initialLng = parseFloat(initial[1]);
            if (!Number.isNaN(initialLat) && !Number.isNaN(initialLng)) {
                selectedLocation = { lat: initialLat, lng: initialLng };
                if (mapSelectionLabel) {
                    mapSelectionLabel.textContent = 'Pinned coordinates: ' + coordinatesInput.value;
                }
            }
        }
    });
</script>
@endif
@stop
