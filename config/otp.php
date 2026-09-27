<?php

return [
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
