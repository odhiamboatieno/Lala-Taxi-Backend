@extends('admin.template')

@section('title', 'Edit Fees & Commissions | FabKin Admin & Dashboards Template')
@section('title-sub', 'Fees Settings')
@section('pagetitle', 'Edit Fees')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">

    <style>
        .input-group-text {
            background-color: var(--color-as-f8f9fa);
            border-radius: 0;
            font-weight: 500;
        }
        .percentage-input .input-group-text {
            min-width: 40px;
        }
    </style>
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/fees', 'class' => 'row g-5', 'id' => 'fees_form']) !!}

                            <!-- Mandatory note -->
                            <div class="col-12">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Rider Service Fee -->
                            <div class="col-12">
                                <label class="form-label" for="input_service_fee">
                                    {{ __('messages.admin.mange_fees.form.rider_service_fee') }}
                                </label>
                                <div class="input-group percentage-input">
                                    {!! Form::text('access_fee', old('access_fee', fees('access_fee')), [
                                        'class' => 'form-control',
                                        'id' => 'input_service_fee',
                                        'placeholder' => 'Rider Service Fee'
                                    ]) !!}
                                    <span class="input-group-text">%</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('access_fee') }}</span>
                            </div>

                            <!-- Driver Peak Fare -->
                            <div class="col-12">
                                <label class="form-label" for="input_driver_peak_fare">
                                    {{ __('messages.admin.mange_fees.form.driver_peak_fare') }}
                                </label>
                                <div class="input-group percentage-input">
                                    {!! Form::text('driver_peak_fare', old('driver_peak_fare', fees('driver_peak_fare')), [
                                        'class' => 'form-control',
                                        'id' => 'input_driver_peak_fare',
                                        'placeholder' => 'Driver Peak Fare'
                                    ]) !!}
                                    <span class="input-group-text">%</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('driver_peak_fare') }}</span>
                            </div>

                            <!-- Additional Rider Fare (Pool) -->
                            <div class="col-12">
                                <label class="form-label" for="input_additional_rider_fare">
                                    {{ __('messages.admin.mange_fees.form.additional_rider_fare') }}
                                    <small class="text-muted ms-2">({{ __('messages.admin.mange_fees.form.is_pool_small') }})</small>
                                </label>
                                <div class="input-group percentage-input">
                                    {!! Form::text('additional_rider_fare', old('additional_rider_fare', fees('additional_rider_fare')), [
                                        'class' => 'form-control',
                                        'id' => 'input_additional_rider_fare',
                                        'placeholder' => 'Additional Rider Fare'
                                    ]) !!}
                                    <span class="input-group-text">%</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('additional_rider_fare') }}</span>
                            </div>

                            <!-- Driver Service Fee -->
                            <div class="col-12">
                                <label class="form-label" for="input_driver_service_fee">
                                    {{ __('messages.admin.mange_fees.form.driver_service_fee') }}
                                </label>
                                <div class="input-group percentage-input">
                                    {!! Form::text('driver_service_fee', old('driver_service_fee', fees('driver_access_fee')), [
                                        'class' => 'form-control',
                                        'id' => 'input_driver_service_fee',
                                        'placeholder' => 'Driver Service Fee'
                                    ]) !!}
                                    <span class="input-group-text">%</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('driver_service_fee') }}</span>
                            </div>

                            <!-- Apply Trip Additional Fee -->
                            <div class="col-12">
                                <label class="form-label" for="input_additional_fee">
                                    {{ __('messages.admin.mange_fees.form.apply_trip_additional_fee') }}
                                </label>
                                {!! Form::select('additional_fee', ['Yes' => 'Yes', 'No' => 'No'], old('additional_fee', fees('additional_fee')), [
                                    'class' => 'form-select',
                                    'id' => 'input_additional_fee'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('additional_fee') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right column: Info & Actions -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-5">
                            <div class="col-12">
                                <h5 class="card-title mb-4">
                                    {{ __('messages.admin.mange_fees.fees_form') }}
                                </h5>
                                <p class="text-muted mb-4">
                                    {{ __('messages.blade_static.adjust_the_platform_fees_peak_fares_pool_settings_and_ad_3e48702d') }}
                                </p>

                                <div class="alert alert-info mt-3">
                                    <strong>{{ __('messages.blade_static.note_83423c19') }}</strong> {{ __('messages.blade_static.rider_service_fee_and_driver_service_fee_are_applied_on_2dbcf81f') }}
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 mt-5">
                                <div class="text-end">
                                    <button type="reset" class="btn btn-outline-secondary me-2">
                                        {{ __('messages.admin.reset') }}
                                    </button>
                                    <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                        {{ __('messages.admin.submit') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End right col -->

            {!! Form::close() !!}

        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
</main>    
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- Datepicker Js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection