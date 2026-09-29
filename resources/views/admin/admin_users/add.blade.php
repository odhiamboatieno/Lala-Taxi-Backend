@extends('admin.template')

@section('title', __('messages.admin.manage_admin_page.add_admin_user'))
@section('title-sub', __('messages.admin.manage_admin_page.admin_users'))
@section('pagetitle', __('messages.admin.manage_admin_page.add_admin_user'))

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('main')
    <div id="layout-wrapper">
        <div class="row g-4">
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        {!! Form::open(['url' => 'admin/add_admin_user', 'class' => 'row g-5', 'id' => 'add-admin-user-form']) !!}
                        @include('admin.admin_users.partials.form', ['result' => null])
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-2">{{ __('messages.admin.manage_admin_page.add_admin_user_form') }}</h5>
                        <p class="text-muted mb-3">{{ __('messages.admin.manage_admin_page.add_admin_user_description') }}</p>
                        <div class="border rounded p-3 bg-light-subtle">
                            <p class="mb-2"><strong>{{ __('messages.admin.manage_admin_page.form.role') }}:</strong> {{ __('messages.admin.manage_admin.role_and_permissions') }}</p>
                            <p class="mb-2"><strong>{{ __('messages.admin.manage_admin_page.form.status') }}:</strong> {{ __('messages.blade_static.active_inactive_14f9b98f') }}</p>
                            <p class="mb-0 text-muted">{{ __('messages.admin.fields_are_mandatory') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/forms-select.init.js') }}"></script>
@endsection
