<?php

return [
    'platform_company_id' => (int) env('SAAS_PLATFORM_COMPANY_ID', 1),
    'module_site_setting_key' => env('SAAS_MODULE_SITE_SETTING_KEY', 'saas_dispatch_module_enabled'),
    'allow_null_platform_drivers' => (bool) env('SAAS_ALLOW_NULL_PLATFORM_DRIVERS', true),
    'default_dispatch_mode' => env('SAAS_DEFAULT_DISPATCH_MODE', 'global'),
    'status_route' => env('SAAS_DISPATCH_STATUS_ROUTE', 'saas-dispatch/status'),
];
