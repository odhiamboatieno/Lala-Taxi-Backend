<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Country;

class CompanyOnboarding
{
    public static function enabled(): bool
    {
        return site_settings('company_onboarding_enabled') !== '0';
    }

    public static function requirePhoneVerification(): bool
    {
        return site_settings('company_onboarding_require_phone_verification') !== '0';
    }

    public static function requireEmailVerification(): bool
    {
        return site_settings('company_onboarding_require_email_verification') !== '0';
    }

    public static function requireLogoUpload(): bool
    {
        return site_settings('company_onboarding_require_logo_upload') !== '0';
    }

    public static function requireDocumentsUpload(): bool
    {
        return site_settings('company_onboarding_require_documents_upload') !== '0';
    }

    public static function requireAdminApproval(): bool
    {
        return site_settings('company_onboarding_require_admin_approval') !== '0';
    }

    public static function steps(): array
    {
        $steps = [
            ['slug' => 'company-details', 'title' => 'Company Details'],
            ['slug' => 'contact-person', 'title' => 'Contact Person'],
            ['slug' => 'address', 'title' => 'Business Address'],
        ];

        if (static::requirePhoneVerification()) {
            $steps[] = ['slug' => 'phone-verification', 'title' => 'Verify Phone'];
        }

        if (static::requireEmailVerification()) {
            $steps[] = ['slug' => 'email-verification', 'title' => 'Verify Email'];
        }

        if (static::requireLogoUpload()) {
            $steps[] = ['slug' => 'logo', 'title' => 'Company Logo'];
        }

        if (static::requireDocumentsUpload()) {
            $steps[] = ['slug' => 'documents', 'title' => 'Documents'];
        }

        $steps[] = ['slug' => 'review', 'title' => 'Review & Submit'];

        return $steps;
    }

    public static function stepSlugs(): array
    {
        return array_column(static::steps(), 'slug');
    }

    public static function firstStep(): string
    {
        return static::steps()[0]['slug'];
    }

    public static function isValidStep(string $step): bool
    {
        return in_array($step, static::stepSlugs(), true);
    }

    public static function nextStep(string $step): string
    {
        $steps = static::stepSlugs();
        $index = array_search($step, $steps, true);

        if ($index === false || !isset($steps[$index + 1])) {
            return $step;
        }

        return $steps[$index + 1];
    }

    public static function previousStep(string $step): string
    {
        $steps = static::stepSlugs();
        $index = array_search($step, $steps, true);

        if ($index === false || $index === 0) {
            return $step;
        }

        return $steps[$index - 1];
    }

    public static function defaultCountryId(): ?int
    {
        $siteCountry = site_settings('admin_country_code');
        if ($siteCountry) {
            return (int) $siteCountry;
        }

        return Country::query()->value('id');
    }

    public static function normalizedStatus(?Company $company): string
    {
        if (!$company) {
            return 'draft';
        }

        $status = strtolower((string) ($company->onboarding_status ?: $company->approval_status ?: ''));

        if ($status === '') {
            if (strtolower((string) $company->status) === 'active') {
                return 'approved';
            }

            return 'draft';
        }

        return $status;
    }

    public static function isApproved(?Company $company): bool
    {
        if (!$company) {
            return false;
        }

        if (!static::enabled()) {
            return strtolower((string) $company->status) === 'active';
        }

        if (!static::requireAdminApproval()) {
            return static::normalizedStatus($company) === 'approved'
                || strtolower((string) $company->status) === 'active';
        }

        return static::normalizedStatus($company) === 'approved';
    }

    public static function dashboardRouteForStatus(?Company $company): ?string
    {
        if (!$company || !static::enabled()) {
            return null;
        }

        $status = static::normalizedStatus($company);

        if (in_array($status, ['draft', 'phone_pending', 'email_pending', 'documents_pending'], true)) {
            return url('company/onboarding');
        }

        if (in_array($status, ['pending_approval', 'rejected', 'suspended'], true)) {
            return url('company/account-under-review');
        }

        return null;
    }
}
