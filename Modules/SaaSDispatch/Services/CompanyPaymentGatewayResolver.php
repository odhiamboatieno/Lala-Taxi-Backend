<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\SaaSDispatch\Entities\CompanyPaymentGateway;

class CompanyPaymentGatewayResolver
{
    public function __construct(protected CompanyConfigResolver $companyConfigResolver)
    {
    }

    public function shouldUseCompanyGateway(?int $companyId): bool
    {
        // The platform-wide `wallet_payment_owner` site setting is the
        // primary gate.  When the admin owns every gateway we never return
        // tenant credentials – even if a tenant context is available.
        if (
            function_exists('wallet_payment_owner')
            && wallet_payment_owner() !== 'company'
        ) {
            return false;
        }

        if (! $companyId || ! \saas_dispatch_enabled() || $companyId === \saas_platform_company_id()) {
            return false;
        }

        $config = $this->companyConfigResolver->forCompany($companyId);

        return (bool) ($config['is_saas_enabled'] ?? false);
    }

    public function collectionForCompany(int $companyId): Collection
    {
        $global = PaymentGateway::query()->get()->keyBy(fn ($item) => $item->site.'::'.$item->name);

        if (! $this->shouldUseCompanyGateway($companyId) || ! \Schema::hasTable('company_payment_gateways')) {
            return $global->values();
        }

        $overrides = CompanyPaymentGateway::query()
            ->where('company_id', $companyId)
            ->get()
            ->keyBy(fn ($item) => $item->site.'::'.$item->name);

        $merged = $global->map(function ($item, $key) use ($overrides) {
            return $overrides->get($key) ?: $item;
        });

        foreach ($overrides as $key => $item) {
            if (! $merged->has($key)) {
                $merged->put($key, $item);
            }
        }

        return $merged->values();
    }

    public function value(int $companyId, string $key, string $site, mixed $default = '')
    {
        if (! $this->shouldUseCompanyGateway($companyId) || ! \Schema::hasTable('company_payment_gateways')) {
            return null;
        }

        $gateway = CompanyPaymentGateway::query()
            ->where('company_id', $companyId)
            ->where('name', $key)
            ->where('site', $site)
            ->first();

        return $gateway?->value ?? $default;
    }

    public function enabledSites(int $companyId): Collection
    {
        return $this->collectionForCompany($companyId)
            ->where('site', '!=', 'Cash')
            ->where('name', 'is_enabled')
            ->where('value', '1')
            ->pluck('site');
    }

    public function syncFromRequest(int $companyId, Request $request): void
    {
        $values = [
            ['site' => 'Paypal', 'name' => 'is_enabled', 'value' => $request->paypal_enabled ?? '0'],
            ['site' => 'Paypal', 'name' => 'paypal_id', 'value' => $request->paypal_id ?? ''],
            ['site' => 'Paypal', 'name' => 'mode', 'value' => $request->paypal_mode ?? 'sandbox'],
            ['site' => 'Paypal', 'name' => 'client', 'value' => $request->paypal_client ?? ''],
            ['site' => 'Paypal', 'name' => 'secret', 'value' => $request->paypal_secret ?? ''],
            ['site' => 'Paypal', 'name' => 'access_token', 'value' => $request->paypal_access_token ?? ''],

            ['site' => 'Stripe', 'name' => 'is_enabled', 'value' => $request->stripe_enabled ?? '0'],
            ['site' => 'Stripe', 'name' => 'publish', 'value' => $request->stripe_publish_key ?? ''],
            ['site' => 'Stripe', 'name' => 'secret', 'value' => $request->stripe_secret_key ?? ''],
            ['site' => 'Stripe', 'name' => 'api_version', 'value' => $request->stripe_api_version ?? '2019-12-03'],

            ['site' => 'Paytm', 'name' => 'is_enabled', 'value' => $request->paytm_enabled ?? '0'],
            ['site' => 'Paytm', 'name' => 'paytm_merchant', 'value' => $request->paytm_merchant ?? ''],
            ['site' => 'Paytm', 'name' => 'paytm_secret', 'value' => $request->paytm_secret ?? ''],
            ['site' => 'Paytm', 'name' => 'mode', 'value' => $request->paytm_mode ?? 'sandbox'],

            ['site' => 'Flutterwave', 'name' => 'is_enabled', 'value' => $request->flutterwave_enabled ?? '0'],
            ['site' => 'Flutterwave', 'name' => 'mode', 'value' => $request->flutterwave_mode ?? 'sandbox'],
            ['site' => 'Flutterwave', 'name' => 'public_key', 'value' => $request->flutterwave_public_key ?? ''],
            ['site' => 'Flutterwave', 'name' => 'secret_key', 'value' => $request->flutterwave_secret_key ?? ''],
            ['site' => 'Flutterwave', 'name' => 'encryption_key', 'value' => $request->flutterwave_encryption_key ?? ''],

            ['site' => 'Mpesa', 'name' => 'is_enabled', 'value' => $request->mpesa_enabled ?? '0'],
            ['site' => 'Mpesa', 'name' => 'mode', 'value' => $request->mpesa_mode ?? 'sandbox'],
            ['site' => 'Mpesa', 'name' => 'consumer_key', 'value' => $request->consumer_key ?? ''],
            ['site' => 'Mpesa', 'name' => 'consumer_secret', 'value' => $request->consumer_secret ?? ''],
            ['site' => 'Mpesa', 'name' => 'callback_url', 'value' => $request->callback_url ?? ''],
            ['site' => 'Mpesa', 'name' => 'business_shortcode', 'value' => $request->business_shortcode ?? ''],

            ['site' => 'Mollie', 'name' => 'is_enabled', 'value' => $request->mollie_enabled ?? '0'],
            ['site' => 'Mollie', 'name' => 'api_key', 'value' => $request->mollie_key ?? ''],
            ['site' => 'Mollie', 'name' => 'partner_id', 'value' => $request->mollie_partner ?? ''],
            ['site' => 'Mollie', 'name' => 'profile_id', 'value' => $request->mollie_profile ?? ''],
            ['site' => 'Mollie', 'name' => 'mode', 'value' => $request->mollie_mode ?? 'sandbox'],

            ['site' => 'Braintree', 'name' => 'is_enabled', 'value' => $request->bt_enabled ?? '0'],
            ['site' => 'Braintree', 'name' => 'mode', 'value' => $request->bt_mode ?? 'sandbox'],
            ['site' => 'Braintree', 'name' => 'merchant_id', 'value' => $request->bt_merchant_id ?? ''],
            ['site' => 'Braintree', 'name' => 'public_key', 'value' => $request->bt_public_key ?? ''],
            ['site' => 'Braintree', 'name' => 'private_key', 'value' => $request->bt_private_key ?? ''],
            ['site' => 'Braintree', 'name' => 'tokenization_key', 'value' => $request->tokenization_key ?? ''],
            ['site' => 'Braintree', 'name' => 'merchant_account_id', 'value' => $request->merchant_account_id ?? ''],

            ['site' => 'Common', 'name' => 'is_web_payment', 'value' => $request->has('is_web_payment') ? '1' : '0'],
            ['site' => 'Common', 'name' => 'payout_methods', 'value' => implode(',', $request->payout_methods ?? [])],
        ];

        foreach ($values as $value) {
            CompanyPaymentGateway::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'site' => $value['site'],
                    'name' => $value['name'],
                ],
                ['value' => (string) $value['value']]
            );
        }
    }
}
