@extends('admin.template')

@section('title-sub', 'Customer Levels')
@section('pagetitle', 'Customer Levels')

@section('main')
<div id="layout-wrapper">
    @php $isCompanyScope = defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company'; @endphp

    <div class="row g-4 mb-4">
        <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Customer Levels</h3>
                <p class="text-muted mb-0">Define promotion targets, rewards, and operator-specific tiers for riders.</p>
                @if($isCompanyScope)
                    <span class="badge bg-info-subtle text-info mt-2">
                        <i class="ri-information-line me-1"></i>Company scope &mdash; you can adopt the admin-configured levels and override their values.
                    </span>
                @endif
            </div>
            <a href="{{ route('userlevels.customer-levels.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Add Level
            </a>
        </div>
        @foreach($cards as $card)
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase text-muted small mb-1">Customers</p>
                                <h4 class="mb-1">{{ $card['name'] }}</h4>
                                <span class="badge {{ $card['status'] === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $card['status'] }}</span>
                                @if(!empty($card['inherited']))
                                    <span class="badge bg-warning-subtle text-warning ms-1" title="This level inherits from the admin-configured level of the same name.">Inherited</span>
                                @endif
                            </div>
                            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-end justify-content-center" style="width:52px;height:52px;">
                                <i class="{{ $card['icon'] ?: config('user_levels.types.rider.icon') }} fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-4 fs-2 fw-semibold">{{ $card['count'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($isCompanyScope && !empty($inheritableAdminLevels))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Adopt an Admin Level</p>
                        <h5 class="mb-1">Use the platform owner's customer levels as your base</h5>
                        <p class="text-muted mb-0">Click "Adopt" to clone an admin level for your company.  After adoption you can override any value you want from the level's edit form.</p>
                    </div>
                </div>
                <div class="row g-3">
                    @foreach($inheritableAdminLevels as $adminLevel)
                        @php
                            $alreadyAdopted = $levels->getCollection()->contains(fn ($l) => (int) $l->inherits_from_id === (int) $adminLevel->id);
                        @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="border rounded-3 p-3 h-100 d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="d-flex align-items-end gap-2 mb-1">
                                        <i class="{{ $adminLevel->icon ?: config('user_levels.types.rider.icon') }} text-primary"></i>
                                        <strong>{{ $adminLevel->name }}</strong>
                                    </div>
                                    <small class="text-muted">Sequence {{ $adminLevel->sequence }} &middot; {{ $adminLevel->status }}</small>
                                </div>
                                @if($alreadyAdopted)
                                    <span class="badge bg-success-subtle text-success">Adopted</span>
                                @else
                                    <form method="POST" action="{{ route('userlevels.customer-levels.inherit', $adminLevel) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Adopt</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search level name">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        @foreach(['Active', 'Inactive'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a href="{{ route('userlevels.customer-levels.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>SL</th>
                            <th>Level</th>
                            <th>Target To Proceed Level</th>
                            <th>Total Customers</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $index => $level)
                            @php
                                $effectiveName = method_exists($level, 'effectiveName') ? $level->effectiveName() : $level->name;
                                $effectiveIcon = method_exists($level, 'effectiveIcon') ? $level->effectiveIcon() : $level->icon;
                                $effectiveStatus = method_exists($level, 'effectiveStatus') ? $level->effectiveStatus() : $level->status;
                                $effectiveTarget = method_exists($level, 'effectiveTargetConfig') ? $level->effectiveTargetConfig() : ($level->target_config ?? []);
                            @endphp
                            <tr>
                                <td>{{ $levels->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-end gap-3">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-end justify-content-center" style="width:46px;height:46px;">
                                            <i class="{{ $effectiveIcon ?: config('user_levels.types.rider.icon') }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $effectiveName ?: $level->name }}</div>
                                            <small class="text-muted">Sequence {{ $level->sequence }}</small>
                                            @if($level->inherits_from_id)
                                                <span class="badge bg-warning-subtle text-warning ms-1">Inherits admin</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    Ride Complete: {{ $effectiveTarget['minimum_completed_trips'] ?? 0 }}<br>
                                    Spend Amount: {{ number_format((float) ($effectiveTarget['minimum_spend'] ?? 0), 2) }}<br>
                                    Reviews Given: {{ $effectiveTarget['minimum_reviews'] ?? 0 }}<br>
                                    Cancellation Rate: {{ $effectiveTarget['maximum_cancellation_rate'] ?? 100 }}%
                                </td>
                                <td>{{ $level->assignments()->count() }}</td>
                                <td>
                                    <form action="{{ route('userlevels.customer-levels.toggle', $level) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $effectiveStatus === 'Active' ? 'btn-success' : 'btn-outline-danger' }}">{{ $effectiveStatus }}</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('userlevels.customer-levels.edit', $level) }}" class="btn btn-sm btn-primary"><i class="ri-pencil-line"></i></a>
                                    @if($level->inherits_from_id)
                                        <form action="{{ route('userlevels.customer-levels.unlink', $level) }}" method="POST" class="d-inline" onsubmit="return confirm('Drop the inheritance?  Only the values stored on this level will remain.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Unlink from admin level"><i class="ri-link-unlink"></i></button>
                                        </form>
                                    @endif
                                    <form action="{{ route('userlevels.customer-levels.destroy', $level) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this level?')"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No customer levels found yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $levels->links() }}
        </div>
    </div>
</div>
@endsection

