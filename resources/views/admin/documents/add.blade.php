@extends('admin.template')

@section('title', 'Document | FabKin Admin & Dashboards Template')
@section('title-sub', 'Document')
@section('pagetitle', 'Document')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">
        <?php $page = isset($result) ? __('messages.admin.edit') : __('messages.admin.add'); ?>

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/add_document', 'class' => 'row g-5']) !!}

                            <input type="hidden" name="document_id" value="{{ $result->id ?? '' }}">

                            <div class="col-12">
                                <label class="form-label" for="input_language">
                                    {{ __('messages.admin.manage_documents_page.form.language') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('language', $language, 'en', [
                                    'class' => 'form-select',
                                    'id' => 'input_language',
                                    'disabled' => 'disabled'
                                ]) !!}
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_type">
                                    {{ __('messages.admin.manage_documents_page.form.document_for') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('type', [
                                    'Driver' => 'Driver',
                                    'Vehicle' => 'Vehicle',
                                    'Company' => 'Company'
                                ], $result->type ?? old('type'), [
                                    'class' => 'form-select',
                                    'id' => 'input_type'
                                ]) !!}
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_document_name">
                                    {{ __('messages.admin.manage_documents_page.form.document_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('document_name', old('document_name', $result->document_name ?? ''), [
                                    'class' => 'form-control',
                                    'id' => 'input_document_name',
                                    'placeholder' => 'Document Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('document_name') }}</span>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_country">
                                    {{ __('messages.admin.manage_documents_page.form.country') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('country', $country, old('country', $result->country_code ?? ''), [
                                    'class' => 'form-select',
                                    'id' => 'input_country'
                                ]) !!}
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="input_expire">
                                    {{ __('messages.admin.manage_documents_page.form.expire_on_date') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('expire_on_date', ['Yes' => 'Yes', 'No' => 'No'], old('expire_on_date', $result->expire_on_date ?? ''), [
                                    'class' => 'form-select',
                                    'id' => 'input_expire'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('expire_on_date') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_documents_page.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status', $result->status ?? ''), [
                                    'class' => 'form-select',
                                    'id' => 'input_status'
                                ]) !!}
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div><!-- End col -->

            <!-- Right Info Panel -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                       <!-- Translations Panel -->
                            <div class="col-12" 
                                 ng-init="translations = {{ json_encode(old('translations') ?: ($result->translate ?? [])) }}; 
                                          removed_translations = []; 
                                          errors = {{ json_encode($errors->getMessages()) }};" 
                                 ng-cloak>

                                <div class="card border mt-4">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">{{ __('messages.blade_static.translations_8ad8302d') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" name="removed_translations" ng-value="removed_translations.toString()">

                                        <div class="row g-4" ng-repeat="translation in translations track by $index">
                                            <input type="hidden" name="translations[@{{$index}}][id]" value="@{{ translation.id }}">

                                            <div class="col-md-11">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            {{ __('messages.blade_static.language_89b86ab0') }} <em class="text-danger">*</em>
                                                        </label>
                                                        <select name="translations[@{{$index}}][locale]" 
                                                                class="form-select inactive_translate" 
                                                                id="input_language_@{{$index}}" 
                                                                ng-model="translation.locale">
                                                            <option value="" ng-if="translation.locale == ''">{{ __('messages.blade_static.select_language_17d647c6') }}</option>
                                                            @foreach($languages as $key => $value)
                                                                <option value="{{ $key }}" 
                                                                        ng-if="(('{{ $key }}' | checkKeyValueUsedInStack : 'locale': translations) || '{{ $key }}' == translation.locale) && '{{ $key }}' != 'en'">
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span class="text-danger">@{{ errors['translations.'+$index+'.locale'][0] }}</span>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            {{ __('messages.blade_static.document_name_34767b31') }} <em class="text-danger">*</em>
                                                        </label>
                                                        {!! Form::text('translations[@{{$index}}][document_name]', '@{{ translation.document_name }}', [
                                                            'class' => 'form-control',
                                                            'id' => 'input_name_@{{$index}}',
                                                            'placeholder' => 'Name'
                                                        ]) !!}
                                                        <span class="text-danger">@{{ errors['translations.'+$index+'.document_name'][0] }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-1 d-flex align-items-center">
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>

                                            <div class="col-12">
                                                <hr ng-if="$index + 1 < translations.length">
                                            </div>
                                        </div>

                                        <!-- Add Translation Button -->
                                        <div class="row mt-3" ng-show="translations.length < {{ count($languages) - 1 }}">
                                            <div class="col-12 text-center">
                                                <button type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        ng-click="translations.push({locale:''});">
                                                    <i class="fa fa-plus"></i> {{ __('messages.blade_static.add_translation_74acd500') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mandatory Note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 mt-4">
                                <div class="text-end">
                                    <button type="submit" name="cancel" value="cancel" class="btn btn-light-primary">
                                        {{ __('messages.admin.cancel') }}
                                    </button>
                                    <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                        {{ __('messages.admin.submit') }}
                                    </button>
                                </div>
                            </div>

                            {!! Form::close() !!}
                    </div>
                </div>
            </div><!-- End col -->
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