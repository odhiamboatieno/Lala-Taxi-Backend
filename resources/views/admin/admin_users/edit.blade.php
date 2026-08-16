@extends('admin.template')

@section('title', __('messages.admin.manage_admin_page.edit_admin_user'))
@section('title-sub', __('messages.admin.manage_admin_page.admin_users'))
@section('pagetitle', __('messages.admin.manage_admin_page.edit_admin_user'))

@section('css')
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
    <div id="layout-wrapper">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <h5 class="card-title flex-grow-1 mb-0">
                                {{ __('messages.admin.manage_admin_page.edit_admin_user_form') }}
                            </h5>
                        </div>
                        <div class="alert alert-info" role="alert">
                            <strong>{{ __('messages.admin.manage_admin_page.edit_admin_user') }}</strong><br>
                            {{ __('messages.admin.home') }} ->
                            <a href="{{ url(LOGIN_USER_TYPE . '/admin_user') }}" class="alert-link">
                                {{ __('messages.admin.manage_admin_page.admin_users') }}
                            </a> ->
                            <span class="text-primary">{{ __('messages.admin.edit') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        {!! Form::open(['url' => 'admin/edit_admin_users/'.$result->id, 'class' => 'row g-5', 'id' => 'edit-admin-user-form']) !!}
                        @include('admin.admin_users.partials.form', ['result' => $result])
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-2">{{ __('messages.admin.manage_admin_page.edit_admin_user_form') }}</h5>
                        <p class="text-muted mb-3">{{ __('messages.admin.manage_admin_page.edit_admin_user_description') }}</p>
                        <div class="border rounded p-3 bg-light-subtle">
                            <p class="mb-2"><strong>{{ __('messages.admin.manage_admin_page.form.email') }}:</strong> {{ $result->email }}</p>
                            <p class="mb-2"><strong>{{ __('messages.admin.manage_admin_page.form.role') }}:</strong> {{ optional($result->roles->first())->name ?: '-' }}</p>
                            <p class="mb-0"><strong>{{ __('messages.admin.manage_admin_page.form.status') }}:</strong> {{ $result->status }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="assets/js/form/forms-select.init.js"></script>
@endsection
