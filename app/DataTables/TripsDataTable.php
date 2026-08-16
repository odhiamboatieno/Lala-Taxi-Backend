<?php

namespace App\DataTables;

use App\Models\Currency;
use App\Support\TripOperations;
use Illuminate\Http\Request;
use Yajra\DataTables\Services\DataTable;

class TripsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('select_trip', function ($trip) {
                return '<input type="checkbox" class="form-check-input js-trip-select" value="'.e($trip->id).'">';
            })
            ->editColumn('trip_reference', function ($trip) {
                return '<button type="button" class="btn btn-link p-0 fw-semibold text-decoration-none js-trip-preview" data-trip-id="'.e($trip->id).'">#'.e($trip->id).'</button>';
            })
            ->editColumn('status_badge', function ($trip) {
                return $this->formatStatusBadge($trip->status_raw);
            })
            ->editColumn('payment_status_badge', function ($trip) {
                return $this->formatPaymentStatusBadge($trip->payment_status);
            })
            ->editColumn('trip_type_badge', function ($trip) {
                return $this->formatTripTypeBadge($trip->trip_type);
            })
            ->editColumn('pickup_short', function ($trip) {
                return $this->formatTextWithTooltip($trip->pickup_location_full);
            })
            ->editColumn('drop_short', function ($trip) {
                return $this->formatTextWithTooltip($trip->drop_location_full);
            })
            ->editColumn('driver_name', function ($trip) {
                return $trip->driver_name ?: '<span class="text-muted">Unassigned</span>';
            })
            ->editColumn('company_name', function ($trip) {
                return $trip->company_name ?: '<span class="text-muted">Platform</span>';
            })
            ->editColumn('driver_earnings_display', fn ($trip) => $trip->driver_earnings_display ?: $trip->currency_symbol.'0.00')
            ->editColumn('commission_display', fn ($trip) => $trip->commission_display ?: $trip->currency_symbol.'0.00')
            ->editColumn('fare_display', fn ($trip) => $trip->fare_display ?: $trip->currency_symbol.'0.00')
            ->editColumn('distance_display', fn ($trip) => $trip->distance_display ?: '0 KM')
            ->editColumn('duration_display', fn ($trip) => $trip->duration_display ?: '-')
            ->editColumn('dispatch_mode_label', function ($trip) {
                return $trip->dispatch_mode_label ?: '<span class="text-muted">Default</span>';
            })
            ->addColumn('action', function ($trip) {
                $actions = [];
                $actions[] = '<a class="dropdown-item" href="'.url(LOGIN_USER_TYPE.'/view_trips/'.$trip->id).'"><i class="bi bi-eye me-2"></i>View Details</a>';

                if (!empty($trip->request_id)) {
                    $actions[] = '<a class="dropdown-item" href="'.url(LOGIN_USER_TYPE.'/detail_request/'.$trip->request_id).'"><i class="bi bi-card-list me-2"></i>View Booking</a>';
                }

                if (!empty($trip->payment_record_id) && (LOGIN_USER_TYPE === 'company' || optional(auth('admin')->user())->can('manage_payments'))) {
                    $actions[] = '<a class="dropdown-item" href="'.url(LOGIN_USER_TYPE.'/view_payments/'.$trip->payment_record_id).'"><i class="bi bi-wallet2 me-2"></i>Payment Review</a>';
                }

                if (in_array($trip->status_raw, TripOperations::liveStatuses(), true)) {
                    $actions[] = '<button type="button" class="dropdown-item js-trip-preview" data-trip-id="'.e($trip->id).'"><i class="bi bi-broadcast-pin me-2"></i>Track Trip</button>';
                }

                if (!empty($trip->driver_phone)) {
                    $actions[] = '<a class="dropdown-item" href="tel:'.e($trip->driver_phone).'"><i class="bi bi-telephone-outbound me-2"></i>Contact Driver</a>';
                }

                if (!empty($trip->rider_phone)) {
                    $actions[] = '<a class="dropdown-item" href="tel:'.e($trip->rider_phone).'"><i class="bi bi-telephone me-2"></i>Contact Rider</a>';
                }

                $actions[] = '<button type="button" class="dropdown-item js-trip-preview" data-trip-id="'.e($trip->id).'"><i class="bi bi-clock-history me-2"></i>View Timeline</button>';

                return '
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>'.implode('</li><li>', $actions).'</li>
                        </ul>
                    </div>
                ';
            })
            ->rawColumns([
                'select_trip',
                'trip_reference',
                'status_badge',
                'payment_status_badge',
                'trip_type_badge',
                'pickup_short',
                'drop_short',
                'driver_name',
                'company_name',
                'dispatch_mode_label',
                'action',
            ]);
    }

    public function query()
    {
        $defaultCurrency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE === 'company' && session()->get('currency') !== null) {
            $defaultCurrency = Currency::whereCode(session()->get('currency'))->first() ?: $defaultCurrency;
        }

        $symbol = $defaultCurrency->symbol ?? '$';
        $currencyRate = $defaultCurrency->rate ?? 1;
        $schema = TripOperations::schemaSupport();
        $query = TripOperations::baseQuery(LOGIN_USER_TYPE);
        $query = TripOperations::applyFilters($query, request(), $schema);

        $fareExpression = 'ROUND(((trips.total_fare / currency.rate) * '.$currencyRate.'), 2)';
        $driverEarningsExpression = 'ROUND((((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * '.$currencyRate.'), 2)';
        $commissionExpression = 'ROUND(((trips.driver_or_company_commission / currency.rate) * '.$currencyRate.'), 2)';
        $scheduledExpression = $schema['schedule_ride_scheduled_at']
            ? 'schedule_ride.scheduled_at'
            : "CONCAT(schedule_ride.schedule_date, ' ', schedule_ride.schedule_time)";

        return $query->selectRaw('trips.id')
            ->selectRaw('trips.id as trip_reference')
            ->selectRaw('trips.status as status_raw')
            ->selectRaw('COALESCE(trips.payment_status, "Pending") as payment_status')
            ->selectRaw('CONCAT(COALESCE(rider.first_name, ""), " ", COALESCE(rider.last_name, "")) as rider_name')
            ->selectRaw('CONCAT(COALESCE(driver.first_name, ""), " ", COALESCE(driver.last_name, "")) as driver_name')
            ->selectRaw('companies.name as company_name')
            ->selectRaw('car_type.car_name as vehicle_type')
            ->selectRaw('trips.pickup_location as pickup_location_full')
            ->selectRaw('trips.drop_location as drop_location_full')
            ->selectRaw('trips.pickup_latitude')
            ->selectRaw('trips.pickup_longitude')
            ->selectRaw('trips.drop_latitude')
            ->selectRaw('trips.drop_longitude')
            ->selectRaw('trips.payment_mode as payment_method')
            ->selectRaw('COALESCE(currency.symbol, "'.$symbol.'") as currency_symbol')
            ->selectRaw($fareExpression.' as fare_amount')
            ->selectRaw('CONCAT("'.$symbol.'", FORMAT('.$fareExpression.', 2)) as fare_display')
            ->selectRaw($driverEarningsExpression.' as driver_earnings_amount')
            ->selectRaw('CONCAT("'.$symbol.'", FORMAT('.$driverEarningsExpression.', 2)) as driver_earnings_display')
            ->selectRaw($commissionExpression.' as commission_amount')
            ->selectRaw('CONCAT("'.$symbol.'", FORMAT('.$commissionExpression.', 2)) as commission_display')
            ->selectRaw('CONCAT(ROUND(COALESCE(trips.total_km, 0), 2), " KM") as distance_display')
            ->selectRaw('CASE WHEN trips.begin_trip IS NOT NULL AND trips.end_trip IS NOT NULL AND trips.begin_trip != "0000-00-00 00:00:00" AND trips.end_trip != "0000-00-00 00:00:00" THEN CONCAT(TIMESTAMPDIFF(MINUTE, trips.begin_trip, trips.end_trip), " min") ELSE "-" END as duration_display')
            ->selectRaw('DATE_FORMAT(trips.created_at, "%d %b %Y %H:%i") as created_time')
            ->selectRaw('CASE WHEN '.$scheduledExpression.' IS NOT NULL THEN DATE_FORMAT('.$scheduledExpression.', "%d %b %Y %H:%i") ELSE "-" END as scheduled_time')
            ->selectRaw('CASE WHEN trips.end_trip IS NOT NULL AND trips.end_trip != "0000-00-00 00:00:00" THEN DATE_FORMAT(trips.end_trip, "%d %b %Y %H:%i") ELSE "-" END as completed_time')
            ->selectRaw('CASE 
                WHEN schedule_ride.booking_type = "Manual Booking" THEN "manual"
                WHEN schedule_ride.booking_type = "Schedule Booking" THEN "scheduled"
                ELSE "instant"
            END as trip_type')
            ->selectRaw('CASE 
                WHEN '.($schema['schedule_ride_dispatch_mode'] ? 'schedule_ride.dispatch_mode' : '"default"').' IS NULL THEN "Default"
                ELSE CONCAT(UCASE(LEFT('.($schema['schedule_ride_dispatch_mode'] ? 'schedule_ride.dispatch_mode' : '"default"').',1)), SUBSTRING('.($schema['schedule_ride_dispatch_mode'] ? 'schedule_ride.dispatch_mode' : '"default"').',2))
            END as dispatch_mode_label')
            ->selectRaw('trips.request_id')
            ->selectRaw('payment.id as payment_record_id')
            ->selectRaw('driver.mobile_number as driver_phone')
            ->selectRaw('rider.mobile_number as rider_phone')
            ->selectRaw('trips.begin_trip')
            ->selectRaw('trips.end_trip')
            ->selectRaw('trips.total_fare as fare_total_raw')
            ->selectRaw('trips.driver_payout as driver_payout_raw')
            ->selectRaw('trips.driver_or_company_commission as commission_raw')
            ->selectRaw('trips.access_fee')
            ->selectRaw('trips.wallet_amount')
            ->selectRaw('trips.promo_amount')
            ->selectRaw('trips.cash_collectable')
            ->selectRaw('trips.additional_rider_amount')
            ->selectRaw('trips.waiting_charge')
            ->selectRaw('trips.toll_fee')
            ->selectRaw('trips.created_at')
            ->orderByDesc('trips.created_at');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('trips-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->orderBy(15, 'desc')
            ->buttons(['csv', 'excel', 'print', 'reset'])
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 25,
                'drawCallback' => 'function() {
                    $(".pagination").addClass("pagination-primary mb-0");
                    $(".paginate_button").addClass("page-item");
                    $(".paginate_button a").addClass("page-link");
                }',
                'language' => [
                    'paginate' => [
                        'previous' => '<i class="ri-arrow-left-s-line fw-semibold"></i>',
                        'next' => '<i class="ri-arrow-right-s-line fw-semibold"></i>',
                    ],
                ],
            ]);
    }

    protected function getColumns()
    {
        $columns = [
            ['data' => 'select_trip', 'name' => 'select_trip', 'title' => '<input type="checkbox" class="form-check-input" id="select-all-trips">', 'orderable' => false, 'searchable' => false, 'exportable' => false],
            ['data' => 'trip_reference', 'name' => 'trips.id', 'title' => 'Trip ID / Reference'],
            ['data' => 'status_badge', 'name' => 'trips.status', 'title' => 'Status'],
            ['data' => 'trip_type_badge', 'name' => 'schedule_ride.booking_type', 'title' => 'Trip Type'],
            ['data' => 'rider_name', 'name' => 'rider.first_name', 'title' => 'Rider'],
            ['data' => 'driver_name', 'name' => 'driver.first_name', 'title' => 'Driver'],
        ];

        if (LOGIN_USER_TYPE !== 'company') {
            $columns[] = ['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company / Operator'];
        }

        return array_merge($columns, [
            ['data' => 'vehicle_type', 'name' => 'car_type.car_name', 'title' => 'Vehicle Type'],
            ['data' => 'pickup_short', 'name' => 'trips.pickup_location', 'title' => 'Pickup'],
            ['data' => 'drop_short', 'name' => 'trips.drop_location', 'title' => 'Dropoff'],
            ['data' => 'distance_display', 'name' => 'trips.total_km', 'title' => 'Distance'],
            ['data' => 'duration_display', 'name' => 'trips.end_trip', 'title' => 'Duration'],
            ['data' => 'fare_display', 'name' => 'trips.total_fare', 'title' => 'Fare'],
            ['data' => 'payment_method', 'name' => 'trips.payment_mode', 'title' => 'Payment Method'],
            ['data' => 'payment_status_badge', 'name' => 'trips.payment_status', 'title' => 'Payment Status'],
            ['data' => 'driver_earnings_display', 'name' => 'trips.driver_payout', 'title' => 'Driver Earnings'],
            ['data' => 'commission_display', 'name' => 'trips.driver_or_company_commission', 'title' => 'Commission'],
            ['data' => 'created_time', 'name' => 'trips.created_at', 'title' => 'Created Time'],
            ['data' => 'scheduled_time', 'name' => 'schedule_ride.schedule_date', 'title' => 'Scheduled Time'],
            ['data' => 'completed_time', 'name' => 'trips.end_trip', 'title' => 'Completed Time'],
            ['data' => 'dispatch_mode_label', 'name' => 'schedule_ride.dispatch_mode', 'title' => 'Dispatch Mode'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ]);
    }

    protected function filename(): string
    {
        return 'trips_' . date('YmdHis');
    }

    protected function formatStatusBadge(?string $status): string
    {
        $status = $status ?: 'Unknown';
        $normalized = strtolower($status);
        $class = match (true) {
            in_array($status, TripOperations::completedStatuses(), true) => 'bg-success-subtle text-success',
            in_array($status, TripOperations::cancelledStatuses(), true) => 'bg-danger-subtle text-danger',
            in_array($status, TripOperations::failedStatuses(), true) => 'bg-danger-subtle text-danger',
            in_array($status, TripOperations::liveStatuses(), true) => 'bg-info-subtle text-info',
            str_contains($normalized, 'pending') || str_contains($normalized, 'search') => 'bg-warning-subtle text-warning',
            default => 'bg-secondary-subtle text-secondary',
        };

        return '<span class="badge '.$class.'">'.e($status).'</span>';
    }

    protected function formatPaymentStatusBadge(?string $status): string
    {
        $status = $status ?: 'Pending';
        $class = match ($status) {
            'Completed' => 'bg-success-subtle text-success',
            'Trip Cancelled' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };

        return '<span class="badge '.$class.'">'.e($status).'</span>';
    }

    protected function formatTripTypeBadge(?string $tripType): string
    {
        $tripType = $tripType ?: 'instant';
        $label = ucfirst($tripType);
        $class = match ($tripType) {
            'manual' => 'bg-primary-subtle text-primary',
            'scheduled' => 'bg-secondary-subtle text-secondary',
            default => 'bg-info-subtle text-info',
        };

        return '<span class="badge '.$class.'">'.$label.'</span>';
    }

    protected function formatTextWithTooltip(?string $text): string
    {
        $text = $text ?: 'N/A';
        $short = mb_strimwidth($text, 0, 38, '...');

        return '<span title="'.e($text).'">'.e($short).'</span>';
    }
}
