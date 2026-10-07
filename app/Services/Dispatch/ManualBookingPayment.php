<?php

namespace App\Services\Dispatch;

use App\Models\User;
use App\Services\Corporate\CorporateBillingContext;
use Illuminate\Validation\ValidationException;

class ManualBookingPayment
{
    /** Manual dispatch has no card credential or wallet-consent collection yet. */
    public function resolve(?string $method, ?User $rider = null): array
    {
        $method = $this->intent($method);
        if ($method === 'Corporate' && !$rider) {
            throw ValidationException::withMessages(['payment_method' => __('corporate.booking_unavailable')]);
        }
        if ($rider) {
            $context = app(CorporateBillingContext::class)->resolveForBooking($rider, ['payment_method' => $method]);
            if ($context['billing_mode'] === 'corporate') {
                $method = 'Corporate';
            }
        }

        return ['payment_method' => $method, 'is_wallet' => 'No'];
    }

    /** Drafts retain intent only; eligibility is revalidated against the actual rider on booking. */
    public function intent(?string $method): string
    {
        $method = $method ?: 'Cash';
        if (!in_array($method, ['Cash', 'Corporate'], true)) {
            throw ValidationException::withMessages(['payment_method' => __('corporate.unsupported_manual_payment')]);
        }

        return $method;
    }
}
