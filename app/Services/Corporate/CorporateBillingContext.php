<?php

namespace App\Services\Corporate;

use App\Models\CarType;
use App\Models\Request as RideRequest;
use App\Models\ScheduleRide;
use App\Models\Trips;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

class CorporateBillingContext
{
    public const FIELDS = ['billing_mode', 'corporate_account_id', 'billing_snapshot'];

    public function resolveForBooking(User $rider, array $payload = []): array
    {
        $intent = $payload['billing_mode'] ?? null;
        $method = strtolower((string) ($payload['payment_mode'] ?? '').' '.($payload['payment_method'] ?? ''));
        if ($intent !== null && !in_array($intent, ['personal', 'corporate'], true)) {
            $this->invalid();
        }
        if ($intent === 'personal' && str_contains($method, 'corporate')) {
            $this->invalid();
        }
        $corporate = $intent === 'corporate' || str_contains($method, 'corporate') || ($intent === null && $rider->corporate_account_id);
        if (!$corporate) {
            if (!empty($payload['corporate_account_id'])) {
                $this->invalid();
            }

            return ['billing_mode' => 'personal', 'corporate_account_id' => null, 'billing_snapshot' => null];
        }
        $account = CompanyCorporateAccount::query()->when(DB::transactionLevel() > 0, fn ($q) => $q->lockForUpdate())->find($rider->corporate_account_id);
        if (DB::transactionLevel() > 0 && $account) {
            $rider = User::query()->lockForUpdate()->findOrFail($rider->id);
        }
        if (!$account || $rider->user_type !== 'Rider' || $rider->status !== 'Active'
            || $account->status !== 'Active' || $account->archived_at
            || !$account->currency_code || !$account->legal_name || !$account->billing_address
            || !\App\Models\Currency::where('code', $account->currency_code)->where('status', 'Active')->exists()
            || !app(CorporateCapabilities::class)->newBookingsEnabled($account->company)
            || ($payload['corporate_account_id'] ?? $account->id) != $account->id
            || (int) $rider->corporate_account_id !== (int) $account->id
            || !in_array($rider->corporate_role, ['member', 'manager', 'finance'], true)) {
            $this->invalid();
        }
        if (str_contains($method, 'wallet') || strtolower((string) ($payload['is_wallet'] ?? 'no')) === 'yes') {
            $this->invalid('corporate.no_wallet');
        }
        $currency = $payload['currency_code'] ?? $rider->currency_code;
        if ($currency !== $account->currency_code || ($payload['service_category'] ?? 'ride') !== 'ride') {
            $this->invalid('corporate.unsupported_currency_service');
        }
        if (!empty($payload['car_id'])) {
            $vehicle = CarType::find($payload['car_id']);
            if ($vehicle && $vehicle->is_pool === 'Yes') {
                $this->invalid('corporate.unsupported_currency_service');
            }
        }

        return [
            'billing_mode' => 'corporate',
            'corporate_account_id' => $account->id,
            'billing_snapshot' => [
                'account_name' => $account->account_name,
                'company_id' => (int) $account->company_id,
                'currency_code' => $account->currency_code,
                'legal_name' => $account->legal_name,
                'billing_address' => $account->billing_address,
                'contact_email' => $account->contact_email,
                'billing_timezone' => $account->billing_timezone,
                'payment_terms_days' => $account->payment_terms_days,
            ],
        ];
    }

    /** Server-side propagation catches every Eloquent schedule/request/accept path. */
    public function prepareCreation(Model $model): void
    {
        if ($model instanceof Trips) {
            $source = $model->request_id ? RideRequest::find($model->request_id) : null;
            if ($source) {
                $this->copyPending($source, $model);
            } elseif ($model->billing_mode === 'corporate' || $model->corporate_account_id || str_contains(strtolower((string) $model->payment_mode), 'corporate')) {
                $this->invalid();
            }

            return;
        }
        $rider = User::findOrFail($model->user_id);
        $source = null;
        if ($model instanceof RideRequest) {
            if (is_numeric($model->schedule_id) && (int) $model->schedule_id > 0) {
                $source = ScheduleRide::findOrFail($model->schedule_id);
            } elseif ($model->group_id) {
                $source = RideRequest::where('group_id', $model->group_id)->orderBy('id')->first();
            }
        }
        if ($source) {
            $this->copyPending($source, $model);
        } else {
            $context = $this->resolveForBooking($rider, $model->getAttributes());
            // Old personal fixtures/installations without corporate columns remain untouched.
            if ($context['billing_mode'] === 'corporate' || $model->billing_mode !== null) {
                $this->apply($model, $context);
            }
        }
    }

    public function copyPending(Model $source, Model $target): void
    {
        if ((int) $source->user_id !== (int) $target->user_id) {
            $this->invalid();
        }
        if (!$source->isCorporate()) {
            // A stored personal schedule/offer never changes payer when membership changes later.
            if ($target->billing_mode === 'corporate' || $target->corporate_account_id
                || str_contains(strtolower((string) ($target->payment_mode ?? $target->payment_method)), 'corporate')) {
                $this->invalid();
            }
            $this->apply($target, $this->attributes($source));
            return;
        }
        $this->validatePending($source);
        $this->apply($target, $this->attributes($source));
        if ($target instanceof Trips) {
            $target->billing_status = 'pending';
        }
    }

