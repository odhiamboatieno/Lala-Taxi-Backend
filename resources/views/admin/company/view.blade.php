@extends('admin.template')

@section('title-sub', 'Company Management')
@section('pagetitle', 'Manage Companies')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex align-items-end flex-wrap gap-3">
            <div class="flex-grow-1">
                <h4 class="mb-1">Manage Companies</h4>
                <div class="text-muted">Review operator accounts, driver totals, contact information, and company status.</div>
            </div>
            @if(optional(Auth::guard('admin')->user())->can('view_company'))
                <a href="{{ url('admin/company-onboarding') }}" class="btn btn-light">
                    <i class="bi bi-shield-check me-2"></i>Onboarding Queue
                </a>
            @endif
            @if(optional(Auth::guard('admin')->user())->can('create_company'))
                <a href="{{ url('admin/add_company') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle-dotted me-2"></i>{{ __('messages.admin.manage_company_page.add_company') }}
                </a>
            @endif
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/company') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Company name, email, mobile or ID">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" {{ $statusFilter === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $statusFilter === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="Pending" {{ $statusFilter === 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/company') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Drivers</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr>
                                <td>#{{ $company->id }}</td>
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->drivers_count }}</td>
                                <td>{{ protectedString($company->email) }}</td>
                                <td>{{ $company->mobile_number }}</td>
                                <td><span class="badge {{ $company->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $company->status }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if(optional(Auth::guard('admin')->user())->can('update_company'))
                                            <a href="{{ url('admin/edit_company/'.$company->id) }}" class="btn btn-sm btn-outline-success"><i class="ri-pencil-line"></i></a>
                                        @endif
                                        @if(optional(Auth::guard('admin')->user())->can('delete_company') && $company->id != 1)
                                            <a href="{{ url('admin/delete_company/'.$company->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this company?')"><i class="ri-delete-bin-line"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No companies found.</td></tr>
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
