@extends('admin.template')

@section('title-sub', 'SaaS Dispatch')
@section('pagetitle', 'SaaS Dispatch')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Module Status</div>
                    <h4 class="mt-2 mb-0">{{ $moduleEnabled ? 'Enabled' : 'Disabled' }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Enabled Companies</div>
                    <h4 class="mt-2 mb-0">{{ $stats['enabledCompanies'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Vehicle Types</div>
                    <h4 class="mt-2 mb-0">{{ $stats['companyVehicleTypes'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Service Areas</div>
                    <h4 class="mt-2 mb-0">{{ $stats['serviceAreas'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
        <div class="row g-4 mb-4">
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Global Module Control</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Platform company ID: <strong>{{ $platformCompanyId }}</strong></p>
                        <form method="POST" action="{{ route('saasdispatch.module.toggle') }}">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $moduleEnabled ? 0 : 1 }}">
                            <button class="btn {{ $moduleEnabled ? 'btn-outline-danger' : 'btn-primary' }}">
                                {{ $moduleEnabled ? 'Disable Module' : 'Enable Module' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Companies</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('saasdispatch.modules.index') }}" class="btn btn-sm btn-outline-primary">SaaS Modules</a>
                            <a href="{{ route('saasdispatch.companies.index') }}" class="btn btn-sm btn-outline-primary">Manage Companies</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Dispatch Mode</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCompanies as $item)
                                        <tr>
                                            <td>
                                                <div>{{ $item->name }}</div>
                                                <small class="text-muted">{{ $item->email }}</small>
                                            </td>
                                            <td>{{ optional($item->saas_setting)->is_saas_enabled ? 'Enabled' : 'Disabled' }}</td>
                                            <td>{{ optional($item->saas_setting)->dispatch_mode ?: 'global' }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('saasdispatch.companies.settings.edit', $item) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No companies found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $company->name }}</h5>
                    <div class="text-muted">Configure your dispatch module, service areas, vehicles, fares, fees, and routing rules.</div>
                </div>
                <a href="{{ route('saasdispatch.self.settings.edit') }}" class="btn btn-primary">Open Settings</a>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Coverage</h5></div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Global vehicle categories: {{ $stats['globalVehicleCategories'] }}</li>
                        <li>Fare rules: {{ $stats['fareRules'] }}</li>
                        <li>Fee settings: {{ $stats['feeSettings'] }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Quick Links</h5></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @if($isAdmin)
                        <a href="{{ route('saasdispatch.global-vehicle-categories.index') }}" class="btn btn-outline-primary">Global Categories</a>
                        <a href="{{ route('saasdispatch.companies.index') }}" class="btn btn-outline-primary">Company Settings</a>
                    @else
                        <a href="{{ route('saasdispatch.self.manage-zones.index') }}" class="btn btn-outline-primary">Manage Zones</a>
                        <a href="{{ route('saasdispatch.self.vehicle-types.index') }}" class="btn btn-outline-primary">Vehicle Types</a>
                        <a href="{{ route('saasdispatch.self.fare-rules.index') }}" class="btn btn-outline-primary">Fare Rules</a>
                        <a href="{{ route('saasdispatch.self.fee-settings.index') }}" class="btn btn-outline-primary">Manage Fees</a>
                        <a href="{{ route('saasdispatch.self.dispatch-settings.edit') }}" class="btn btn-outline-primary">Manage Settings</a>
                    @endif
                </div>
            </div>
        </div>
        @if($isAdmin)
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Feature Modules</h5>
                        <a href="{{ route('saasdispatch.modules.index') }}" class="btn btn-sm btn-outline-primary">Manage Modules</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Installed</th>
                                        <th>Enabled</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($featureModules as $module)
                                        <tr>
                                            <td>{{ $module->name }}</td>
                                            <td>{{ $module->is_installed ? 'Yes' : 'No' }}</td>
                                            <td>{{ $module->is_enabled ? 'Yes' : 'No' }}</td>
                                            <td>{{ $module->status }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No feature modules found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
