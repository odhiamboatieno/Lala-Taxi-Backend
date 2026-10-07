<?php

namespace App\Services\Corporate;

use App\Models\Payment;
use App\Models\Trips;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;

class CorporateTripFinalizer
{
    public const FINANCIAL_FIELDS = [
        'total_fare', 'subtotal_fare', 'driver_payout', 'base_fare', 'time_fare', 'distance_fare',
        'access_fee', 'schedule_fare', 'peak_amount', 'driver_peak_amount', 'extra_fare_amount',
        'waiting_charge', 'tips', 'toll_fee', 'additional_rider_amount', 'driver_or_company_commission',
        'platform_driver_fee_amount', 'company_commission_amount', 'company_driver_fee_amount',
        'applied_owe_amount', 'owe_amount', 'wallet_amount', 'promo_amount', 'currency_code',
    ];

    public function finalize(Trips $candidate): Trips
    {
        return DB::transaction(function () use ($candidate) {
            $account = CompanyCorporateAccount::query()->lockForUpdate()->findOrFail($candidate->corporate_account_id);
            $trip = Trips::query()->lockForUpdate()->findOrFail($candidate->id);
            if (!$trip->isCorporate() || (int) $trip->corporate_account_id !== (int) $account->id
                || (int) data_get($trip->billing_snapshot, 'company_id') !== (int) $account->company_id) {
                $this->invalid();
            }
            if ($trip->billing_finalized_at) {
                return $trip;
            }
            if ((int) $trip->is_calculation !== 1 || !$trip->completed_at || !$trip->end_trip
                || !in_array(strtolower((string) $trip->getRawOriginal('status')), ['payment', 'rating', 'completed', 'end trip', 'end_trip'], true)
                || $trip->getRawOriginal('currency_code') !== data_get($trip->billing_snapshot, 'currency_code')
                || (float) $trip->getRawOriginal('wallet_amount') !== 0.0 || (float) $trip->getRawOriginal('promo_amount') !== 0.0
                || (float) $trip->getRawOriginal('owe_amount') !== 0.0 || $trip->payment_mode !== 'Corporate') {
                $this->invalid();
            }
            $amount = CorporateMoney::decimal(CorporateMoney::minor((string) $trip->getRawOriginal('total_fare')));
            $payments = Payment::where('trip_id', $trip->id)->lockForUpdate()->get();
            if ($payments->count() > 1 || ($payments->isNotEmpty() && (
                $payments->first()->correlation_id || $payments->first()->admin_transaction_id || $payments->first()->driver_transaction_id
                || $payments->first()->driver_payout_status !== 'Pending' || $payments->first()->admin_payout_status !== 'Pending'))) {
                throw ValidationException::withMessages(['trip' => __('corporate.reconcile_payment')]);
            }
            $payment = $payments->first() ?? Payment::create([
                'trip_id' => $trip->id, 'driver_payout_status' => 'Pending', 'admin_payout_status' => 'Pending',
            ]);
            $record = DB::table('company_corporate_trip_records')->where('trip_id', $trip->id)->lockForUpdate()->first();
            if (!$record || ($record->payment_id && (int) $record->payment_id !== (int) $payment->id)) {
                $this->invalid();
            }
            DB::table('company_corporate_trip_records')->where('trip_id', $trip->id)->update(['payment_id' => $payment->id]);
            $snapshot = array_intersect_key($trip->getRawOriginal(), array_flip(self::FINANCIAL_FIELDS));
            DB::table('trips')->where('id', $trip->id)->update([
                'status' => 'Completed', 'payment_status' => 'Deferred', 'billing_status' => 'unbilled',
                'corporate_amount' => $amount, 'corporate_currency_code' => $trip->getRawOriginal('currency_code'),
                'corporate_financial_snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR), 'billing_finalized_at' => now('UTC'),
            ]);
            CompanyCorporateAudit::create([
                'corporate_account_id' => $account->id, 'action' => 'trip.finalized', 'actor' => 'system',
                'reference' => 'trip:'.$trip->id, 'data' => ['amount' => $amount, 'currency_code' => $trip->getRawOriginal('currency_code')], 'created_at' => now('UTC'),
            ]);
            DB::afterCommit(fn () => app(\App\Services\Rating\DriverRatingEligibilityService::class)->emitIfEligible($trip->fresh()));

            return $trip->fresh();
        }, 3);
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['trip' => __('corporate.finalization_invalid')]);
    }
}
