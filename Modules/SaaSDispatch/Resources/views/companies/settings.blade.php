@extends('admin.template')

@section('title-sub', 'SaaS Settings')
@section('pagetitle', 'SaaS Settings')

@section('main')
<div id="layout-wrapper">
    @include('saasdispatch::partials.company_tabs')

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Company SaaS Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $isAdmin ? route('saasdispatch.companies.settings.update', $company) : route('saasdispatch.self.settings.update') }}" class="row g-4">
                @csrf
                @method('PUT')

                <div class="col-md-4">
                    <label class="form-label">Dispatch Mode</label>
                    <select name="dispatch_mode" class="form-select">
                        @foreach(['global' => 'Global', 'saas' => 'SaaS', 'hybrid' => 'Hybrid'] as $value => $label)
                            <option value="{{ $value }}" {{ old('dispatch_mode', $setting->dispatch_mode) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['Active', 'Inactive'] as $status)
                            <option value="{{ $status }}" {{ old('status', $setting->status ?: 'Active') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Domain</label>
                    <input type="text" name="domain" value="{{ old('domain', $setting->domain) }}" class="form-control" placeholder="dispatch.example.com">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Subdomain</label>
                    <input type="text" name="subdomain" value="{{ old('subdomain', $setting->subdomain) }}" class="form-control" placeholder="acme-dispatch">
                </div>

                <div class="col-md-8 d-flex flex-wrap gap-4 align-items-end">
                    @if($canManageEnablement)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_saas_enabled" value="1" id="is_saas_enabled" {{ old('is_saas_enabled', $setting->is_saas_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_saas_enabled">Enable SaaS for this company</label>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">SaaS enablement is controlled by the platform admin.</div>
                    @endif
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="use_own_branding" value="1" id="use_own_branding" {{ old('use_own_branding', $setting->use_own_branding) ? 'checked' : '' }}>
                        <label class="form-check-label" for="use_own_branding">Use company branding</label>
                    </div>
                </div>

                <div class="col-12">
                    <hr>
                    <h6 class="mb-3">Ownership Rules</h6>
                    <div class="row g-3">
                        @foreach([
                            'use_own_locations' => 'Service Areas',
                            'use_own_fares' => 'Fare Rules',
                            'use_own_fees' => 'Fee Settings',
                            'use_own_vehicle_types' => 'Vehicle Types',
                            'use_own_dispatch_settings' => 'Dispatch Settings',
                        ] as $field => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" {{ old($field, $setting->{$field}) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $field }}">Use own {{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
