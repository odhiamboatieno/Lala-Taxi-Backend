<style>
    body {
        top: 0px !important;
        margin: 0;
        overflow-x: hidden;
    }

    /* Preloader Styles */
    .preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #1e3c72, #2a5298); /* Carpooling-themed gradient */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }

    .preloader.hidden {
        opacity: 0;
        visibility: hidden;
    }

    /* Four Dots Animation */
    .dots-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
        width: 100px;
        height: 100px;
    }

    .dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        position: absolute;
        animation: rotateAndSwitch 3s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }

    .dot:nth-child(1) {
        background-color: #ff6b6b; /* Red */
        animation-delay: 0s;
    }

    .dot:nth-child(2) {
        background-color: #48dbfb; /* Blue */
        animation-delay: 0.15s;
    }

    .dot:nth-child(3) {
        background-color: #1dd1a1; /* Green */
        animation-delay: 0.3s;
    }

    .dot:nth-child(4) {
        background-color: #feca57; /* Yellow */
        animation-delay: 0.45s;
    }

    .preloader-text {
        color: #fff;
        font-size: 18px;
        font-weight: bold;
        font-family: 'Arial', sans-serif;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-top: 20px;
    }

    /* Animation */
    @keyframes rotateAndSwitch {
        0%, 100% {
            transform: rotate(0deg) translateX(40px) rotate(0deg);
        }
        25% {
            transform: rotate(90deg) translateX(40px) rotate(-90deg);
        }
        50% {
            transform: rotate(180deg) translateX(40px) rotate(-180deg);
        }
        75% {
            transform: rotate(270deg) translateX(40px) rotate(-270deg);
        }
    }

    /* Ensure content is hidden until loaded */
    .main-header.hide, .flash-container.hide {
        display: none;
    }

    /* Flash message styling */
    .flash-container .alert {
        margin: 10px auto;
        max-width: 600px;
        position: relative;
        z-index: 1000;
    }

    /* Currency select styling */
    #js-currency-select {
        padding: 1px 7px;
        float: right;
        font-size: 13px;
        display: inline-block;
        color: #000;
        height: auto;
        margin: 13px 6px 3px;
        border-color: rgb(169, 169, 169);
        width: auto;
    }
</style>

<!-- Preloader -->
<div class="preloader">
    <div class="dots-container">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
   
</div>

<header class="main-header hide">
    <!-- Logo -->
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <!-- logo for regular state and mobile devices -->
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">{{ __('messages.blade_static.toggle_navigation_1ec0b00d') }}</span>
        </a>
        <span id="show_date_time" class="show_date_time" style="color:#303841; font-size:16px; line-height: 46px;"></span>
        <div class="navbar-custom-menu">
            @php
                if (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE == 'company') {
                    $user = Auth::guard('company')->user();
                    $company_user = true;
                } else {
                    $user = Auth::guard('admin')->user();
                    $company_user = false;
                }
            @endphp
            <ul class="nav navbar-nav">
                <input type="hidden" id="current_time" value="{{ date('F d, Y H:i:s', time()) }}">

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                        aria-expanded="false">
                        <x-icon class="w-5 h-5" style="width:18px;" name="flag-language-{{ $default_language[0]->value }}" />
                        {{ $default_language[0]->name }}
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        @foreach ($language as $key => $lang)
                            @if ($key != App::getLocale())
                                <li>
                                    <a class="dropdown-item inline" href="{{ route('switch.locale', $key) }}">
                                        <x-icon class="w-5 h-5" style="width:18px;" name="flag-language-{{ $key }}" />
                                        {{ $lang }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </li>

                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        @if (!$company_user || $user->profile == null)
                            <img src="{{ url('admin_assets/dist/img/avatar04.png') }}" class="user-image"
                                 alt="{{ __('messages.blade_static.user_image_f1658ae3') }}">
                        @else
                            <img src="{{ $user->profile }}" class="user-image" alt="{{ __('messages.blade_static.user_image_f1658ae3') }}">
                        @endif
                        <span class="hidden-xs">{{ !$company_user ? $user->username : $user->name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            @if (!$company_user || $user->profile == null)
                                <img src="{{ url('admin_assets/dist/img/avatar04.png') }}" class="img-circle"
                                     alt="{{ __('messages.blade_static.user_image_f1658ae3') }}">
                            @else
                                <img src="{{ $user->profile }}" class="img-circle" alt="{{ __('messages.blade_static.user_image_f1658ae3') }}">
                            @endif
                            <p>
                                {{ !$company_user ? $user->username : $user->name }}
                                <small>{{ __('messages.blade_static.member_since_f425b08f') }} {{ date('M. Y', strtotime($user->created_at)) }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            @if ($company_user)
                                <div class="pull-left">
                                    <a href="{{ url('company/profile') }}" class="btn btn-default btn-flat">{{ __('messages.blade_static.profile_ff4fc027') }}</a>
                                </div>
                            @endif
                            <div class="pull-right">
                                <a href="{{ url($company_user ? 'company/logout' : 'admin/logout') }}"
                                   class="btn btn-primary btn-flat">{{ __('messages.blade_static.sign_out_dc1649a1') }}</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        @if ($company_user)
            <select id="js-currency-select" class="form-control">
                @foreach ($currency_select as $code)
                    <option value="{{ $code }}" @if (session('currency') == $code) selected="selected" @endif>
                        {{ $code }}
                    </option>
                @endforeach
            </select>
        @endif
    </nav>
</header>

<div class="flash-container hide">
    @if (Session::has('message'))
        <div class="alert text-center {{ Session::get('alert-class') }}" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('messages.blade_static.close_bbfa773e') }}"></button>
            {{ Session::get('message') }}
        </div>
    @endif
</div>

<script>
    // Preloader JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        const preloader = document.querySelector('.preloader');
        const mainHeader = document.querySelector('.main-header');
        const flashContainer = document.querySelector('.flash-container');

        // Simulate content loading (replace with actual AJAX/data load check if needed)
        setTimeout(() => {
            preloader.classList.add('hidden');
            mainHeader.classList.remove('hide');
            flashContainer.classList.remove('hide');
        }, 2000); // Adjust timeout based on actual content load time

        // Optional: Remove preloader from DOM after fade-out
        preloader.addEventListener('transitionend', () => {
            if (preloader.classList.contains('hidden')) {
                preloader.style.display = 'none';
            }
        });
    });
</script>
