@extends('admin.template')

@section('title-sub', 'Onboarding Review')
@section('pagetitle', 'Company Onboarding Review')

@section('main')
@php
    $uploadedByDocumentId = $uploadedDocuments->keyBy('document_id');
    $queueStatus = $company->onboarding_status ?: $company->approval_status ?: 'draft';
@endphp
<div id="layout-wrapper">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start">
                <div>
                    <div class="badge bg-info-subtle text-info mb-2">{{ ucfirst(str_replace('_', ' ', $queueStatus)) }}</div>
                    <h3 class="mb-1">{{ $company->name ?: $company->legal_name }}</h3>
                    <div class="text-muted">{{ $company->registration_reference ?: 'No reference' }}</div>
                </div>
                <a href="{{ url('admin/company-onboarding') }}" class="btn btn-light">Back to queue</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Submitted company details</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Legal name:</strong> {{ $company->legal_name ?: $company->name }}</div>
                        <div class="col-md-6"><strong>Trading name:</strong> {{ $company->trading_name ?: '-' }}</div>
                        <div class="col-md-6"><strong>Registration number:</strong> {{ $company->registration_number ?: '-' }}</div>
                        <div class="col-md-6"><strong>VAT number:</strong> {{ $company->vat_number ?: '-' }}</div>
                        <div class="col-md-6"><strong>Company type:</strong> {{ ucfirst(str_replace('_', ' ', $company->business_type ?: '-')) }}</div>
                        <div class="col-md-6"><strong>Vehicles / drivers:</strong> {{ $company->vehicles_count ?: 0 }} / {{ $company->drivers_count ?: 0 }}</div>
                        <div class="col-md-6"><strong>Contact:</strong> {{ trim(($company->contact_first_name ?: '').' '.($company->contact_last_name ?: '')) ?: '-' }}</div>
                        <div class="col-md-6"><strong>Position:</strong> {{ $company->contact_position ?: '-' }}</div>
                        <div class="col-md-6"><strong>Email:</strong> {{ $company->email ?: '-' }}</div>
                        <div class="col-md-6"><strong>Phone:</strong> +{{ $company->country_code }} {{ $company->mobile_number }}</div>
                        <div class="col-md-6"><strong>Address:</strong> {{ $company->address ?: '-' }}</div>
                        <div class="col-md-6"><strong>City / state:</strong> {{ $company->city ?: '-' }} / {{ $company->state ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Logo and documents</h5></div>
                <div class="card-body">
                    @if($company->profile)
                        <div class="mb-4">
                            <div class="fw-semibold mb-2">Company logo</div>
                            <img src="{{ $company->profile }}" alt="{{ $company->name }}" class="rounded border" style="width:120px;height:120px;object-fit:cover;">
                        </div>
                    @endif

                    <div class="row g-3">
                        @foreach($requiredDocuments as $requiredDocument)
                            @php $uploaded = $uploadedByDocumentId->get($requiredDocument->id); @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-semibold">{{ $requiredDocument->document_name }}</div>
                                    <div class="text-muted small mb-2">{{ $uploaded ? 'Uploaded' : 'Missing' }}</div>
                                    @if($uploaded)
                                        <div class="small mb-2">Status: {{ $uploaded->status == 1 ? 'Approved' : ($uploaded->status == 2 ? 'Rejected' : 'Pending') }}</div>
                                        <a href="{{ $uploaded->document }}" target="_blank" class="btn btn-sm btn-outline-primary">Open document</a>
                                    @else
                                        <div class="text-muted small">No file submitted yet.</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Verification status</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom"><span>Phone</span><strong>{{ $company->phone_verified_at ? 'Verified' : 'Pending' }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span>Email</span><strong>{{ $company->email_verified_at ? 'Verified' : 'Pending' }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span>Submitted</span><strong>{{ $company->submitted_for_approval_at ? 'Yes' : 'No' }}</strong></div>
                    <div class="d-flex justify-content-between py-2"><span>Drivers linked</span><strong>{{ $driversCount }}</strong></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Admin actions</h5></div>
                <div class="card-body">
                    {!! Form::open(['url' => 'admin/company-onboarding/'.$company->id.'/approve', 'class' => 'mb-3']) !!}
                        <div class="mb-3">
                            <label class="form-label">Internal review notes</label>
                            <textarea class="form-control" name="admin_notes" rows="3">{{ old('admin_notes', $company->admin_notes) }}</textarea>
                        </div>
                        <button class="btn btn-success w-100">Approve company</button>
                    {!! Form::close() !!}

                    {!! Form::open(['url' => 'admin/company-onboarding/'.$company->id.'/reject', 'class' => 'mb-3']) !!}
                        <div class="mb-3">
                            <label class="form-label">Rejection reason</label>
                            <textarea class="form-control" name="rejection_reason" rows="3">{{ old('rejection_reason', $company->rejection_reason) }}</textarea>
                        </div>
                        <button class="btn btn-warning w-100">Reject with reason</button>
                    {!! Form::close() !!}

                    {!! Form::open(['url' => 'admin/company-onboarding/'.$company->id.'/request-resubmission', 'class' => 'mb-3']) !!}
                        <div class="mb-3">
                            <label class="form-label">Resubmission note</label>
                            <textarea class="form-control" name="rejection_reason" rows="3">{{ old('rejection_reason') }}</textarea>
                        </div>
                        <button class="btn btn-primary w-100">Request resubmission</button>
                    {!! Form::close() !!}

                    {!! Form::open(['url' => 'admin/company-onboarding/'.$company->id.'/suspend']) !!}
                        <div class="mb-3">
                            <label class="form-label">Suspension reason</label>
                            <textarea class="form-control" name="rejection_reason" rows="3">{{ old('rejection_reason') }}</textarea>
                        </div>
                        <button class="btn btn-danger w-100">Suspend account</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
