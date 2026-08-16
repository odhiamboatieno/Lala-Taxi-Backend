@extends('admin.template')

@section('title-sub', 'Company Onboarding')
@section('pagetitle', 'Company Onboarding')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex flex-wrap gap-3 align-items-center">
            <div class="flex-grow-1">
                <h4 class="mb-1">Company onboarding requests</h4>
                <div class="text-muted">Review draft, pending, approved, rejected, and suspended operator onboarding records from one queue.</div>
            </div>
            <a href="{{ url('admin/company') }}" class="btn btn-light">Manage companies</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/company-onboarding') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input class="form-control" name="search" value="{{ $search }}" placeholder="Company, email, or reference">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        @foreach(['pending_approval' => 'Pending approval', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended', 'draft' => 'Draft', 'documents_pending' => 'Documents pending'] as $value => $label)
                            <option value="{{ $value }}" {{ $statusFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                        <option value="" {{ $statusFilter === '' ? 'selected' : '' }}>All statuses</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary">Filter queue</button>
                    <a href="{{ url('admin/company-onboarding') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>Reference</th>
                            <th>Contact</th>
                            <th>Drivers</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            @php $queueStatus = $company->onboarding_status ?: $company->approval_status ?: 'draft'; @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $company->name ?: $company->legal_name }}</div>
                                    <div class="text-muted small">{{ $company->business_type ?: 'Company' }}</div>
                                </td>
                                <td>{{ $company->registration_reference ?: 'N/A' }}</td>
                                <td>
                                    <div>{{ $company->email }}</div>
                                    <div class="text-muted small">+{{ $company->country_code }} {{ $company->mobile_number }}</div>
                                </td>
                                <td>{{ $company->drivers_count }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ ucfirst(str_replace('_', ' ', $queueStatus)) }}</span></td>
                                <td>{{ $company->submitted_for_approval_at ? $company->submitted_for_approval_at->format('d M Y H:i') : 'Not submitted' }}</td>
                                <td class="text-end">
                                    <a href="{{ url('admin/company-onboarding/'.$company->id) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No onboarding requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($companies->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $companies->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
