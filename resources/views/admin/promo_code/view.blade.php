@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 flex-grow-1">{{ __('messages.admin.manage_wallet_and_promo.promo_code') }}</h5>
            @if(optional(Auth::guard('admin')->user())->can('create_promo_code'))
                <div class="ms-auto">
                    <a class="btn btn-success" href="{{ url('admin/add_promo_code') }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_wallet_and_promo.add_promo_code') }}</a>
                </div>
            @endif
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/promo_code') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Code, currency or ID">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" {{ $statusFilter === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $statusFilter === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/promo_code') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promoCodes as $promo)
                            <tr>
                                <td>#{{ $promo->id }}</td>
                                <td>{{ $promo->code }}</td>
                                <td>{{ $promo->amount }}</td>
                                <td>{{ $promo->currency_code }}</td>
                                <td>{{ \Carbon\Carbon::parse($promo->expire_date)->format('d M Y') }}</td>
                                <td><span class="badge {{ $promo->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $promo->status }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('admin/edit_promo_code/'.$promo->id) }}" class="btn btn-sm btn-outline-success"><i class="ri-pencil-line"></i></a>
                                        <a href="{{ url('admin/delete_promo_code/'.$promo->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this promo code?')"><i class="ri-delete-bin-line"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No promo codes found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($promoCodes->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $promoCodes->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
