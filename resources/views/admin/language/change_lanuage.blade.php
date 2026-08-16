@extends('admin.template')

@section('title-sub', 'Manage Language file')
@section('pagetitle', 'Manage Language file')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
    <!-- Custom css for tabs -->
    <style>
        .language-tabs .nav-tabs {
            border-bottom: 1px solid var(--color-as-dee2e6);
        }
        .language-tabs .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-radius: 0;
            margin-right: 2px;
        }
        .language-tabs .nav-tabs .nav-link.active {
            background-color: var(--color-as-007bff);
            color: white;
            border-color: var(--color-as-007bff);
        }
        .language-content {
            border: 1px solid var(--color-as-dee2e6);
            border-top: none;
            padding: 20px;
            border-radius: 0;
        }
        .language-section {
            margin-bottom: 30px;
        }
        .language-section h4 {
            color: var(--color-as-007bff);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-as-f8f9fa);
        }
        .language-field {
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-body">
                        {!! Form::open(['url' => route('language.update_locale'), 'class' => 'row g-4']) !!}

                        <div class="col-12">
                            <h6 class="mb-3 text-primary">{{ __('messages.blade_static.manage_language_file_form_6602fcf5') }}</h6>
                            <p class="text-danger mb-3">{{ __('messages.blade_static.fields_are_mandatory_721cfbe4') }}</p>
                        </div>

                        <!-- Language Selection -->
                        <div class="col-12">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-end">
                                    <label class="form-label fw-bold" for="input_locale_lanuage">
                                        {{ __('messages.blade_static.language_89b86ab0') }}
                                    </label>
                                </div>
                                <div class="col-md-10">
                                    {!! Form::select('lanuage', $all_lanuage, $select_lang, [
                                        'class' => 'form-select',
                                        'id' => 'input_locale_lanuage',
                                        'placeholder' => 'Select'
                                    ]) !!}
                                    <span class="text-danger">{{ $errors->first('lanuage') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Language Content Tabs -->
                        <div class="col-12">
                            <div class="language-tabs">
                                <ul class="nav nav-tabs" role="tablist">
                                    @foreach($language as $key => $value)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $key == 'home' ? 'active' : '' }}" 
                                                    id="{{ $key }}-tab" 
                                                    data-bs-toggle="tab" 
                                                    data-bs-target="#{{ $key }}" 
                                                    type="button" 
                                                    role="tab">
                                                {{ ucfirst($key) }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content language-content">
                                    @foreach($language as $key => $value)
                                        <div class="tab-pane fade {{ $key == 'home' ? 'show active' : '' }}" 
                                             id="{{ $key }}" 
                                             role="tabpanel">
                                            
                                            @if(is_array($value))
                                                <div class="language-section">
                                                    <h4>{{ ucfirst($key) }} Section</h4>
                                                    <div class="row">
                                                        @foreach($value as $sub_key => $sub_value)
                                                            <div class="col-xl-4 col-lg-6 language-field">
                                                                <label class="form-label text-capitalize">
                                                                    {{ $sub_key }}
                                                                </label>
                                                                <input type="text" 
                                                                       name="data[{{ $key }}][{{ $sub_key }}]" 
                                                                       value="{{ $sub_value }}" 
                                                                       class="form-control">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="language-field">
                                                    <label class="form-label">{{ $key }}</label>
                                                    <input type="text" 
                                                           name="data[{{ $key }}]" 
                                                           value="{{ $value }}" 
                                                           class="form-control">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="cancel" value="cancel" class="btn btn-light-primary">
                                    {{ __('messages.blade_static.cancel_77dfd213') }}
                                </button>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                    {{ __('messages.blade_static.submit_2dacf659') }}
                                </button>
                            </div>
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div><!-- End Left Col -->
        </div><!-- End row -->
    </div><!-- End layout-wrapper -->
    </main>
@endsection
