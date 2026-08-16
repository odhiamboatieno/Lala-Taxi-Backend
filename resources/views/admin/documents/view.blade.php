@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_documents.title') }}</h4>
                    <div class="text-muted">Manage verification requirements with a uniform searchable table.</div>
                </div>
                @if(Auth::guard('admin')->user()->can('create_documents'))
                    <a class="btn btn-primary" href="{{ url('admin/add_document') }}">
                        <i class="ri-add-line me-1"></i>{{ __('messages.admin.manage_documents.add') }}
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ url('admin/documents') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by document name, type, or status">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Document For</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="Driver" {{ ($filters['type'] ?? '') === 'Driver' ? 'selected' : '' }}>Driver</option>
                        <option value="Vehicle" {{ ($filters['type'] ?? '') === 'Vehicle' ? 'selected' : '' }}>Vehicle</option>
                        <option value="Company" {{ ($filters['type'] ?? '') === 'Company' ? 'selected' : '' }}>Company</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <select name="country" class="form-select">
                        <option value="">All</option>
                        <option value="all" {{ ($filters['country'] ?? '') === 'all' ? 'selected' : '' }}>All Countries</option>
                        @foreach($countryOptions as $countryId => $countryName)
                            <option value="{{ $countryId }}" {{ (string) ($filters['country'] ?? '') === (string) $countryId ? 'selected' : '' }}>{{ $countryName }}</option>
                        @endforeach
                    </select>
                </div>
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
                    <a href="{{ url('admin/documents') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Document For</th>
                            <th>Country</th>
                            <th>Document Name</th>
                            <th>Expiry Rule</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr>
                                <td>#{{ $document->id }}</td>
                                <td>{{ $document->type }}</td>
                                <td>{{ $document->document_for ?: '-' }}</td>
                                <td class="fw-semibold">{{ $document->document_name }}</td>
                                <td>{{ $document->expire_on_date }}</td>
                                <td>
                                    <span class="badge {{ $document->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $document->status }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if(Auth::guard('admin')->user()->can('update_documents'))
                                            <a href="{{ url('admin/edit_document/' . $document->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif
                                        @if(Auth::guard('admin')->user()->can('delete_documents'))
                                            <a href="{{ url('admin/delete_document/' . $document->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this document?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No documents found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $documents->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
