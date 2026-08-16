@extends('admin.template')

@section('title', 'Referral Details | FabKin Admin & Dashboards Template')
@section('title-sub', 'Referral Details')
@section('pagetitle', 'Referral Details')

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">{{ __('messages.admin.referrals.view_referral_details') }}</h5>
                            <a href="{{ url(LOGIN_USER_TYPE . '/referrals/' . $user_type) }}" 
                               class="btn btn-light-primary">
                                {{ __('messages.admin.back') }}
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="payout_methods">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.admin.referrals.table.referral_name') }}</th>
                                        <th>{{ __('messages.admin.referrals.table.trips') }}</th>
                                        <th>{{ __('messages.admin.referrals.table.remaining_trips') }}</th>
                                        <th>{{ __('messages.admin.referrals.table.remaining_days') }}</th>
                                        <th>{{ __('messages.admin.referrals.table.amount') }}</th>
                                        <th>{{ __('messages.admin.referrals.table.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($referral_details as $referral)
                                        <tr>
                                            <td>{{ $referral->referral_user->full_name ?? '—' }}</td>
                                            <td>{{ $referral->trips ?? 0 }}</td>
                                            <td>{{ $referral->remaining_trips ?? 0 }}</td>
                                            <td>{{ $referral->remaining_days ?? '—' }}</td>
                                            <td>
                                                {{ html_string($referral->currency_symbol ?? '') }} 
                                                {{ number_format($referral->amount ?? 0, 2) }}
                                            </td>
                                            <td>
                                                @php
                                                    $paymentBadge = $referral->payment_status == 'Paid'
                                                        ? 'bg-success'
                                                        : ($referral->payment_status == 'Pending' ? 'bg-warning' : 'bg-danger');
                                                @endphp
                                                <span class="badge {{ $paymentBadge }}">
                                                    {{ $referral->payment_status ?? 'Unknown' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                {{ __('messages.admin.no_records_found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Optional: pagination if you add it later -->
                        <!-- <div class="d-flex justify-content-center mt-4">
                            {{ $referral_details->links('pagination::bootstrap-5') }}
                        </div> -->

                    </div>
                </div>
            </div>
        </div><!-- End row -->

    </div><!-- End layout-wrapper -->
    </main>
@endsection

@section('js')
    <!-- App js -->
    <script type="module" src="assets/js/app.js"></script>


@endsection
