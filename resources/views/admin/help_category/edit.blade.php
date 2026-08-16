@extends('admin.template')

@section('title-sub', __('messages.admin.manage_help.edit_help_category'))
@section('pagetitle', __('messages.admin.manage_help.edit_help_category'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="category_language" ng-cloak>

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit_help_category/'.$result->id, 'class' => 'row g-5']) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_help.edit_help_category_form') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Language -->
                            <div class="col-12">
                                <label class="form-label" for="input_language">
                                    {{ __('messages.admin.manage_help.form.language') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('language_code', $language, 'en', [
                                    'class' => 'form-select',
                                    'id' => 'input_language',
                                    'placeholder' => 'Select',
                                    'disabled' => 'disabled'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('language_code') }}</span>
                            </div>

                            <!-- Name -->
                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_help.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', $result->name, [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => 'Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label" for="input_description">
                                    {{ __('messages.admin.manage_help.form.description') }}
                                </label>
                                {!! Form::textarea('description', $result->description, [
                                    'class' => 'form-control',
                                    'id' => 'input_description',
                                    'placeholder' => 'Description',
                                    'rows' => 3
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            </div>

                            <!-- Status -->
                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_help.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], $result->status, [
                                    'class' => 'form-select',
                                    'id' => 'input_status',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>



                            <!-- Buttons -->
                            <div class="col-12 text-end">
                                <button type="submit" name="cancel" value="cancel" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </button>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                    {{ __('messages.admin.submit') }}
                                </button>
                            </div>

                           
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                                                    <!-- Translations Section -->
                            <div class="col-12" ng-init="translations = {{json_encode(old('translations') ?: $result->translate)}}; removed_translations =  []; errors = {{json_encode($errors->getMessages())}};">
                                <div class="card border-info mt-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">{{ __('messages.admin.manage_help.translations') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" name="removed_translations" ng-value="removed_translations.toString()">
                                        
                                        <div class="translation-item mb-4 pb-4 border-bottom" ng-repeat="translation in translations" ng-if="translations.length > 0">
                                            <input type="hidden" name="translations[@{{$index}}][id]" value="@{{translation.id}}">
                                            
                                            <!-- Language Selection -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-10">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_help.form.language') }} <em class="text-danger">*</em>
                                                    </label>
                                                    <select name="translations[@{{$index}}][locale]" class="form-select form-select-sm" ng-model="translation.locale">
                                                        <option value="" ng-if="translation.locale == ''">{{ __('messages.blade_static.select_language_17d647c6') }}</option>
                                                        @foreach($languages as $key => $value)
                                                            <option value="{{$key}}" ng-if="(('{{$key}}' | checkKeyValueUsedInStack : 'locale': translations) || '{{$key}}' == translation.locale) && '{{$key}}' != 'en'">{{$value}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-danger small">@{{ errors['translations.'+$index+'.locale'][0] }}</span>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm" ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Translated Name -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-12">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_help.form.name') }} <em class="text-danger">*</em>
                                                    </label>
                                                    {!! Form::text('translations[@{{$index}}][name]', '@{{translation.name}}', [
                                                        'class' => 'form-control form-control-sm',
                                                        'id' => 'input_name_@{{$index}}',
                                                        'placeholder' => __('messages.admin.manage_help.form.name')
                                                    ]) !!}
                                                    <span class="text-danger small">@{{ errors['translations.'+$index+'.name'][0] }}</span>
                                                </div>
                                            </div>

                                            <!-- Translated Description -->
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_help.form.description') }}
                                                    </label>
                                                    <textarea class="form-control form-control-sm" id="description_@{{$index}}" name="translations[@{{$index}}][description]" rows="3">@{{translation.description}}</textarea>
                                                    <span class="text-danger small">@{{ errors['translations.'+$index+'.description'][0] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add Translation Button -->
                                        <div class="text-center" ng-show="translations.length < {{count($languages) - 1}}">
                                            <button type="button" class="btn btn-info btn-sm" ng-click="translations.push({locale:''});">
                                                <i class="fas fa-plus"></i> {{ __('messages.admin.manage_help.add_translation') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div><!-- End Right Col -->
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