<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Company Panel | Login</title>
    <link rel="shortcut icon" href="{{ $favicon ?? asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            background:
                radial-gradient(circle at top left, rgba(20, 184, 166, .24), transparent 36rem),
                linear-gradient(135deg, #07111f 0%, #123454 100%);
        }

        .login-shell {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 22px 60px rgba(2, 6, 23, 0.28);
            overflow: hidden;
        }

        .login-shell__body {
            padding: 2rem;
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
            color: #fff;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: .08em;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .45rem;
        }

        .login-title span {
            color: #dc2626;
        }

        .login-subtitle {
            color: #64748b;
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: 1rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            background: #ecfeff;
            color: #0f766e;
            font-size: .85rem;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: .45rem;
            color: #334155;
            font-size: .92rem;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: .9rem 1rem;
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            color: #0f172a;
            font-size: .96rem;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .field-error {
            display: block;
            margin-top: .35rem;
            color: #dc2626;
            font-size: .82rem;
        }

        .login-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin: 1.25rem 0 1.5rem;
            color: #64748b;
            font-size: .92rem;
        }

        .login-actions a {
            color: #0f766e;
            font-weight: 700;
            text-decoration: none;
        }

        .login-submit {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: .95rem 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(90deg, #0f766e 0%, #2563eb 100%);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .login-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
        }

        .flash-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            max-width: 320px;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            font-size: .92rem;
            display: flex;
            gap: .5rem;
            align-items: flex-start;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        }

        .alert-info,
        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 576px) {
            body {
                padding: 1rem;
                align-items: stretch;
            }

            .login-shell {
                max-width: none;
            }

            .login-shell__body {
                padding: 1.4rem;
            }

            .login-actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="flash-container">
        @if(Session::has('message'))
            <div class="alert {{ Session::get('alert-class', 'alert-info') }}" role="alert">
                <i class="fas fa-info-circle"></i>
                <span>{{ Session::get('message') }}</span>
            </div>
        @endif
    </div>

    <div class="login-shell">
        <div class="login-shell__body">
            <div class="brand-mark">{{ strtoupper(substr($site_name ?? config('app.name', 'CP'), 0, 2)) }}</div>
            <div class="login-badge">
                <i class="fas fa-building"></i>
                <span>Company Portal</span>
            </div>
            <h1 class="login-title">Company Login <span>{{ $site_name ?? config('app.name') }}</span></h1>
            <p class="login-subtitle">Sign in to manage your fleet operations, drivers, trips, documents, and dispatch settings from one operator workspace.</p>

            <form method="POST" action="{{ url('admin/authenticate') }}">
                {{ csrf_field() }}
                <input type="hidden" name="user_type" value="Company">

                <div class="form-group">
                    <label for="company-username">Email / Mobile Number</label>
                    <input id="company-username" type="text" name="username" value="{{ old('username', old('email')) }}" placeholder="Enter email or mobile number" autocomplete="username" required>
                    <span class="field-error">{{ $errors->first('username') ?: $errors->first('email') }}</span>
                </div>

                <div class="form-group">
                    <label for="company-password">{{ trans('messages.user.paswrd') }}</label>
                    <input id="company-password" type="password" name="password" placeholder="{{ __('messages.blade_static.enter_password_570591c0') }}" autocomplete="current-password" required>
                    <span class="field-error">{{ $errors->first('password') }}</span>
                </div>

                <div class="login-actions">
                    <label><input type="checkbox" name="remember" value="1"> {{ __('messages.blade_static.remember_me_ced7b308') }}</label>
                    <a href="{{ url('forgot_password_company') }}">{{ trans('messages.user.forgot_paswrd') }}</a>
                </div>

                <button type="submit" class="login-submit">{{ trans('messages.header.signin') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
