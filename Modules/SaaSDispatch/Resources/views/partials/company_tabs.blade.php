@php
    $companyRouteParam = $isAdmin ? ['company' => $company->id] : [];
@endphp

<div class="card mb-4">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div>
            <h5 class="mb-1">{{ $company->name }}</h5>
            <div class="text-muted small">{{ $company->email }}</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $isAdmin ? route('saasdispatch.companies.settings.edit', $companyRouteParam) : route('saasdispatch.self.settings.edit') }}"
               class="btn btn-sm {{ request()->routeIs('saasdispatch.*settings*') && !request()->routeIs('saasdispatch.*dispatch-settings*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Settings
            </a>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.service-areas.index', $companyRouteParam) : route('saasdispatch.self.service-areas.index') }}"
               class="btn btn-sm {{ request()->routeIs('saasdispatch.*service-areas*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Manage Zones
            </a>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.vehicle-types.index', $companyRouteParam) : route('saasdispatch.self.vehicle-types.index') }}"
               class="btn btn-sm {{ request()->routeIs('saasdispatch.*vehicle-types*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Vehicle Types
            </a>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.fare-rules.index', $companyRouteParam) : route('saasdispatch.self.fare-rules.index') }}"
               class="btn btn-sm {{ request()->routeIs('saasdispatch.*fare-rules*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Fare Rules
            </a>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.fee-settings.index', $companyRouteParam) : route('saasdispatch.self.fee-settings.index') }}"
               class="btn btn-sm {{ request()->routeIs('saasdispatch.*fee-settings*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Fee Settings
            </a>
            <a href="{{ $isAdmin ? route('saasdispatch.companies.dispatch-settings.edit', $companyRouteParam) : route('saasdispatch.self.dispatch-settings.edit') }}"
               class="btn btn-sm {{ request()->routeIs('saasdispatch.*dispatch-settings*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Dispatch
            </a>
        </div>
    </div>
</div>
