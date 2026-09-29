@extends('admin.template')

@section('title', 'Edit Wallet Amount | FabKin Admin & Dashboards Template')
@section('title-sub', 'Edit Wallet')
@section('pagetitle', 'Edit Wallet Amount')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
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
                            {!! Form::open([
                                'url' => route('edit_wallet', ['id' => $result->user_id, 'user_type' => $user_type]),
                                'class' => 'row g-5',
                                'id' => 'wallet_form'
                            ]) !!}

                            <div class="col-12">
                                <label class="form-label" for="user_id">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.username') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('user_id', $users_list, old('user_id', $result->user_id), [
                                    'disabled' => true,
                                    'class' => 'form-select',
                                    'id' => 'user_id',
                                    'placeholder' => 'Select...'
                                ]) !!}
                                <input type="hidden" name="prev_user_id" value="{{ $result->user_id }}">
                                <span class="text-danger">{{ $errors->first('user_id') }}</span>
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

                            <div class="col-12">
                                <label class="form-label" for="input_currency_code">
                                    {{ __('messages.admin.manage_wallet_and_promo.form.currency_code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('currency_code', $currency, old('currency_code', $result->currency_code), [
                                    'class' => 'form-select',
                                    'id' => 'input_currency_code',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('currency_code') }}</span>
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
                                <h5 class="card-title mb-4">
                                    {{ __('messages.admin.manage_wallet_and_promo.edit_title') }}
                                </h5>
                                <p class="text-muted mb-4">
                                    {{ __('messages.blade_static.update_the_wallet_transaction_amount_or_currency_for_thi_993192a7') }}
                                </p>

                                <!-- Optional: you can add transaction history link or note field here later -->
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 mt-5">
                                <div class="text-end">
                                    <a href="{{ url(LOGIN_USER_TYPE . '/wallet/' . $user_type) }}" class="btn btn-light-primary">
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
@endsection