<?php

namespace App\Services\Dispatch;

use Illuminate\Validation\ValidationException;

class ManualBookingPayment
{
    /** Manual dispatch has no card credential or wallet-consent collection yet. */
    public function resolve(?string $method): array
    {
        $method = $method ?: 'Cash';
        if ($method !== 'Cash') {
            throw ValidationException::withMessages(['payment_method' => __('corporate.unsupported_manual_payment')]);
        }

        return ['payment_method' => $method, 'is_wallet' => 'No'];
    }
}
