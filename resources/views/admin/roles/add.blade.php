@extends('admin.template')

@section('title', 'Add Role | FabKin Admin & Dashboards Template')
@section('title-sub', 'Add Role')
@section('pagetitle', 'Add Role')

@section('css')
    <!-- Select css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Datepicker CSS (kept for consistency) -->
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">

    <style>
        .permissions-container {
            max-height: 420px;
            overflow-y: auto;
            padding: 1rem;
            border: 1px solid var(--color-as-e0e0e0);
            border-radius: 0;
            background: var(--color-as-f9f9f9);
        }
        .permission-group {
            margin-bottom: 1.5rem;
        }
        .permission-group h6 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
            color: var(--color-as-495057);
            border-bottom: 1px solid var(--color-as-dee2e6);
            padding-bottom: 0.5rem;
        }
        .permission-item {
            margin-bottom: 0.5rem;
        }
        .permission-item label {
            cursor: pointer;
            user-select: none;
        }
    </style>
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            {!! Form::open(['url' => 'admin/add_role', 'class' => 'row g-5', 'id' => 'role_form']) !!}

                            <div class="col-12">
                                <label class="form-label" for="input_name">
                                    {{ __('messages.admin.manage_admin_page.form.name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('name', old('name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_name',
                                    'placeholder' => 'Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_display_name">
                                    {{ __('messages.admin.manage_admin_page.form.display_name') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::text('display_name', old('display_name'), [
                                    'class' => 'form-control',
                                    'id' => 'input_display_name',
                                    'placeholder' => 'Display Name'
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('display_name') }}</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="input_description">
                                    {{ __('messages.admin.manage_admin_page.form.description') }} <em class="text-danger">*</em>
                                </label>
                                {!! Form::textarea('description', old('description'), [
                                    'class' => 'form-control',
                                    'id' => 'input_description',
                                    'placeholder' => 'Description',
                                    'rows' => 4
                                ]) !!}
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            </div>

                            <!-- Mandatory note -->
                            <div class="col-12 mt-3">
                                <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End left col -->

            <!-- Right column: Permissions & Actions -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ __('messages.admin.manage_admin_page.form.permissions') }}</h5>

                        @if ($errors->has('permission'))
                            <div class="alert alert-danger">
                                {{ $errors->first('permission') }}
                            </div>
                        @endif

                        <div class="permissions-container">
                            @php
                                // Optional: group permissions by module/category if you have them grouped in $permissions
                                // For now we show them flat
                            @endphp

                            <div class="permission-list">
                                @foreach($permissions as $row)
                                    <div class="permission-item form-check">
                                        <input type="checkbox" 
                                               class="form-check-input permission_check" 
                                               name="permission[]" 
                                               value="{{ $row->id }}" 
                                               id="perm_{{ $row->id }}"
                                               {{ old('permission') && in_array($row->id, old('permission')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $row->id }}">
                                            {{ $row->display_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-5 text-end">
                            <a href="{{ url(LOGIN_USER_TYPE . '/roles') }}" class="btn btn-light-primary">
                                {{ __('messages.admin.cancel') }}
                            </a>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                {{ __('messages.admin.submit') }}
                            </button>
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

    <!-- Permission dependency logic (kept from your original) -->
    <script type="text/javascript">
        function permission_changes() {
            // View Dashboard logic
            if (!$('input[value="3"]').is(":checked") && 
                !$('input[value="4"]').is(":checked") && 
                !$('input[value="5"]').is(":checked")) {
                $("input[value='2']").removeAttr("disabled");
            } else {
                $("input[value='2']").prop('checked', true).attr("disabled", "disabled");
            }

            // Users management logic
            if (!$('input[value="19"]').is(":checked") && 
                !$('input[value="20"]').is(":checked") && 
                !$('input[value="21"]').is(":checked")) {
                $("input[value='18']").removeAttr("disabled");
            } else {
                $("input[value='18']").prop('checked', true).attr("disabled", "disabled");
            }

            // Another group (adjust IDs as per your actual permissions)
            if (!$('input[value="42"]').is(":checked") && 
                !$('input[value="43"]').is(":checked") && 
                !$('input[value="44"]').is(":checked")) {
                $("input[value='41']").removeAttr("disabled");
            } else {
                $("input[value='41']").prop('checked', true).attr("disabled", "disabled");
            }
        }

        $(document).ready(function() {
            $('.permission_check').on('change', function() {
                permission_changes();
            });

            // Run once on load to set initial state
            permission_changes();
        });
    </script>
@endsection