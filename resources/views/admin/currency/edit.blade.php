@extends('admin.template')

@section('title-sub', __('messages.admin.manage_currency.edit_currency'))
@section('pagetitle', __('messages.admin.manage_currency.edit_currency'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit_currency/' . $result->id, 'class' => 'row g-5']) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_currency.edit_currency_form') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_currency.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', $result->name, [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => 'Name',
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_code">
                                    {{ __('messages.admin.manage_currency.form.code') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('code', $result->code, [
                                    'class' => 'form-control',
                                    'id' => 'input_code',
                                    'placeholder' => __('messages.admin.manage_currency.form.code'),
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('code') }}</span>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_symbol">
                                    {{ __('messages.admin.manage_currency.form.symbol') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('symbol', $result->symbol, [
                                    'class' => 'form-control',
                                    'id' => 'input_symbol',
                                    'placeholder' => __('messages.admin.manage_currency.form.symbol'),
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('symbol') }}</span>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_rate">
                                    {{ __('messages.admin.manage_currency.form.rate') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('rate', $result->rate, [
                                    'class' => 'form-control',
                                    'id' => 'input_rate',
                                    'placeholder' => __('messages.admin.manage_currency.form.rate'),
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('rate') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_currency.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], $result->status, [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select',
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" name="cancel" value="cancel" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </button>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                    {{ __('messages.admin.submit') }}
                                </button>
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

           
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
    
</main>
@endsection

@section('js')
    <!-- Select js -->
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="assets/js/form/forms-select.init.js"></script>

    <!-- App js -->
    <script type="module" src="assets/js/app.js"></script>
@endsection