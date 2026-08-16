@extends('admin.template')

@section('title', 'Company Driver Fees')
@section('title-sub', 'Company Driver Fees')
@section('pagetitle', 'Company Driver Fees')

@section('main')
    <div id="layout-wrapper">
        <div class="row">
            <div class="col-xl-7">
                <div class="card card-h-100">
                    <div class="card-body">
                        {!! Form::open(['url' => LOGIN_USER_TYPE.'/company-driver-fees', 'class' => 'row g-4']) !!}
                            <div class="col-12">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_fee_type">Charge Model</label>
                                {!! Form::select('fee_type', [
                                    'percentage' => 'Percentage Per Trip',
                                    'weekly_subscription' => 'Weekly Subscription',
                                    'monthly_subscription' => 'Monthly Subscription',
                                ], old('fee_type', $setting->fee_type ?: 'percentage'), ['class' => 'form-select', 'id' => 'input_fee_type']) !!}
                                <span class="text-danger">{{ $errors->first('fee_type') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_status">Status</label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status', $setting->status ?: 'Inactive'), ['class' => 'form-select', 'id' => 'input_status']) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_amount">Amount</label>
                                {!! Form::text('amount', old('amount', $setting->amount ?: '0.00'), ['class' => 'form-control', 'id' => 'input_amount', 'placeholder' => '0.00']) !!}
                                <span class="text-danger">{{ $errors->first('amount') }}</span>
                                <small class="text-muted">Use a percentage value for per-trip charging, or the actual subscription amount for weekly/monthly charging.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_title">Label</label>
                                {!! Form::text('title', old('title', $setting->title), ['class' => 'form-control', 'id' => 'input_title', 'placeholder' => 'Optional custom label']) !!}
                                <span class="text-danger">{{ $errors->first('title') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_notes">Notes</label>
                                {!! Form::textarea('notes', old('notes', $setting->notes), ['class' => 'form-control', 'rows' => 4, 'id' => 'input_notes', 'placeholder' => 'Optional internal note']) !!}
                                <span class="text-danger">{{ $errors->first('notes') }}</span>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">How It Works</h5>
                        <p class="text-muted mb-3">These deductions are applied after the normal trip fare is calculated from the active fare rules.</p>
                        <ul class="mb-0 ps-3 text-muted">
                            <li class="mb-2">Percentage Per Trip deducts a percentage from each completed trip for your drivers.</li>
                            <li class="mb-2">Weekly Subscription deducts the configured amount once on the first trip completed each week.</li>
                            <li class="mb-2">Monthly Subscription deducts the configured amount once on the first trip completed each month.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
