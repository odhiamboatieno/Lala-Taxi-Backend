@extends('admin.template')


@section('title-sub', 'Edit Cancel Reason')
@section('pagetitle', 'Edit Cancel Reason')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
    <!-- Custom styles for translations -->
    <style>
        .translation-item {
            background: #f8f9fa;
            border: 1px solid #e3e6f0;
            border-radius: 0.375rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .translation-item .btn-remove {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
        }
        .translation-item legend {
            margin-top: 1rem;
            border-bottom: 1px solid #dee2e6;
            width: 100%;
        }
        .add-translation-btn {
            display: block;
            margin: 0 auto;
        }
    </style>
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" 
         ng-controller="cancel_reason_controller" 
         ng-init="translations = {{ json_encode(old('translations') ?: $result->translations->toArray()) }}; 
                  removed_translations = []; 
                  errors = {{ json_encode($errors->getMessages()) }}; 
                  languages = {{ json_encode($language) }}; 
                  main_reason = '{{ $result->reason }}';">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/edit-cancel-reason/'.$result->id, 'class' => 'row g-5']) !!}

                            <!-- === MAIN REASON (EN) === -->
                            <div class="col-12">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.cancel_reasons.form.main_reason') }}</h6>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_reason">
                                    {{ __('messages.admin.cancel_reasons.form.reason') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('reason', old('reason', $result->reason), [
                                    'class' => 'form-control',
                                    'id' => 'input_reason',
                                    'placeholder' => __('messages.admin.cancel_reasons.form.reason')
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('reason') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_cancelled_by">
                                    {{ __('messages.admin.cancel_reasons.form.cancelled_by') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('cancelled_by', [
                                    'Rider' => 'Rider',
                                    'Driver' => 'Driver',
                                    'Admin' => 'Admin',
                                    'Company' => 'Company'
                                ], old('cancelled_by', $result->cancelled_by), [
                                    'class' => 'form-select',
                                    'id' => 'input_cancelled_by'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('cancelled_by') }}</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="input_status">
                                    {{ __('messages.admin.cancel_reasons.form.status') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::select('status', [
                                    'Active' => 'Active',
                                    'Inactive' => 'Inactive'
                                ], old('status', $result->status), [
                                    'class' => 'form-select',
                                    'id' => 'input_status'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            </div>

                          
                        </div>
                    </div>
                </div>
            </div><!-- End Left Col -->

            <!-- Right Column: Breadcrumb & Info -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                          <!-- === TRANSLATIONS === -->
                            <div class="col-12 mt-4">
                                <h6 class="mb-3 text-primary">{{ __('messages.admin.cancel_reasons.translations') }}</h6>
                                <input type="hidden" name="removed_translations" ng-value="removed_translations.join(',')">
                            </div>

                            <div class="col-12" ng-repeat="translation in translations" ng-cloak>
                                <div class="translation-item">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove" 
                                            ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                                        <i class="fa fa-trash"></i> {{ __('messages.admin.cancel_reasons.remove') }}
                                    </button>

                                    <input type="hidden" name="translations[@{{$index}}][id]" value="@{{translation.id}}">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                {{ __('messages.admin.cancel_reasons.form.language') }} <em class="text-danger">*</em>
                                            </label>
                                            <select name="translations[@{{$index}}][locale]" 
                                                    class="form-select" 
                                                    id="input_language_@{{$index}}" 
                                                    ng-model="translation.locale">
                                                <option value="" ng-selected="!translation.locale">{{ __('messages.blade_static.select_language_17d647c6') }}</option>
                                                @foreach($language as $key => $lang_name)
                                                    @if($key !== 'en')
                                                        <option value="{{ $key }}"
                                                                ng-if="isLanguageAvailable('{{ $key }}', $index)"
                                                                ng-selected="translation.locale === '{{ $key }}'">
                                                            {{ $lang_name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <span class="text-danger">@{{ errors['translations.'+$index+'.locale'][0] }}</span>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">
                                                {{ __('messages.admin.cancel_reasons.form.reason') }} <em class="text-danger">*</em>
                                            </label>
                                            {!! Form::text('translations[@{{$index}}][reason]', null, [
                                                'class' => 'form-control',
                                                'id' => 'input_reason_@{{$index}}',
                                                'placeholder' => __('messages.admin.cancel_reasons.form.reason'),
                                                'ng-model' => 'translation.reason'
                                            ]) !!}
                                            <span class="text-danger">@{{ errors['translations.'+$index+'.reason'][0] }}</span>
                                        </div>
                                    </div>

                                    <legend ng-if="$index + 1 < translations.length"></legend>
                                </div>
                            </div>

                            <!-- Add Translation Button -->
                            <div class="col-12 text-center" 
                                 ng-show="translations.length < {{ count($language) - 1 }} && canAddMoreTranslations()">
                                <button type="button" class="btn btn-info add-translation-btn" 
                                        ng-click="addTranslation()">
                                    <i class="fa fa-plus"></i> {{ __('messages.admin.cancel_reasons.add_translations') }}
                                </button>
                            </div>

                            <!-- === MANDATORY & BUTTONS === -->
                            <div class="col-12 mt-4">
                                <p class="text-danger mb-3">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
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
            </div><!-- End Right Col -->
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
@endsection

@section('js')
    <!-- Select js -->
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="assets/js/form/forms-select.init.js"></script>

    <!-- App js -->
    <script type="module" src="assets/js/app.js"></script>

    <!-- Angular Controller for Translations -->
    <script>
        app.controller('cancel_reason_controller', function($scope) {
            $scope.translations = $scope.translations || [];
            $scope.removed_translations = $scope.removed_translations || [];
            $scope.languages = $scope.languages || {};

            // Check if a language is already used (except current index)
            $scope.isLanguageAvailable = function(langKey, currentIndex) {
                for (let i = 0; i < $scope.translations.length; i++) {
                    if (i !== currentIndex && $scope.translations[i].locale === langKey) {
                        return false;
                    }
                }
                return true;
            };

            // Can add more translations?
            $scope.canAddMoreTranslations = function() {
                const usedLangs = $scope.translations.map(t => {{ __('messages.blade_static.t_locale_filter_boolean_return_usedlangs_length_30755dc3') }} < (Object.keys($scope.languages).length - 1);
            };

            // Add new empty translation
            $scope.addTranslation = function() {
                $scope.translations.push({ locale: '', reason: '' });
            };
        });
    </script>
@endsection