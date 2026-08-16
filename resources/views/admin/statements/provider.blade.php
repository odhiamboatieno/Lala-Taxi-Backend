@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_statements.drivers') }}</h4>
                    <div class="text-muted">Review driver statement summaries with server-rendered search and pagination.</div>
                </div>
            </div>

            <form method="GET" action="{{ url(LOGIN_USER_TYPE . '/statements/driver') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search driver name, email, or phone">
                </div>
                @if(LOGIN_USER_TYPE === 'admin')
                    <div class="col-md-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select">
                            <option value="">All</option>
                            @foreach($companyOptions as $companyId => $companyName)
                                <option value="{{ $companyId }}" {{ (string) ($filters['company_id'] ?? '') === (string) $companyId ? 'selected' : '' }}>{{ $companyName }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" {{ ($filters['status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url(LOGIN_USER_TYPE . '/statements/driver') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            @if(LOGIN_USER_TYPE === 'admin')
                                <th>Company</th>
                            @endif
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Total Rides</th>
                            <th>Earnings</th>
                            <th>Commission</th>
                            <th>Joined</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr>
                                <td>#{{ $driver->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ trim($driver->first_name . ' ' . $driver->last_name) }}</div>
                                    <div class="text-muted small">{{ $driver->email ?: '-' }}</div>
                                </td>
                                @if(LOGIN_USER_TYPE === 'admin')
                                    <td>{{ optional($driver->company)->name ?: 'Admin' }}</td>
                                @endif
                                <td>{{ isLiveEnv() ? $driver->hidden_mobile_number : $driver->mobile_number }}</td>
                                <td>
                                    <span class="badge {{ $driver->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $driver->status }}</span>
                                </td>
                                <td>{{ $driver->total_rides_driver }}</td>
                                <td>{{ html_entity_decode(session()->get('symbol')) }}{{ $driver->total_earnings }}</td>
                                <td>{{ html_entity_decode(session()->get('symbol')) }}{{ LOGIN_USER_TYPE === 'company' ? $driver->total_company_admin_commission : $driver->total_commission }}</td>
                                <td>{{ $driver->date_time_join }}</td>
                                <td class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE . '/view_driver_statement/' . $driver->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ LOGIN_USER_TYPE === 'admin' ? 10 : 9 }}" class="text-center py-5 text-muted">No driver statements found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($drivers->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $drivers->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