    public function validatePending(Model $source): void
    {
        if (!$source->isCorporate()) {
            return;
        }
        $rider = User::findOrFail($source->user_id);
        $context = $this->resolveForBooking($rider, array_merge($source->getAttributes(), [
            'billing_mode' => 'corporate',
            'corporate_account_id' => $source->corporate_account_id,
            'currency_code' => data_get($source->billing_snapshot, 'currency_code'),
        ]));
        if ((int) $context['corporate_account_id'] !== (int) $source->corporate_account_id
            || $rider->currency_code !== data_get($source->billing_snapshot, 'currency_code')) {
            $this->invalid();
        }
    }

    public function apply(Model $model, array $context): void
    {
        foreach (self::FIELDS as $field) {
            $model->{$field} = $context[$field];
        }
        if ($context['billing_mode'] === 'corporate') {
            $field = $model instanceof ScheduleRide ? 'payment_method' : 'payment_mode';
            $model->{$field} = 'Corporate';
            if ($model instanceof ScheduleRide) {
                $model->is_wallet = 'No';
            }
            if ($model instanceof Trips) {
                $model->currency_code = $context['billing_snapshot']['currency_code'];
            }
        }
    }

    public function attributes(Model $model): array
    {
        return ['billing_mode' => $model->billing_mode ?? 'personal', 'corporate_account_id' => $model->corporate_account_id, 'billing_snapshot' => $model->billing_snapshot];
    }

    /** Serialize acceptance across all dispatch groups for the same rider. */
    public function assertCanAccept(Model $source): void
    {
        $this->validatePending($source);
        User::query()->lockForUpdate()->findOrFail($source->user_id);
        if (Trips::query()->where('user_id', $source->user_id)
            ->when($source instanceof Trips, fn ($query) => $query->where('id', '!=', $source->id))
            ->whereIn('status', array_merge(\App\Support\TripWorkflow::activeStatuses(), ['Scheduled', 'Begin trip', 'End trip']))->exists()) {
            throw ValidationException::withMessages(['trip' => __('corporate.active_trip')]);
        }
    }

    public function recordTrip(Trips $trip): void
    {
        if ($trip->isCorporate()) {
            DB::table('company_corporate_trip_records')->insert([
                'trip_id' => $trip->id, 'corporate_account_id' => $trip->corporate_account_id, 'created_at' => now(),
            ]);
        }
    }

    public function guardMutation(Model $model): void
    {
        $corporate = $model->getRawOriginal('billing_mode') === 'corporate';
        if ($corporate && $model instanceof Trips && $model->getRawOriginal('billing_finalized_at')
            && $model->isDirty(array_merge(CorporateTripFinalizer::FINANCIAL_FIELDS, [
                'corporate_amount', 'corporate_currency_code', 'corporate_financial_snapshot', 'billing_finalized_at',
                'payment_status', 'billing_status', 'status', 'is_calculation', 'completed_at', 'end_trip',
                'cash_collected', 'driver_confirmed_cash', 'paykey',
            ]))) {
            $this->invalid('corporate.immutable_charge');
        }
        if ($model instanceof ScheduleRide && $model->isDirty(['user_id', 'payment_method', 'schedule_date', 'schedule_time', 'scheduled_at'])) {
            $this->validatePending($model);
            if ($model->assignment_status === 'billing_blocked') {
                $model->assignment_status = 'unassigned';
                $model->billing_blocked_at = null;
                $model->billing_blocked_reason = null;
            }
        }
        if ($model->isDirty(self::FIELDS) || ($corporate && $model->isDirty('user_id'))) {
            throw ValidationException::withMessages(['billing_mode' => __('corporate.immutable_payer')]);
        }
        $paymentField = $model instanceof ScheduleRide ? 'payment_method' : 'payment_mode';
        if ($corporate && $model instanceof Trips && $model->isDirty('currency_code')) {
            $this->invalid('corporate.immutable_payer');
        }
        if ($corporate && $model->isDirty($paymentField) && $model->{$paymentField} !== 'Corporate') {
            $this->invalid('corporate.immutable_payer');
        }
    }

    public function summary(Model $model): array
    {
        $corporate = $model->isCorporate();

        return [
            'mode' => $corporate ? 'corporate' : 'personal',
            'account_name' => $corporate ? data_get($model->billing_snapshot, 'account_name') : null,
            'label_key' => $corporate ? 'corporate.billed_to_company' : null,
            'collection_required' => !$corporate,
            'status' => $corporate ? ($model->billing_status ?? 'pending') : null,
            'currency_code' => $corporate ? data_get($model->billing_snapshot, 'currency_code') : ($model->getRawOriginal('currency_code') ?: null),
        ];
    }

    private function invalid(string $key = 'corporate.booking_unavailable'): never
    {
        throw ValidationException::withMessages(['billing_mode' => __($key)]);
    }
}
