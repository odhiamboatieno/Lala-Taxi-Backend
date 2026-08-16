<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Services\CompanyPaymentGatewayResolver;

class CompanyPaymentGatewayController extends Controller
{
    public function __construct(protected CompanyPaymentGatewayResolver $resolver)
    {
    }

    /**
     * Ensure the request is being made by an authenticated company user.
     * Companies can never access admin credentials, other companies'
     * gateways, or modify another tenant's payment configuration.
     */
    protected function ensureCompanyContext(): int
    {
        $companyId = (int) auth('company')->id();
        if (! $companyId) {
            abort(403, 'Company authentication required.');
        }

        return $companyId;
    }

    public function editSelf()
    {
        $companyId = $this->ensureCompanyContext();
        set_payment_gateway_company_context($companyId);

        return view('admin.payment_gateway', [
            'paymentGatewayFormAction' => route('saasdispatch.self.payment-gateways.update'),
            'paymentGatewayHeading' => 'Company Payment Gateways',
        ]);
    }

    public function updateSelf(Request $request)
    {
        $companyId = $this->ensureCompanyContext();
        set_payment_gateway_company_context($companyId);

        // The platform owner must have enabled the company mode before
        // companies can save their own credentials.  This protects
        // deployments that intentionally keep the admin in full control.
        if (! function_exists('wallet_payment_owner') || wallet_payment_owner() !== 'company') {
            flashMessage('danger', 'Per-company payment gateways are disabled.  Enable "Company" mode in Admin Site Settings first.');
            return back();
        }

        if (($request->stripe_enabled ?? '0') === '0' && ($request->bt_enabled ?? '0') === '0' && ($request->paypal_enabled ?? '0') === '0' && ($request->paytm_enabled ?? '0') === '0' && ($request->flutterwave_enabled ?? '0') === '0' && ($request->mpesa_enabled ?? '0') === '0' && ($request->mollie_enabled ?? '0') === '0') {
            flashMessage('danger', 'Please enable at least one Payment Gateway');
            return back();
        }

        if (empty($request->payout_methods)) {
            flashMessage('danger', 'At least one payout method should be enabled.');
            return back();
        }

        if (($request->stripe_enabled ?? '0') === '1' && ($request->bt_enabled ?? '0') === '1') {
            flashMessage('danger', 'Please Choose either Braintree or Stripe for Card Payments');
            return back();
        }

        $rules = [];
        if ($request->paypal_enabled) {
            $rules += ['paypal_id' => 'required', 'paypal_mode' => 'required', 'paypal_client' => 'required', 'paypal_secret' => 'required', 'paypal_access_token' => 'required'];
        }
        if ($request->stripe_enabled) {
            $rules += ['stripe_publish_key' => 'required', 'stripe_secret_key' => 'required', 'stripe_api_version' => 'required'];
        }
        if ($request->paytm_enabled) {
            $rules += ['paytm_merchant' => 'required', 'paytm_secret' => 'required', 'paytm_mode' => 'required'];
        }
        if ($request->flutterwave_enabled) {
            $rules += ['flutterwave_public_key' => 'required', 'flutterwave_secret_key' => 'required', 'flutterwave_encryption_key' => 'required', 'flutterwave_mode' => 'required'];
        }
        if ($request->mpesa_enabled) {
            $rules += ['mpesa_mode' => 'required', 'consumer_key' => 'required', 'consumer_secret' => 'required', 'callback_url' => 'required', 'business_shortcode' => 'required'];
        }
        if ($request->mollie_enabled) {
            $rules += ['mollie_key' => 'required', 'mollie_partner' => 'required', 'mollie_profile' => 'required', 'mollie_mode' => 'required'];
        }
        if ($request->bt_enabled) {
            $rules += ['bt_mode' => 'required', 'bt_merchant_id' => 'required', 'bt_public_key' => 'required', 'bt_private_key' => 'required', 'tokenization_key' => 'required'];
        }

        $this->validate($request, $rules);

        // Tenant isolation: always pass the authenticated company id so the
        // resolver cannot be tricked into writing rows for another tenant.
        $this->resolver->syncFromRequest($companyId, $request);

        flashMessage('success', 'Updated Successfully');

        return redirect()->route('saasdispatch.self.payment-gateways.edit');
    }
}
