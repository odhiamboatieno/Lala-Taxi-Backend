@extends('admin.template')

@section('title-sub', 'Company Fee Settings')
@section('pagetitle', 'Company Fee Settings')

@section('css')
<link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
<div id="layout-wrapper">
    @include('saasdispatch::partials.company_tabs')
    <div class="row">
        <div class="col-xl-6">
            <div class="card card-h-100">
                <div class="card-body">
                    {!! Form::open(['url' => $isAdmin ? route('saasdispatch.companies.fee-settings.store', $company) : route('saasdispatch.self.fee-settings.store'), 'class' => 'row g-5']) !!}
                    <div class="col-12">
                        <p class="text-danger mb-0">(*) Fields are mandatory</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Rider Service Fee</label>
                        <div class="input-group"><input type="text" name="access_fee" value="{{ old('access_fee', $feeSettings['access_fee']) }}" class="form-control"><span class="input-group-text">%</span></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Driver Peak Fare</label>
                        <div class="input-group"><input type="text" name="driver_peak_fare" value="{{ old('driver_peak_fare', $feeSettings['driver_peak_fare']) }}" class="form-control"><span class="input-group-text">%</span></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Additional Rider Fare</label>
                        <div class="input-group"><input type="text" name="additional_rider_fare" value="{{ old('additional_rider_fare', $feeSettings['additional_rider_fare']) }}" class="form-control"><span class="input-group-text">%</span></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Driver Service Fee</label>
                        <div class="input-group"><input type="text" name="driver_service_fee" value="{{ old('driver_service_fee', $feeSettings['driver_access_fee']) }}" class="form-control"><span class="input-group-text">%</span></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Apply Trip Additional Fee</label>
                        {!! Form::select('additional_fee', ['Yes' => 'Yes', 'No' => 'No'], old('additional_fee', $feeSettings['additional_fee']), ['class' => 'form-select']) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Company Fee Settings</h5>
                    <p class="text-muted mb-4">Adjust the company-specific fees, peak fares, pool settings, and additional fee behavior.</p>
                    <div class="alert alert-info mt-3">
                        <strong>Note:</strong> Rider service fee and driver service fee are applied on the company trips handled through SaaS dispatch.
                    </div>
                    <div class="text-end mt-5">
                        <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
