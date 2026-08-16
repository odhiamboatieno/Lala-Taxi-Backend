@extends('admin.template')

@section('title-sub', __('messages.admin.manage_language.edit_language'))
@section('pagetitle', __('messages.admin.manage_language.edit_language'))

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
                            {!! Form::open(['url' => 'admin/edit_language/'.$result->id, 'class' => 'row g-5']) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_language.edit_language_form') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <div class="col-4">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_language.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', old('name',$result->name), [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => __('messages.admin.manage_language.form.name')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-4">
                                <label class="form-label" for="input_value">
                                    {{ __('messages.admin.manage_language.form.value') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('value', old('value',$result->value), [
                                    'class' => 'form-control',
                                    'id' => 'input_value',
                                    'placeholder' => __('messages.admin.manage_language.form.value')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('value') }}</span>
                            </div>

                            <div class="col-4">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_language.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status',$result->status), [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
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