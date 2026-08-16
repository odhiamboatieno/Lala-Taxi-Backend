@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 flex-grow-1">{{ $main_title ?? __('messages.admin.manage_wallet_and_promo.wallet') }}</h5>
            <div class="ms-auto">
                <a class="btn btn-success" href="{{ route('add_wallet', ['user_type' => $user_type]) }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_wallet_and_promo.add_wallet') }}</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ $base_url }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="User ID, name, email or currency">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ $base_url }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>User ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Wallet Amount</th>
                            <th>Currency</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $wallet)
                            <tr>
                                <td>#{{ $wallet->id }}</td>
                                <td>{{ $wallet->first_name }}</td>
                                <td>{{ $wallet->last_name }}</td>
                                <td>{{ $wallet->amount ?: '0' }}</td>
                                <td>{{ $wallet->currency_code }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('edit_wallet',['id' => $wallet->id, 'user_type' => $user_type]) }}" class="btn btn-sm btn-outline-success"><i class="ri-pencil-line"></i></a>
                                        <a href="{{ route('delete_wallet',['id' => $wallet->id, 'user_type' => $user_type]) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this wallet?')"><i class="ri-delete-bin-line"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No wallet records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($wallets->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $wallets->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
