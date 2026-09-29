@extends('admin.template')

@section('title', 'Edit Static Page | FabKin Admin & Dashboards Template')
@section('title-sub', 'Edit Static Page')
@section('pagetitle', 'Edit Static Page')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency across admin) -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <!-- Add your WYSIWYG editor CSS here if not included in template -->
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" 
         ng-controller="pages"
         ng-init="translations = {{ json_encode(old('translations') ?: $result->translations) }}; 
                  removed_translations = []; 
                  errors = {{ json_encode($errors->getMessages()) }}; 
                  result_translations = {{ json_encode($result->translations) }};">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit_page/'.$result->id, 'class' => 'row g-5', 'id' => 'page_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_static_pages.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', old('name', $result->name), [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => __('messages.admin.manage_static_pages.form.name')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_content">
                                    {{ __('messages.admin.manage_static_pages.form.content') }} <em class="text-danger">*</em>
                                </label>
                                <textarea id="txtEditor" name="txtEditor"></textarea>
                                {!! Form::textarea('content', old('content', $result->content), ['id' => 'content', 'hidden' => true]) !!}
                                <span class="text-danger">{{ $errors->first('content') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_footer">
                                    {{ __('messages.admin.manage_static_pages.form.footer') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('footer', ['yes' => 'Yes', 'no' => 'No'], old('footer', $result->footer), [
                                    'class' => 'form-select',
                                    'id' => 'input_footer',
                                    'placeholder' => 'Select'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('footer') }}</span>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.manage_static_pages.form.status') }} <em class="text-danger">*</em>
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

            <!-- Right column: Translations -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.admin.manage_static_pages.translations') }}</h5>

                        <div class="panel-body" ng-cloak ng-init="languages = {{ json_encode($languages) }}">
                            <input type="hidden" name="removed_translations" ng-value="removed_translations.toString()">

                            <div ng-repeat="translation in translations" class="border rounded p-3 mb-4 bg-light">
                                <div class="row g-3">
                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <label class="form-label mb-0">
                                            {{ __('messages.admin.manage_static_pages.form.language') }} <em class="text-danger">*</em>
                                        </label>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <select name="translations[@{{$index}}][locale]" 
                                                class="form-select" 
                                                ng-model="translation.locale">
                                            <option value="">{{ __('messages.admin.select') }}</option>
                                            <option ng-if="!languages.hasOwnProperty(translation.locale) && translation.locale != ''" 
                                                    value="@{{translation.locale}}">
                                                @{{translation.language.name || translation.locale}}
                                            </option>
                                            @foreach($languages as $key => $value)
                                                <option value="{{ $key }}"
                                                        ng-if="('{{ $key }}' | checkKeyValueUsedInStack : 'locale' : translations) || '{{ $key }}' == translation.locale"
                                                        ng-disabled="'{{ $key }}' != translation.locale && ('{{ $key }}' | checkKeyValueUsedInStack : 'locale' : translations) && '{{ $key }}' != 'en'">
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">@{{ errors['translations.' + $index + '.locale'][0] }}</span>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            {{ __('messages.admin.manage_static_pages.form.name') }} <em class="text-danger">*</em>
                                        </label>
                                        <input type="text"
                                               name="translations[@{{$index}}][name]"
                                               class="form-control"
                                               ng-model="translation.name"
                                               placeholder="{{ __('messages.admin.manage_static_pages.form.name') }}">
                                        <span class="text-danger">@{{ errors['translations.' + $index + '.name'][0] }}</span>
                                    </div>

                                    <div class="col-12" ng-init="multiple_editors($index)">
                                        <label class="form-label">
                                            {{ __('messages.admin.manage_static_pages.form.content') }} <em class="text-danger">*</em>
                                        </label>
                                        <textarea class="editors" id="editor_@{{$index}}" 
                                                  name="translations[@{{$index}}][txtEditor]" 
                                                  data-index="@{{$index}}"></textarea>
                                        <textarea class="contents" id="content_@{{$index}}" 
                                                  name="translations[@{{$index}}][description]" hidden>@{{translation.description}}</textarea>
                                        <span class="text-danger">@{{ errors['translations.' + $index + '.description'][0] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add translation button -->
                            <div class="text-center mt-4" 
                                 ng-show="(translations | checkActiveTranslation: languages).length < {{ count($languages) - 1 }}">
                                <button type="button" class="btn btn-info" ng-click="translations.push({locale:''})">
                                    <i class="fa fa-plus"></i> {{ __('messages.admin.manage_static_pages.add_translation') }}
                                </button>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-5 text-end">
                                <a href="{{ url(LOGIN_USER_TYPE . '/pages') }}" class="btn btn-light-primary">
                                    {{ __('messages.admin.cancel') }}
                                </a>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                    {{ __('messages.admin.submit') }}
                                </button>
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

    <!-- Datepicker Js (kept for consistency) -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <!-- WYSIWYG Editor Initialization -->
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            // Main editor (default language / English)
            if (typeof $("#txtEditor").Editor === 'function') {
                $("#txtEditor").Editor();
                $('.Editor-editor').html($('#content').val());
            }

            // Handle dynamic editors in translations
            const observer = new MutationObserver(function () {
                document.querySelectorAll('.editors:not(.editor-initialized)').forEach(el => {
                    if (typeof $(el).Editor === 'function') {
                        $(el).Editor();
                        
                        // Try to load existing content from hidden field
                        const index = el.id.split('_')[1];
                        const hiddenField = document.getElementById('content_' + index);
                        if (hiddenField && hiddenField.value) {
                            const editorContent = $(el).siblings('.Editor-container').find('.Editor-editor');
                            editorContent.html(hiddenField.value);
                        }
                    }
                    el.classList.add('editor-initialized');
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });

            // Also initialize any editors that might already exist on load
            document.querySelectorAll('.editors').forEach(el => {
                if (!el.classList.contains('editor-initialized') && typeof $(el).Editor === 'function') {
                    $(el).Editor();
                    const index = el.id.split('_')[1];
                    const hiddenField = document.getElementById('content_' + index);
                    if (hiddenField && hiddenField.value) {
                        $(el).siblings('.Editor-container').find('.Editor-editor').html(hiddenField.value);
                    }
                    el.classList.add('editor-initialized');
                }
            });
        });
    </script>
@endsection
