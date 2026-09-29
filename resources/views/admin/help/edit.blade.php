@extends('admin.template')

@section('title-sub', __('messages.admin.manage_help.edit_help'))
@section('pagetitle', __('messages.admin.manage_help.edit_help'))

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Text Editor css -->
    <link rel="stylesheet" href="{{ asset('admin_assets/plugins/editor/editor.css') }}">
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="help" ng-cloak>

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit_help/'.$result->id, 'class' => 'row g-5']) !!}

                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.manage_help.edit_help_form') }}</h6>
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                            <!-- Category -->
                            <div class="col-12">
                                <label class="form-label" for="input_category_id">
                                    {{ __('messages.admin.manage_help.form.category') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('category_id', $category->pluck('name', 'id'), $result->category_id, [
                                    'class' => 'form-select',
                                    'id' => 'input_category_id',
                                    'placeholder' => 'Select',
                                    'ng-change' => 'change_category(category_id)',
                                    'ng-model' => 'category_id',
                                    'ng-init' => 'category_id = '.$result->category_id
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('category_id') }}</span>
                            </div>

                            <!-- Subcategory -->
                            <div class="col-12">
                                <label class="form-label" for="input_subcategory_id">
                                    {{ __('messages.admin.manage_help.form.subcategory') }}
                                </label>
                                <select class="form-select" id="input_subcategory_id" name="subcategory_id" ng-model="subcategory_id">
                                    <option value="">{{ __('messages.blade_static.select_85982229') }}</option>
                                    <option ng-repeat="item in subcategory" value="@{{ item.id }}">@{{ item.name }}</option>
                                </select>
                                <input type="hidden" id="hidden_subcategory_id" value="{{ $result->subcategory_id }}">
                                <span class="text-danger">{{ $errors->first('subcategory_id') }}</span>
                            </div>

                            <!-- Question -->
                            <div class="col-12">
                                <label class="form-label" for="input_question">
                                    {{ __('messages.admin.manage_help.form.question') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('question', $result->question, [
                                    'class' => 'form-control',
                                    'id' => 'input_question',
                                    'placeholder' => __('messages.admin.manage_help.form.question')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('question') }}</span>
                            </div>

                            <!-- Answer -->
                            <div class="col-12">
                                <label class="form-label" for="txtEditor">
                                    {{ __('messages.admin.manage_help.form.answer') }} <em class="text-danger">*</em>
                                </label>
                                <textarea id="txtEditor" name="txtEditor" class="form-control"></textarea>
                                {!! Form::textarea('answer', $result->answer, ['id' => 'answer', 'hidden' => 'true']) !!}
                                <span class="text-danger">{{ $errors->first('answer') }}</span>
                            </div>

                            <!-- Suggested -->
                            <div class="col-12">
                                <label class="form-label">
                                    {{ __('messages.admin.manage_help.form.suggested') }}
                                </label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        {!! Form::radio('suggested', 'yes', ($result->suggested == 'yes') ? true : false, ['class' => 'form-check-input', 'id' => 'suggested_yes']) !!}
                                        <label class="form-check-label" for="suggested_yes">{{ __('messages.blade_static.yes_5397e058') }}</label>
                                    </div>
                                    <div class="form-check">
                                        {!! Form::radio('suggested', 'no', ($result->suggested == 'no') ? true : false, ['class' => 'form-check-input', 'id' => 'suggested_no']) !!}
                                        <label class="form-check-label" for="suggested_no">{{ __('messages.blade_static.no_816c52fd') }}</label>
                                    </div>
                                </div>
                                <span class="text-danger">{{ $errors->first('suggested') }}</span>
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
                            <div class="col-12" ng-init="translations = {{json_encode(old('translations') ?: $result->translations)}}; removed_translations =  []; errors = {{json_encode($errors->getMessages())}}; result_translations = {{json_encode($result->translations)}}; languages = {{json_encode($languages)}}">
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
                                                        <option ng-if="!languages.hasOwnProperty(translation.locale) && translation.locale != '';" value="@{{translation.locale}}">@{{translation.language.name}}</option>
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

                                            <!-- Translated Question -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-12">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_help.form.question') }} <em class="text-danger">*</em>
                                                    </label>
                                                    {!! Form::text('translations[@{{$index}}][name]', '@{{translation.name}}', [
                                                        'class' => 'form-control form-control-sm',
                                                        'id' => 'input_name_@{{$index}}',
                                                        'placeholder' => 'Translated Question'
                                                    ]) !!}
                                                    <span class="text-danger small">@{{ errors['translations.'+$index+'.name'][0] }}</span>
                                                </div>
                                            </div>

                                            <!-- Translated Answer -->
                                            <div class="row g-3" ng-init="multiple_editors($index)">
                                                <div class="col-12">
                                                    <label class="form-label small">
                                                        {{ __('messages.admin.manage_help.form.answer') }} <em class="text-danger">*</em>
                                                    </label>
                                                    <textarea class="editors form-control" id="editor_@{{$index}}" name="translations[@{{$index}}][txtEditor]" data-index="@{{$index}}"></textarea>
                                                    <textarea class="contents" id="content_@{{$index}}" name="translations[@{{$index}}][description]" hidden="true">@{{translation.description}}</textarea>
                                                    <span class="text-danger small">@{{ errors['translations.'+$index+'.description'][0] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add Translation Button -->
                                        <div class="text-center" ng-show="(translations | checkActiveTranslation: languages).length < {{count($languages) - 1}}">
                                            <button type="button" class="btn btn-info btn-sm" ng-click="translations.push({locale:''});">
                                                <i class="fas fa-plus"></i> {{ __('messages.admin.manage_help.add_translation') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    </div>
                </div>
                 {!! Form::close() !!}
            </div><!-- End Right Col -->
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
</main>
@endsection

@section('js')
    <!-- Select js -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>

    <!-- Text Editor js -->
    <script src="{{ asset('admin_assets/plugins/editor/editor.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        // Initialize text editor
        $("#txtEditor").Editor(); 
        $('.Editor-editor').html($('#answer').val());
    </script>
@endsection