@extends('admin.template')

@section('title', 'Edit Promo Code | FabKin Admin & Dashboards Template')
@section('title-sub', 'Edit Promo Code')
@section('pagetitle', 'Edit Promo Code')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit_promo_code/' . $result->id, 'class' => 'row g-5', 'id' => 'promo_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_promo_code">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.promo_code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('code', old('code', $result->code), [
                                    'class' => 'form-control',
                                    'id' => 'input_promo_code',
                                    'placeholder' => __('messages.admin.manage_wallet_and_promo.form.promo_code')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('code') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_amount">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.amount') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('amount', old('amount', $result->original_amount), [
                                    'class' => 'form-control',
                                    'id' => 'input_amount',
                                    'placeholder' => __('messages.admin.manage_wallet_and_promo.form.amount')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('amount') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_currency_code">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.currency_code') }}
                                </label>
                                {!! Form::select('currency_code', $currency, old('currency_code', $result->currency_code), [
                                    'class' => 'form-select',
                                    'id' => 'input_currency_code',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('currency_code') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_expired_at">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.expire_date') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('expire_date', old('expire_date', $result->expire_date_mdy), [
                                    'class' => 'form-control air-datepicker',
                                    'id' => 'input_expired_at',
                                    'placeholder' => __('messages.admin.manage_wallet_and_promo.form.expire_date'),
                                    'autocomplete' => 'off'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('expire_date') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status', $result->status), [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <!-- Mandatory note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
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
                                <h5 class="card-title mb-4">{{ __('messages.admin.manage_wallet_and_promo.edit_promo_code_form') }}</h5>
                                <p class="text-muted mb-4">
                                    {{ __('messages.blade_static.modify_the_promotional_code_settings_changing_the_code_o_870205b4') }}
                                </p>
                                <!-- Optional: add more info here (e.g. current usage count, remaining uses, etc.) -->
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 mt-5">
                                <div class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE . '/promo_code') }}" class="btn btn-light-primary">
                                        {{ __('messages.admin.cancel') }}
                                    </a>
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

    <!-- Promo Datepicker Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const expirePicker = document.getElementById('input_expired_at');
            if (expirePicker) {
                new AirDatepicker(expirePicker, {
                    autoClose: true,
                    minDate: new Date(),           // today or later
                    dateFormat: 'yyyy-mm-dd'
                    position: 'bottom left'
                });
            }
        });
    </script>
@endsection