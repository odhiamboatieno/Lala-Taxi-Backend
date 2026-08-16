@extends('admin.template')

@section('title-sub', 'Networking')
@section('pagetitle', 'Networking')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Connect Partner Fleet</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.self.networking.partners.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Partner Company</label>
                            <select name="partner_company_id" class="form-control">
                                <option value="">Select company</option>
                                @foreach($availableCompanies as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <button class="btn btn-primary">Add Partner</button>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header"><h4 class="mb-0">Partner Fleets</h4></div>
                <div class="card-body">
                    @forelse($partners as $partner)
                        <div class="border rounded p-3 mb-3">
                            <div class="fw-semibold">{{ optional($partner->partnerCompany)->name }}</div>
                            <div class="text-muted">{{ $partner->notes ?: 'No notes' }}</div>
                        </div>
                    @empty
                        <div class="text-muted">No partner fleets connected yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Share Overflow Work</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.self.networking.share') }}" class="row g-3 mb-4">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Request</label>
                            <select name="request_id" class="form-control">
                                <option value="">Select trip request</option>
                                @foreach($pendingRequests as $requestItem)
                                    <option value="{{ $requestItem->id }}">#{{ $requestItem->id }} - {{ $requestItem->pickup_location ?: 'No pickup set' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Send To</label>
                            <select name="target_company_id" class="form-control">
                                <option value="">Select partner</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->partner_company_id }}">{{ optional($partner->partnerCompany)->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Note</label>
                            <input type="text" name="share_note" class="form-control" placeholder="Optional handover note">
                        </div>
                        <div class="col-12"><button class="btn btn-primary">Share Request</button></div>
                    </form>

                    <h5 class="mb-3">Incoming Shares</h5>
                    @forelse($incomingShares as $share)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold">Request #{{ $share->request_id ?: 'N/A' }}</div>
                                    <div class="text-muted">Status: {{ ucfirst($share->status) }}</div>
                                    <div class="text-muted">{{ $share->share_note ?: 'No share note' }}</div>
                                </div>
                                @if($share->status === 'pending')
                                    <form method="POST" action="{{ route('saasdispatch.self.networking.respond', $share) }}" class="d-flex gap-2 flex-wrap">
                                        @csrf
                                        <input type="hidden" name="status" value="accepted">
                                        <input type="text" name="response_note" class="form-control" placeholder="Response note">
                                        <button class="btn btn-success btn-sm">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('saasdispatch.self.networking.respond', $share) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-outline-danger btn-sm">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No incoming shared work.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
