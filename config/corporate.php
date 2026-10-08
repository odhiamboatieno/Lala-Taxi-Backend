<?php

return [
    // Enable only after schema, finance adapters and compatible clients pass pilot validation.
    'bookings_enabled' => env('CORPORATE_BOOKINGS_ENABLED', false),
    // Finance always has invoice access. Managers require explicit platform policy approval.
    'manager_finance_access' => env('CORPORATE_MANAGER_FINANCE_ACCESS', false),
];
