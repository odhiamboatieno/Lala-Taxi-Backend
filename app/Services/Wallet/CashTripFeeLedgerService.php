<?php

namespace App\Services\Wallet;

use App\Models\Trips;
use App\Models\Wallet;
use Illuminate\Support\Facades\Schema;

class CashTripFeeLedgerService
{
    public function debitDriverFees(Trips $trip): float
    {
        if (! $this->isCashTrip($trip) || ! $trip->driver_id) {
            return 0.0;
        }

        if (Schema::hasColumn('trips', 'cash_fees_debited_at') && $trip->cash_fees_debited_at) {
            return 0.0;
        }

        $debitAmount = $this->cashFeeAmount($trip);
        if ($debitAmount <= 0) {
            $this->markDebited($trip);
            return 0.0;
        }

        $wallet = Wallet::firstOrNew(['user_id' => $trip->driver_id]);
        $wallet->currency_code = $wallet->currency_code ?: $trip->currency_code;
        $wallet->amount = round(((float) ($wallet->getOriginal('amount') ?? 0)) - $debitAmount, 2);
        $wallet->save();

        $this->markDebited($trip);

        return $debitAmount;
    }

    public function cashFeeAmount(Trips $trip): float
    {
        return round(
            (float) $trip->access_fee
            + (float) ($trip->platform_driver_fee_amount ?? 0)
            + (float) ($trip->company_commission_amount ?? 0)
            + (float) ($trip->company_driver_fee_amount ?? 0),
            2
        );
    }

    protected function isCashTrip(Trips $trip): bool
    {
        return in_array(strtolower((string) $trip->payment_mode), ['cash', 'cash & wallet'], true);
    }

    protected function markDebited(Trips $trip): void
    {
        if (! Schema::hasColumn('trips', 'cash_fees_debited_at')) {
            return;
        }

        Trips::query()
            ->whereKey($trip->id)
            ->whereNull('cash_fees_debited_at')
            ->update(['cash_fees_debited_at' => now()]);
    }
}
