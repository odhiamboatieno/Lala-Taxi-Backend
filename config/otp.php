<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Demo Mode
    |--------------------------------------------------------------------------
    |
    | Switches OTP delivery between demo and live without touching the admin
    | panel. In demo mode every issued code is OTP_DEMO_CODE and the SMS
    | gateway is never called, so logins work on a deployment that has no
    | working Twilio credentials.
    |
    | Defaults to null so OtpManager can tell "not set in .env" (fall back to
    | the site_settings toggles) apart from an explicit "false".
    |
    | SECURITY: while this is true, anyone who knows a registered mobile number
    | can sign in with the fixed code. Keep it false on any public deployment.
    |
    */
    'demo_mode' => env('OTP_DEMO_MODE'),

    // The code issued while demo_mode is on.
    'demo_code' => env('OTP_DEMO_CODE', '123456'),

    /*
    |--------------------------------------------------------------------------
    | OTP Logging
    |--------------------------------------------------------------------------
    |
    | Writes generated OTP values to the application log (stderr by default,
    | so the code is visible via `docker compose logs app`). Intended for
    | local and staging testing only.
    |
    | The value is a live authentication secret. Leave this disabled on any
    | real deployment: anyone who can read container output would otherwise
    | be able to complete a login for any account.
    |
    */
    'log_enabled' => env('OTP_LOG_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Verification Attempts
    |--------------------------------------------------------------------------
    |
    | Number of wrong codes tolerated against a single issued OTP before the
    | record is invalidated and the user must request a new one. Guards the
    | 6-digit code against brute forcing within its expiry window.
    |
    */
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
];
