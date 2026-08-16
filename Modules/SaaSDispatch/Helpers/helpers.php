<?php

use Modules\SaaSDispatch\Entities\CompanySaasSetting;
use Modules\SaaSDispatch\Entities\SaaSFeatureModule;

if (!function_exists('saas_dispatch_config')) {
    function saas_dispatch_config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('saas_dispatch', []);
        }

        return config('saas_dispatch.'.$key, $default);
    }
}

if (!function_exists('saas_dispatch_enabled')) {
    function saas_dispatch_enabled(): bool
    {
        return site_settings(saas_dispatch_config('module_site_setting_key', 'saas_dispatch_module_enabled')) === '1';
    }
}

if (!function_exists('saas_platform_company_id')) {
    function saas_platform_company_id(): int
    {
        return (int) saas_dispatch_config('platform_company_id', 1);
    }
}

if (!function_exists('wallet_payment_owner')) {
    /**
     * Resolve the platform-wide payment gateway ownership mode.
     *
     * - admin   : every gateway lookup uses the global `payment_gateway` table
     *             (the default and existing behaviour).
     * - company : each SaaS company uses its own credentials from
     *             `company_payment_gateways`.  The global table is used as a
     *             fallback when the company has no override.
     */
    function wallet_payment_owner(): string
    {
        $value = function_exists('site_settings')
            ? site_settings('wallet_payment_owner')
            : '';

        return $value === 'company' ? 'company' : 'admin';
    }
}

if (!function_exists('user_level_owner')) {
    /**
     * Resolve the platform-wide user level (driver / customer) ownership mode.
     *
     * - admin   : every company inherits the global `user_levels` rows.
     * - company : SaaS companies can inherit an admin level and override its
     *             values, or define their own levels from scratch.
     */
    function user_level_owner(): string
    {
        $value = function_exists('site_settings')
            ? site_settings('user_level_owner')
            : '';

        return $value === 'company' ? 'company' : 'admin';
    }
}

if (!function_exists('company_saas_payment_gateways_enabled')) {
    function company_saas_payment_gateways_enabled(?int $companyId = null): bool
    {
        $companyId = $companyId ?: (auth('company')->check() ? (int) auth('company')->id() : null);

        return saas_dispatch_enabled() && (bool) $companyId && $companyId !== saas_platform_company_id();
    }
}

if (!function_exists('company_saas_settings')) {
    function company_saas_settings(?int $companyId = null): ?CompanySaasSetting
    {
        $companyId = $companyId ?: (auth('company')->check() ? (int) auth('company')->id() : null);

        if (! $companyId || ! class_exists(CompanySaasSetting::class)) {
            return null;
        }

        return CompanySaasSetting::query()->where('company_id', $companyId)->first();
    }
}

if (!function_exists('company_saas_enabled')) {
    function company_saas_enabled(?int $companyId = null): bool
    {
        $companyId = $companyId ?: (auth('company')->check() ? (int) auth('company')->id() : null);

        if (! saas_dispatch_enabled() || ! $companyId || $companyId === saas_platform_company_id()) {
            return false;
        }

        return (bool) optional(company_saas_settings($companyId))->is_saas_enabled;
    }
}

if (!function_exists('saas_feature_module_enabled')) {
    function saas_feature_module_enabled(string $slug): bool
    {
        if (! class_exists(SaaSFeatureModule::class)) {
            return false;
        }

        return SaaSFeatureModule::query()
            ->where('slug', $slug)
            ->where('is_installed', true)
            ->where('is_enabled', true)
            ->exists();
    }
}
