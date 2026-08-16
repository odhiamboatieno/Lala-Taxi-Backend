<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TripOperations
{
    protected static ?array $schemaSupport = null;

    public static function schemaSupport(): array
    {
        if (self::$schemaSupport !== null) {
            return self::$schemaSupport;
        }

        self::$schemaSupport = [
            'cancel_table' => Schema::hasTable('cancel'),
            'cancel_reasons_table' => Schema::hasTable('cancel_reasons'),
            'cancel_cancel_reason_id' => Schema::hasColumn('cancel', 'cancel_reason_id'),
            'cancel_cancel_comments' => Schema::hasColumn('cancel', 'cancel_comments'),
            'cancel_reasons_reason' => Schema::hasColumn('cancel_reasons', 'reason'),
            'trips_company_id' => Schema::hasColumn('trips', 'company_id'),
            'trips_payment_status' => Schema::hasColumn('trips', 'payment_status'),
            'trips_operator_type' => Schema::hasColumn('trips', 'operator_type'),
            'trips_operator_id' => Schema::hasColumn('trips', 'operator_id'),
            'trips_company_vehicle_type_id' => Schema::hasColumn('trips', 'company_vehicle_type_id'),
            'trips_global_vehicle_category_id' => Schema::hasColumn('trips', 'global_vehicle_category_id'),
            'trips_completed_at' => Schema::hasColumn('trips', 'completed_at'),
            'trips_started_at' => Schema::hasColumn('trips', 'started_at'),
            'trips_cancelled_at' => Schema::hasColumn('trips', 'cancelled_at'),
            'trips_request_id' => Schema::hasColumn('trips', 'request_id'),
            'schedule_ride_booking_mode' => Schema::hasColumn('schedule_ride', 'booking_mode'),
            'schedule_ride_dispatch_mode' => Schema::hasColumn('schedule_ride', 'dispatch_mode'),
            'schedule_ride_scheduled_at' => Schema::hasColumn('schedule_ride', 'scheduled_at'),
            'request_location_id' => Schema::hasColumn('request', 'location_id'),
        ];

        return self::$schemaSupport;
    }

    public static function baseQuery(?string $loginUserType = null): Builder
    {
        $loginUserType = $loginUserType ?: (defined('LOGIN_USER_TYPE') ? LOGIN_USER_TYPE : 'admin');
        $schema = self::schemaSupport();

        $query = DB::table('trips')
            ->leftJoin('users as rider', 'rider.id', '=', 'trips.user_id')
            ->leftJoin('users as driver', 'driver.id', '=', 'trips.driver_id')
            ->leftJoin('companies', 'companies.id', '=', 'driver.company_id')
            ->leftJoin('currency', 'currency.code', '=', 'trips.currency_code')
            ->leftJoin('car_type', 'car_type.id', '=', 'trips.car_id')
            ->leftJoin('payment', 'payment.trip_id', '=', 'trips.id');

        if ($schema['cancel_table']) {
            $query->leftJoin('cancel', 'cancel.trip_id', '=', 'trips.id');

            if ($schema['cancel_reasons_table'] && $schema['cancel_cancel_reason_id']) {
                $query->leftJoin('cancel_reasons', 'cancel_reasons.id', '=', 'cancel.cancel_reason_id');
            }
        }

        if ($schema['trips_request_id']) {
            $query->leftJoin('request as ride_request', 'ride_request.id', '=', 'trips.request_id')
                ->leftJoin('schedule_ride', 'schedule_ride.id', '=', 'ride_request.schedule_id');
        }

        if ($loginUserType === 'company') {
            $companyId = Auth::guard('company')->id();

            $query->where(function (Builder $scopedQuery) use ($schema, $companyId) {
                if ($schema['trips_company_id']) {
                    $scopedQuery->where('trips.company_id', $companyId)
                        ->orWhere('driver.company_id', $companyId);
                } else {
                    $scopedQuery->where('driver.company_id', $companyId);
                }
            });
        }

        return $query;
    }

    public static function applyFilters(Builder $query, Request $request, ?array $schema = null): Builder
    {
        $schema = $schema ?: self::schemaSupport();
        $status = $request->get('status');
        $quickFilter = $request->get('quick_filter');

        if ($status && $status !== 'all') {
            self::applyStatusToken($query, $status, $schema);
        } elseif ($quickFilter) {
            self::applyQuickFilter($query, $quickFilter, $schema);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('trips.created_at', [
                $request->get('from').' 00:00:00',
                $request->get('to').' 23:59:59',
            ]);
        } elseif ($request->filled('from')) {
            $query->where('trips.created_at', '>=', $request->get('from').' 00:00:00');
        } elseif ($request->filled('to')) {
            $query->where('trips.created_at', '<=', $request->get('to').' 23:59:59');
        }

        if ($request->filled('payment_method')) {
            $query->where('trips.payment_mode', $request->get('payment_method'));
        }

        if ($schema['trips_payment_status'] && $request->filled('payment_status')) {
            $query->where('trips.payment_status', $request->get('payment_status'));
        }

        if ($request->filled('company_id')) {
            if ($schema['trips_company_id']) {
                $query->where('trips.company_id', $request->integer('company_id'));
            } else {
                $query->where('driver.company_id', $request->integer('company_id'));
            }
        }

        if ($request->filled('driver_id')) {
            $query->where('trips.driver_id', $request->integer('driver_id'));
        }

        if ($request->filled('rider_id')) {
            $query->where('trips.user_id', $request->integer('rider_id'));
        }

        if ($request->filled('vehicle_type_id')) {
            $query->where('trips.car_id', $request->integer('vehicle_type_id'));
        }

        if ($schema['request_location_id'] && $request->filled('location_id')) {
            $query->where('ride_request.location_id', $request->integer('location_id'));
        }

        if ($request->filled('pickup_zone')) {
            $query->where('trips.pickup_location', 'like', '%'.$request->get('pickup_zone').'%');
        }

        if ($request->filled('fare_min')) {
            $query->where('trips.total_fare', '>=', (float) $request->get('fare_min'));
        }

        if ($request->filled('fare_max')) {
            $query->where('trips.total_fare', '<=', (float) $request->get('fare_max'));
        }

        if ($request->filled('trip_type')) {
            $tripType = $request->get('trip_type');

            if ($tripType === 'instant') {
                $query->where(function (Builder $typeQuery) {
                    $typeQuery->whereNull('ride_request.schedule_id')
                        ->orWhere('ride_request.schedule_id', 'Null')
                        ->orWhere('ride_request.schedule_id', 0);
                });
            } elseif ($tripType === 'manual') {
                $query->where('schedule_ride.booking_type', 'Manual Booking');
            } elseif ($tripType === 'scheduled') {
                $query->where(function (Builder $typeQuery) use ($schema) {
                    $typeQuery->where('schedule_ride.booking_type', 'Schedule Booking');
                    if ($schema['schedule_ride_booking_mode']) {
                        $typeQuery->orWhere('schedule_ride.booking_mode', 'scheduled');
                    }
                });
            }
        }

        if ($schema['schedule_ride_dispatch_mode'] && $request->filled('dispatch_mode')) {
            $query->where('schedule_ride.dispatch_mode', $request->get('dispatch_mode'));
        }

        if ($schema['trips_operator_type'] && $request->filled('operator_type')) {
            $query->where('trips.operator_type', $request->get('operator_type'));
        }

        if ($request->filled('cancellation_reason') && $schema['cancel_table']) {
            $search = $request->get('cancellation_reason');

            $query->where(function (Builder $cancelQuery) use ($schema, $search) {
                if ($schema['cancel_reasons_table'] && $schema['cancel_reasons_reason']) {
                    $cancelQuery->where('cancel_reasons.reason', 'like', '%'.$search.'%');
                }

                if ($schema['cancel_cancel_comments']) {
                    $method = ($schema['cancel_reasons_table'] && $schema['cancel_reasons_reason']) ? 'orWhere' : 'where';
                    $cancelQuery->{$method}('cancel.cancel_comments', 'like', '%'.$search.'%');
                }
            });
        }

        return $query;
    }

    public static function cancellationReasonExpression(?array $schema = null): string
    {
        $schema = $schema ?: self::schemaSupport();
        $parts = [];

        if ($schema['cancel_reasons_table'] && $schema['cancel_reasons_reason']) {
            $parts[] = "NULLIF(cancel_reasons.reason, '')";
        }

        if ($schema['cancel_cancel_comments']) {
            $parts[] = "NULLIF(cancel.cancel_comments, '')";
        }

        if (empty($parts)) {
            return "'Unspecified'";
        }

        return 'COALESCE('.implode(', ', $parts).", 'Unspecified')";
    }

    public static function applyStatusToken(Builder $query, string $token, ?array $schema = null): Builder
    {
        $schema = $schema ?: self::schemaSupport();

        switch ($token) {
            case 'pending':
                $query->whereIn('trips.status', self::pendingStatuses());
                break;

            case 'live':
            case 'ongoing':
                $query->whereIn('trips.status', self::liveStatuses());
                break;

            case 'completed':
                $query->whereIn('trips.status', self::completedStatuses());
                break;

            case 'cancelled':
                $query->whereIn('trips.status', self::cancelledStatuses());
                break;

            case 'failed':
            case 'no_driver':
                $query->whereIn('trips.status', self::failedStatuses());
                break;

            case 'payment_issues':
                if ($schema['trips_payment_status']) {
                    $query->whereIn('trips.status', self::completedStatuses())
                        ->where(function (Builder $paymentQuery) {
                            $paymentQuery->whereNull('trips.payment_status')
                                ->orWhere('trips.payment_status', '!=', 'Completed');
                        });
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
        }

        return $query;
    }

    public static function applyQuickFilter(Builder $query, string $quickFilter, ?array $schema = null): Builder
    {
        $schema = $schema ?: self::schemaSupport();

        switch ($quickFilter) {
            case 'today':
                $query->whereDate('trips.created_at', today());
                break;
            case 'yesterday':
                $query->whereDate('trips.created_at', today()->subDay());
                break;
            case 'this_week':
                $query->whereBetween('trips.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('trips.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'scheduled_today':
                if ($schema['schedule_ride_scheduled_at']) {
                    $query->whereDate('schedule_ride.scheduled_at', today());
                } else {
                    $query->whereDate(DB::raw("CONCAT(schedule_ride.schedule_date, ' ', schedule_ride.schedule_time)"), today());
                }
                break;
            default:
                self::applyStatusToken($query, $quickFilter, $schema);
                break;
        }

        return $query;
    }

    public static function completedStatuses(): array
    {
        return ['Completed', 'completed', 'Payment', 'Rating'];
    }

    public static function cancelledStatuses(): array
    {
        return ['Cancelled', 'cancelled', 'Trip Cancelled'];
    }

    public static function failedStatuses(): array
    {
        return ['Car Not Found', 'No Driver Found', 'no_driver_found', 'Failed'];
    }

    public static function pendingStatuses(): array
    {
        return ['Pending', 'Searching', 'requested', 'searching_driver', 'driver_assigned'];
    }

    public static function liveStatuses(): array
    {
        return array_merge(self::pendingStatuses(), ['Accepted', 'driver_arriving', 'Arrived', 'arrived', 'Begin trip', 'Started', 'started']);
    }
}
