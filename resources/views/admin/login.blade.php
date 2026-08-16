<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    @php
        $loginType = $login_type ?? 'Admin';
        $isCompanyLogin = $loginType === 'Company';
        $defaultUserType = $isCompanyLogin ? 'Company' : old('user_type', 'Admin');
    @endphp
    <title>{{ $isCompanyLogin ? 'Company' : 'Admin' }} Panel | Login</title>
    <link rel="shortcut icon" href="{{ $favicon ?? asset('favicon.ico') }}">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, var(--color-as-rgba-20-184-166-0-24), transparent 36rem),
                linear-gradient(135deg, var(--color-as-07111f) 0%, var(--color-as-123454) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-y: hidden;
        }

        .login-box {
            background: var(--color-white);
            border-radius: 0;
            box-shadow: 0 10px 30px var(--color-as-rgba-0-0-0-0-2);
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .login-box h2 {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--color-as-102033);
            margin-bottom: 1.5rem;
        }

        .login-box h2 .red {
            color: var(--color-as-e63946);
        }

        .login-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .login-tabs ul {
            display: flex;
            background: var(--color-as-f1f5f9);
            border-radius: 0;
            padding: 0.3rem;
            width: 100%;
            list-style: none;
        }

        .login-tabs li {
            flex: 1;
            text-align: center;
            padding: 0.8rem;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--color-as-64748b);
            transition: all 0.3s ease;
            border-radius: 0;
        }

        .login-tabs li.active {
            background: var(--color-as-0f766e);
            color: var(--color-white);
            box-shadow: 0 2px 8px var(--color-as-rgba-0-0-0-0-1);
        }

        .login-tabs li:hover {
            background: var(--color-as-e2e8f0);
            color: var(--color-as-1e3c72);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--color-as-334155);
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--color-as-e2e8f0);
            border-radius: 0;
            font-size: 0.95rem;
            color: var(--color-as-1e3c72);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--color-as-1e3c72);
            box-shadow: 0 0 0 3px var(--color-as-rgba-30-60-114-0-1);
        }

        .form-group input::placeholder {
            color: var(--color-as-94a3b8);
        }

        button[type="submit"] {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(90deg, var(--color-as-0f766e) 0%, var(--color-info) 100%);
            border: none;
            border-radius: 0;
            color: var(--color-white);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background: linear-gradient(90deg, var(--color-as-115e59) 0%, var(--color-as-1d4ed8) 100%);
            transform: translateY(-2px);
        }

        .flash-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .alert {
            padding: 1rem;
            border-radius: 0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            max-width: 300px;
        }

        .alert-success {
            background: var(--color-as-d1fae5);
            color: var(--color-as-065f46);
        }

        .alert-danger {
            background: var(--color-as-fee2e2);
            color: var(--color-as-991b1b);
        }

        .alert-close {
            text-decoration: none;
            color: inherit;
            font-size: 1rem;
        }

        @media (max-width: 480px) {
            .login-box {
                margin: 1rem;
                padding: 1.5rem;
            }

            .login-tabs ul {
                flex-direction: column;
            }

            .login-tabs li {
                margin-bottom: 0.5rem;
            }
        }
    </style>
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css?v='.filemtime(public_path('css/theme.css'))) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<script src="{{ url('js/colors.js?v='.filemtime(public_path('js/colors.js'))) }}"></script>
  <script src="{{ url('js/lucide.min.js?v='.filemtime(public_path('js/lucide.min.js'))) }}"></script>
  <script src="{{ url('js/lucide-loader.js?v='.filemtime(public_path('js/lucide-loader.js'))) }}"></script>
  <!-- /lalatokens -->
</head>
<body class="hold-transition login-page">
    <div class="flash-container">
        @if(Session::has('message') && !Auth::guard('admin')->check())
        <div class="alert {{ Session::get('alert-class') }}" role="alert">
            <a href="#" class="alert-close" data-dismiss="alert"><i class="fas fa-times"></i></a>
            {{ Session::get('message') }}
        </div>
        @endif
    </div>
    <div class="login-box">
        <h2>{{ $isCompanyLogin ? 'COMPANY LOGIN' : 'ADMIN LOGIN' }} <span class="red"><strong>{{ $site_name ?? config('app.name') }}</strong></span></h2>
        <div class="register-container">
            <div class="register">
                <form action="{{ url('admin/authenticate') }}" method="post">
                    {!! Form::token() !!}
                    @unless($isCompanyLogin)
                        <div class="login-tabs">
                            <ul>
                                <li class="login_btn {{ $defaultUserType == 'Admin' ? 'active' : '' }}" user="Admin">{{ __('messages.blade_static.admin_4e7afebc') }}</li>
                                <li class="login_btn {{ $defaultUserType == 'Dispatcher' ? 'active' : '' }}" user="Dispatcher">{{ __('messages.blade_static.dispatcher_48f91366') }}</li>
                            </ul>
                        </div>
                    @endunless
                    <div class="tab-content">
                        <input type="hidden" name="user_type" class="user_type" value="{{ $defaultUserType }}">
                        <div class="form-group">
                            <label for="username" class="username_label">{{ $isCompanyLogin ? 'Email / Mobile Number' : 'Username' }}</label>
                            <input type="text" id="username" name="username" placeholder="{{ $isCompanyLogin ? 'Enter email or mobile number' : 'Enter username' }}" value="">
                        </div>
                        <div class="form-group">
                            <label for="password">{{ __('messages.blade_static.password_8be3c943') }}</label>
                            <input type="password" id="password" name="password" placeholder="{{ __('messages.blade_static.enter_password_570591c0') }}" value="{{ canDisplayCredentials() ? '123456' : '' }}">
                        </div>
                    </div>
                    <button type="submit">{{ __('messages.blade_static.login_d4fc4761') }}</button>
                </form>
            </div>
        </div>
    </div>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var canDisplayCredentials = '{!! canDisplayCredentials() !!}';
            var isCompanyLogin = {{ $isCompanyLogin ? 'true' : 'false' }};

            $('.login_btn').click(function() {
                $('.login_btn').removeClass('active');
                $(this).addClass('active');
                login_change();
            });

            login_change();

            function login_change() {
                if (isCompanyLogin) {
                    $('.user_type').val('Company');
                    if (canDisplayCredentials === '1') {
                        $('#username').val('9876543211');
                    }
                    return;
                }

                var user = $('.login_btn.active').attr('user');
                $('.user_type').val(user);
                if (user === 'Admin') {
                    if (canDisplayCredentials === '1') {
                        $('#username').val('admin');
                    }
                    $('#username').attr('placeholder', 'Enter username');
                    $('.username_label').text('Username');
                } else if (user === 'Dispatcher') {
                    if (canDisplayCredentials === '1') {
                        $('#username').val('dispatcher');
                    }
                    $('#username').attr('placeholder', 'Enter username');
                    $('.username_label').text('Username');
                } else if (user === 'Company') {
                    if (canDisplayCredentials === '1') {
                        $('#username').val('9876543211');
                    }
                    $('#username').attr('placeholder', 'Enter email or mobile number');
                    $('.username_label').text('Email / Mobile Number');
                }
            }
        });
    </script>
</body>
</html>
