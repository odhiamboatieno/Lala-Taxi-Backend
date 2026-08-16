<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Collection;
use Modules\SaaSDispatch\Entities\CompanyPaymentGateway;

/**
 * Centralized payment-gateway credential resolver.
 *
 * Single source of truth that decides which credentials to use for a given
 * gateway lookup.  It encapsulates the platform-wide `wallet_payment_owner`
 * mode (`admin` | `company`) and the multi-tenant fallback rules.
 *
 * The class is intentionally a thin wrapper around the existing
 * `PaymentGateway` model (admin scope) and `CompanyPaymentGateway` table
 * (tenant scope) – it does **not** introduce a new payment integration and
 * re-uses the existing gateway business logic.
 *
 * Behaviour:
 *
 *   Mode = admin   -> always `payment_gateway` (company_id = NULL)
 *                     regardless of any tenant context.
 *
 *   Mode = company -> prefer `company_payment_gateways` for the active
 *                     company id; fall back to the global `payment_gateway`
 *                     row only when no tenant override is defined.
 */
class PaymentGatewayResolver
{
    public function __construct(protected CompanyPaymentGatewayResolver $companyResolver)
    {
    }

    /**
     * Determine whether the platform-wide mode is `admin` (default).
     */
    public function isAdminMode(): bool
    {
        return ! function_exists('wallet_payment_owner')
            || wallet_payment_owner() === 'admin';
    }

    /**
     * Determine whether the platform-wide mode is `company`.
     */
    public function isCompanyMode(): bool
    {
        return function_exists('wallet_payment_owner')
            && wallet_payment_owner() === 'company';
    }

    /**
     * Resolve a single gateway credential (`$key`/`$site`) for the given
     * company context.
     *
     * @return string Empty string when nothing is configured.  The empty
     *                string is intentionally a safe default that keeps the
     *                existing helper-based call-sites working.
     */
    public function value(string $key, string $site, ?int $companyId = null): string
    {
        if ($this->isAdminMode() || ! $this->companyResolver->shouldUseCompanyGateway($companyId)) {
            return $this->globalValue($key, $site);
        }

        $tenantValue = $this->tenantValue($companyId, $key, $site);
        if ($tenantValue !== null && $tenantValue !== '') {
            return $tenantValue;
        }

        return $this->globalValue($key, $site);
    }

    /**
     * Return the merged gateway list (`global` + company overrides) for a
     * tenant.  The merged collection is keyed by `site::name`.
     */
    public function collectionForCompany(int $companyId): Collection
    {
        return $this->companyResolver->collectionForCompany($companyId);
    }

    /**
     * Return the sites that are currently enabled for the resolved
     * credentials.
     */
    public function enabledSites(?int $companyId = null): Collection
    {
        if ($this->isAdminMode() || ! $this->companyResolver->shouldUseCompanyGateway($companyId)) {
            return $this->globalEnabledSites();
        }

        $sites = $this->companyResolver->enabledSites($companyId);
        if ($sites->isNotEmpty()) {
            return $sites;
        }

        return $this->globalEnabledSites();
    }

    /**
     * Look up a single value directly in the global `payment_gateway` table.
     */
    protected function globalValue(string $key, string $site): string
    {
        if (! class_exists(PaymentGateway::class) || ! \Schema::hasTable('payment_gateway')) {
            return '';
        }

        $row = PaymentGateway::query()
            ->where('name', $key)
            ->where('site', $site)
            ->first();

        return (string) ($row->value ?? '');
    }

    /**
     * Look up a single value in the tenant `company_payment_gateways` table.
     * Returns `null` when nothing is stored for the tenant so the caller can
     * fall back to the global table.
     */
    protected function tenantValue(int $companyId, string $key, string $site): ?string
    {
        if (! class_exists(CompanyPaymentGateway::class) || ! \Schema::hasTable('company_payment_gateways')) {
            return null;
        }

        $row = CompanyPaymentGateway::query()
            ->where('company_id', $companyId)
            ->where('name', $key)
            ->where('site', $site)
            ->first();

        return $row?->value;
    }

    protected function globalEnabledSites(): Collection
    {
        if (! class_exists(PaymentGateway::class) || ! \Schema::hasTable('payment_gateway')) {
            return collect();
        }

        return PaymentGateway::query()
            ->where('site', '!=', 'Cash')
            ->where('name', 'is_enabled')
            ->where('value', '1')
            ->pluck('site');
    }
}
