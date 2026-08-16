@extends('admin.template')

@section('title-sub', 'Company Fare Rules')
@section('pagetitle', 'Company Fare Rules')

@section('main')
<div id="layout-wrapper">
    @include('saasdispatch::partials.company_tabs')

    <div class="card">
        <div class="card-body border-bottom">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h3 class="card-title mb-1">{{ $showOnlyNew ?? false ? 'Add Fare Setup' : 'Trip Fare Setup' }}</h3>
                    <p class="text-muted mb-0">
                        {{ $showOnlyNew ?? false ? 'New service areas without fare setup are listed below.' : 'Manage company ride sharing fares zone wise and open a single setup screen for all company vehicle types in each zone.' }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @if(!($showOnlyNew ?? false))
                        <a href="{{ $isAdmin ? route('saasdispatch.companies.fare-rules.create', $company) : route('saasdispatch.self.fare-rules.create') }}" class="btn btn-primary">Add Fare Setup</a>
                    @endif
                    <form method="GET" action="{{ url()->current() }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control" placeholder="Search" value="{{ $search ?? '' }}" style="min-width: 240px;">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(($zones ?? collect())->isEmpty())
                <div class="text-center text-muted py-5">
                    {{ $showOnlyNew ?? false ? 'No new service areas available for fare setup.' : 'No service areas found.' }}
                </div>
            @else
                <div class="d-flex flex-column gap-4">
                    @foreach($zones as $zone)
                        <div class="card border shadow-none mb-0">
                            <div class="card-body">
                                <div class="row align-items-center g-3">
                                    <div class="col-xl-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px;">
                                                {{ $zone['index'] }}
                                            </div>
                                            <div>
                                                <h5 class="mb-2">{{ $zone['name'] }}</h5>
                                                <div class="text-muted">
                                                    {{ $zone['configured'] ?? false ? 'Configured Vehicle Types' : 'Available Vehicle Types' }} :
                                                    <span class="text-primary fw-semibold">{{ $zone['configured_count'] ?? collect($zone['vehicle_names'] ?? [])->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-5">
                                        <div class="fw-semibold mb-2">Available Vehicle Category In This Zone</div>
                                        <div class="d-flex flex-wrap gap-3">
                                            @forelse(($zone['vehicle_names'] ?? []) as $vehicleName)
                                                <span class="d-inline-flex align-items-center gap-2 text-muted">
                                                    <i class="fa fa-check-circle text-primary"></i>
                                                    {{ $vehicleName }}
                                                </span>
                                            @empty
                                                <span class="text-muted">No configured vehicles yet.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <div class="col-xl-3 text-xl-end">
                                        <a href="{{ $zone['setup_url'] }}" class="btn btn-primary">
                                            <i class="fa fa-cog me-2"></i>
                                            {{ $zone['configured'] ?? false ? 'View Fare Setup' : 'Add Fare Setup' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
