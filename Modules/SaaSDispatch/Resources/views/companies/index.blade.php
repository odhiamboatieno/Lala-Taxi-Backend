@extends('admin.template')

@section('title-sub', 'SaaS Companies')
@section('pagetitle', 'SaaS Companies')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Company SaaS Profiles</h5>
            <a href="{{ route('saasdispatch.dashboard') }}" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Dispatch Mode</th>
                            <th>Domain</th>
                            <th>Subdomain</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr>
                                <td>
                                    <div>{{ $company->name }}</div>
                                    <small class="text-muted">{{ $company->email }}</small>
                                </td>
                                <td>{{ optional($company->saas_setting)->is_saas_enabled ? 'Enabled' : 'Disabled' }}</td>
                                <td>{{ optional($company->saas_setting)->dispatch_mode ?: 'global' }}</td>
                                <td>{{ optional($company->saas_setting)->domain ?: '-' }}</td>
                                <td>{{ optional($company->saas_setting)->subdomain ?: '-' }}</td>
                                <td class="text-end">
                                    @if(Auth::guard('admin')->user()?->can('manage_saas_companies') || Auth::guard('admin')->user()?->can('manage_company_branding') || Auth::guard('admin')->user()?->can('manage_company_domains'))
                                        <a href="{{ route('saasdispatch.companies.settings.edit', $company) }}" class="btn btn-sm btn-primary">Settings</a>
                                    @endif
                                    @if(Auth::guard('admin')->user()?->can('manage_company_service_areas') || Auth::guard('admin')->user()?->can('manage_company_vehicle_types') || Auth::guard('admin')->user()?->can('manage_company_fares') || Auth::guard('admin')->user()?->can('manage_company_fees') || Auth::guard('admin')->user()?->can('manage_company_dispatch_settings') || Auth::guard('admin')->user()?->can('manage_saas_companies'))
                                        <a href="{{ route('saasdispatch.companies.service-areas.index', $company) }}" class="btn btn-sm btn-outline-primary">Resources</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No companies available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $companies->links() }}</div>
    </div>
</div>
@endsection
