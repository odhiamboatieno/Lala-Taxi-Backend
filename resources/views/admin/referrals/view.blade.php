@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ $main_title ?? __('messages.admin.referrals.title') }}</h4>
                <div class="text-muted">Track referral earnings and pending balances for {{ strtolower($user_type) }}s.</div>
            </div>
            <div class="text-muted">Total referrals : <strong>{{ $referrals->total() }}</strong></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ $base_url.'/'.strtolower($user_type) }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="User or referral name">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ $base_url.'/'.strtolower($user_type) }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>{{ $user_type }} Name</th>
                            <th>Referral Name</th>
                            <th>Currency</th>
                            <th>Earned Amount</th>
                            <th>Pending Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrals as $referral)
                            @php
                                $user = $referral->user;
                                $earnedAmount = \App\Models\ReferralUser::where('user_id', $referral->user_id)->where('payment_status','Completed')->where('amount','>',0)->sum('amount');
                                $pendingAmount = \App\Models\ReferralUser::where('user_id', $referral->user_id)->where('payment_status','Pending')->where('pending_amount','>',0)->sum('pending_amount');
                            @endphp
                            <tr>
                                <td>#{{ optional($user)->id }}</td>
                                <td>{{ optional($user)->first_name }} {{ optional($user)->last_name }}</td>
                                <td>{{ optional($referral->referral_user)->first_name }} {{ optional($referral->referral_user)->last_name }}</td>
                                <td>{{ $referral->currency_code }}</td>
                                <td>{{ $earnedAmount }}</td>
                                <td>{{ $pendingAmount }}</td>
                                <td class="text-end">
                                    <a href="{{ url('admin/referrals/'.$referral->user_id) }}" class="btn btn-sm btn-outline-primary"><i class="ri-eye-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No referral records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($referrals->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $referrals->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
