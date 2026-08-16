<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title> {{ Helpers::meta((!isset($exception)) ? ucfirst(LOGIN_USER_TYPE) : '', 'title') }} </title>
<!-- Tell the browser to be responsive to screen width -->
<meta name="description" content="{{ Helpers::meta((!isset($exception)) ? ucfirst(LOGIN_USER_TYPE) : '', 'description') }}">
<meta name="keywords" content="{{ Helpers::meta((!isset($exception)) ? ucfirst(LOGIN_USER_TYPE) : '', 'keywords') }}">

<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">


<!-- layout setup -->
<script type="module" src="{{ url('assets/js/layout-setup.js') }}"></script>

<!-- App favicon -->
<link rel="shortcut icon" href="{{ $favicon ?? asset('favicon.ico') }}">
@yield('css')
@include('partials.head-css')
<style>
    .dashboard-feedback-modal {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: var(--color-as-rgba-15-23-42-0-45);
    }

    .dashboard-feedback-card {
        width: min(100%, 460px);
        border-radius: 0;
        background: var(--color-white);
        box-shadow: 0 24px 60px var(--color-as-rgba-15-23-42-0-24);
        overflow: hidden;
    }

    .dashboard-feedback-card .feedback-head {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: 1.1rem 1.25rem;
        color: var(--color-white);
        font-weight: 800;
    }

    .dashboard-feedback-card.success .feedback-head {
        background: var(--color-success);
    }

    .dashboard-feedback-card.error .feedback-head {
        background: var(--color-error);
    }

    .dashboard-feedback-card.warning .feedback-head {
        background: var(--color-as-d97706);
    }

    .dashboard-feedback-card .feedback-body {
        padding: 1.25rem;
        color: var(--color-as-334155);
        line-height: 1.55;
    }

    .dashboard-feedback-card .feedback-body ul {
        margin: .5rem 0 0;
        padding-left: 1.25rem;
    }

    .dashboard-feedback-card .feedback-actions {
        padding: 0 1.25rem 1.25rem;
        text-align: right;
    }

    .app-wrapper {
        min-height: calc(100vh - var(--pe-app-header-height, 70px));
        padding-bottom: calc(var(--pe-app-footer-height, 60px) + 1rem);
    }

    .footer {
        position: fixed;
        inset-inline-end: 0;
        inset-block-end: 0;
        z-index: 998;
        width: auto;
        height: auto;
        min-height: var(--pe-app-footer-height, 60px);
        display: flex;
        align-items: center;
        margin-inline-end: 0;
        border-top: 1px solid var(--pe-border-color, var(--color-as-rgba-148-163-184-0-22));
    }

    @media (min-width: 1200px) {
        .footer {
            inset-inline-start: var(--pe-app-sidebar-width);
            margin-inline-start: 0;
        }

        [data-sidebar="icon"] .footer {
            inset-inline-start: var(--pe-app-sidebar-sm-width);
        }
    }

    @media (max-width: 1199.98px) {
        .footer {
            inset-inline-start: 0;
            margin-inline-start: 0;
        }
    }

    [data-layout="horizontal"] .footer {
        inset-inline-start: 0;
        margin-inline-start: 0;
    }

    .pe-app-sidebar-logo a,
    .app-header a.fs-18 {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        overflow: hidden;
    }

    .pe-app-sidebar-logo img,
    .app-header .header-sidebar-logo-default,
    .app-header .header-sidebar-logo-light {
        max-width: 150px;
        object-fit: contain;
    }

    .pe-app-sidebar-logo-minimize,
    .pe-app-sidebar-logo-minimize-light,
    .header-sidebar-logo-small,
    .header-sidebar-logo-small-light {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px;
        max-width: 30px !important;
        object-fit: contain;
    }

    [data-sidebar="icon"] .pe-app-sidebar-logo {
        justify-content: center;
        padding-inline: .75rem !important;
    }
</style>

<body class="admin-app" ng-app="App">
    <div id="admin-page-preloader" aria-hidden="true">
        <div class="admin-preloader-spinner"></div>
    </div>

    @include('partials.header')
    @include('partials.sidebar')
    @include('partials.horizontal')

    <main class="app-wrapper">
        <div class="container-fluid">

            @include('partials.page-title')

            @yield('main')
            @if(Session::has('message') || $errors->any())
                @php
                    $alertClass = Session::get('alert-class', $errors->any() ? 'alert-danger' : 'alert-success');
                    $feedbackClass = str_contains($alertClass, 'success') && !$errors->any()
                        ? 'success'
                        : (str_contains($alertClass, 'warning') ? 'warning' : 'error');
                    $feedbackTitle = $feedbackClass === 'success'
                        ? 'Success'
                        : ($feedbackClass === 'warning' ? 'Warning' : 'Error');
                @endphp
                <div class="dashboard-feedback-modal" id="dashboard-feedback-modal">
                    <div class="dashboard-feedback-card {{ $feedbackClass }}">
                        <div class="feedback-head">
                            <span>{{ $feedbackClass === 'success' ? 'OK' : '!' }}</span>
                            <span>{{ $feedbackTitle }}</span>
                        </div>
                        <div class="feedback-body">
                            @if(Session::has('message'))
                                <div>{{ Session::get('message') }}</div>
                            @endif
                            @if($errors->any())
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="feedback-actions">
                            <button type="button" class="btn btn-light" data-dashboard-feedback-close>{{ __('messages.blade_static.close_bbfa773e') }}</button>
                        </div>
                    </div>
                </div>
            @endif
            @include('partials.switcher')
            @include('partials.scroll-to-top')
            @include('admin.common.modals')
            @include('partials.footer')

            @include('admin.common.foot')

            @include('partials.vendor-scripts')

            @yield('js')

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var feedbackModal = document.getElementById('dashboard-feedback-modal');
                    if (!feedbackModal) {
                        return;
                    }

                    var closeFeedback = function () {
                        feedbackModal.remove();
                    };

                    feedbackModal.querySelectorAll('[data-dashboard-feedback-close]').forEach(function (button) {
                        button.addEventListener('click', closeFeedback);
                    });

                    window.setTimeout(closeFeedback, 6000);
                });

                window.addEventListener('load', function () {
                    var preloader = document.getElementById('admin-page-preloader');
                    if (!preloader) {
                        return;
                    }

                    preloader.classList.add('is-hidden');
                    window.setTimeout(function () {
                        preloader.remove();
                    }, 320);
                });
            </script>

</body>

</html>
