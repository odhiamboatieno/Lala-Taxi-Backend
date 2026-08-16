<?php

return [
    'otp_expiry_minutes' => env('COMPANY_ONBOARDING_OTP_EXPIRY', 10),
    'otp_resend_cooldown_seconds' => env('COMPANY_ONBOARDING_OTP_RESEND_COOLDOWN', 60),
    'otp_max_attempts' => env('COMPANY_ONBOARDING_OTP_MAX_ATTEMPTS', 5),
    'logo_max_size_kb' => env('COMPANY_ONBOARDING_LOGO_MAX_SIZE_KB', 4096),
    'document_max_size_kb' => env('COMPANY_ONBOARDING_DOCUMENT_MAX_SIZE_KB', 6144),
    'required_documents' => [
        'business_certificate',
        'operator_license',
        'director_id',
        'proof_of_address',
    ],
];
