<?php

/**
 * Trips Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Trips
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\User;
use App\Models\Trips;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\SiteSettings;
use App\Models\Currency;
use App\Models\PayoutCredentials;
use App\Models\DriverOweAmount;
use App\Models\Company;
use App\Models\CarType;
use App\Models\Location;
use App\Models\User as UserModel;
use App\DataTables\CancelTripsDataTable;
use App\Support\TripOperations;
use Carbon\Carbon;
use Excel;
use DB;
use Auth;
class TripsController extends Controller
{
    /**
     * Load Datatable for Trips
     *
     * @return view file
     */
    public function index(Request $request)
    {
        $trips = $this->getTripsPaginator($request);
        $tripRows = $trips->getCollection()->map(function ($trip) {
            return (object) $this->formatTripRow($trip);
        })->values();
        $trips->setCollection($tripRows);

        return view('admin.trips.index', [
            'pageTitle' => 'Trip Operations Center',
            'activeStatus' => $request->get('status', 'all'),
            'activeQuickFilter' => $request->get('quick_filter', ''),
            'fromDate' => $request->get('from'),
            'toDate' => $request->get('to'),
            'tripSummary' => $this->getTripSummary($request),
            'tripAlerts' => $this->getTripAlerts($request),
            'tripAnalytics' => $this->getTripAnalytics($request),
            'filterOptions' => $this->getFilterOptions(),
            'schemaSupport' => TripOperations::schemaSupport(),
            'lastUpdatedAt' => now()->format('d M Y, H:i:s'),
            'liveRefreshSeconds' => 30,
            'mapViewAvailable' => !empty(site_settings('map_key') ?? ''),
            'reportsUrl' => url(LOGIN_USER_TYPE.'/statements/overall'),
            'trips' => $trips,
            'tripRows' => $tripRows,
        ]);
    }
    
    /**
     * Load particular trip data 
     *
     * @return view file
     */
    public function view(Request $request)
    {
        $data['result'] = Trips::with([
                    'users',
                    'driver.company',
                    'car_type',
                    'cancel.cancel_reason',
                    'trips.users',
                ])
                ->where('id',$request->id)
                ->where(function($query)  {
                    if(LOGIN_USER_TYPE=='company') {
                        $query->whereHas('driver',function($q1){
                            $q1->where('company_id',Auth::guard('company')->user()->id);
                        });
                    }
                })
                ->first();

        if($data['result']) {
            $data['invoice_data'] = $this->getAdminInvoice($data['result']);
            $data['back_url'] = url(LOGIN_USER_TYPE.'/trips');
            if($request->s == 'overall') {
                $data['back_url'] = url(LOGIN_USER_TYPE.'/statements/overall');
            }
            elseif($request->s == 'driver') {
                $data['back_url'] = url(LOGIN_USER_TYPE.'/view_driver_statement/'.$data['result']->driver_id);
            }


            $driver_owe_amount = DriverOweAmount::where('user_id',$data['result']->driver_id)->first();

            return view('admin.trips.view', $data);
        }
        flashMessage('danger', 'Invalid ID');
        return redirect(LOGIN_USER_TYPE.'/trips');          
    }

    /**
     * Export trip data to excel 
     *
     * @return view file
     */
    public function export(Request $request)
    {
      

           $from = date('Y-m-d H:i:s', strtotime($request->from));
            $to = date('Y-m-d H:i:s', strtotime($request->to));
            $category = $request->category;

           
                $result = Trips::where('trips.created_at', '>=', $from)->where('trips.created_at', '<=', $to) 
                        ->join('users', function($join) {
                                $join->on('users.id', '=', 'trips.user_id');
                            })
                        ->join('currency', function($join) {
                                $join->on('currency.code', '=', 'trips.currency_code');
                            })
                        ->join('car_type', function($join) {
                                $join->on('car_type.id', '=', 'trips.car_id');
                            })
                        ->leftJoin('users as u', function($join) {
                                $join->on('u.id', '=', 'trips.driver_id');
                            })
                        ->select(['trips.id as id','trips.begin_trip as begin_trip','trips.pickup_location as pickup_location','trips.drop_location as drop_location', 'u.first_name as driver_name', 'users.first_name as rider_name',  DB::raw('CONCAT(currency.symbol, trips.total_fare) AS total_amount'), 'trips.status','car_type.car_name as car_name', 'trips.created_at as created_at', 'trips.updated_at as updated_at', 'trips.*'])->get();
        

        Excel::create('Trips-report', function($excel) use($result) {
            $excel->sheet('sheet1', function($sheet) use($result) {

                 $data[0]=['Id','From Location','To Location','Date','Driver Name','Rider Name','Fare','Vehicle Details','Status','Created At'];

                foreach ($result as $key => $value) {
                    $data[]=[$value->id,$value->pickup_location,$value->drop_location,date('d-m-y h:m a',strtotime($value->date)),$value->rider_name,$value->driver_name, html_entity_decode($value->total_amount),$value->car_name,$value->status,$value->created_at];
                }
                $data = array_values($data);
                 $sheet->with($data);
            });
        })->export('csv');
    }

    /**
     * Load Datatable for Cancel trips
     *
     * @param array $dataTable  Instance of Cancel tripsDataTable
     * @return datatable
     */
    public function cancel_trips(CancelTripsDataTable $dataTable)
    {
        return $dataTable->render('admin.trips.cancel');
    }

    public function getAdminInvoice($trips)
    {
        $default_currency = Currency::defaultCurrency()->first();
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {
            $default_currency = Currency::whereCode(session('currency'))->first();
        }
        $currency_code = $default_currency->code;

        $symbol = html_entity_decode($default_currency->symbol);

        $invoice = [];

        if($trips->pool_id) {
            $item = [
                'key'   => 'No.of Seats',
                'value' => $trips->seats,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->total_km > 0) {
            $item = [
                'key' => 'Distance',
                'value' => $trips->total_km.' KM',
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->base_fare > 0) {
            $item = [
                'key' => 'Base fare',
                'value' => $symbol.$trips->base_fare,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->time_fare > 0) {
            $item = [
                'key' => 'Time fare',
                'value' => $symbol.$trips->time_fare,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->distance_fare > 0) {
            $item = [
                'key' => 'Distance fare',
                'value' => $symbol.$trips->distance_fare,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->schedule_fare > 0) {
            $item = [
                'key' => 'Schedule fare',
                'value' => $symbol.$trips->schedule_fare,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->peak_fare > 0) {
            $item = [
                'key' => 'Normal fare',
                'value' => $symbol.$trips->subtotal_fare,
            ];
            $invoice[] = formatStatementItem($item);

            $item = [
                'key' => 'Peak time pricing  x '.$trips->peak_fare,
                'value' => $symbol.$trips->peak_amount,
            ];
            $invoice[] = formatStatementItem($item);

            $item = [
                'key' => 'Subtotal',
                'value' => $symbol.$trips->peak_subtotal_fare,
            ];
            $invoice[] = formatStatementItem($item);

            $item = [
                'key' => 'Driver Peak Amount',
                'value' => $symbol.$trips->driver_peak_amount,
            ];
            $invoice[] = formatStatementItem($item);

            $item = [
                'key' => 'Admin Peak Amount',
                'value' => $symbol.($trips->peak_amount - $trips->driver_peak_amount),
            ];
            $invoice[] = formatStatementItem($item);
        }
        else {
            if($trips->subtotal_fare > 0) {
                $item = [
                    'key'   => 'Sub Total Fare',
                    'value' => $symbol.$trips->subtotal_fare,
                ];
                $invoice[] = formatInvoiceItem($item);
            }
        }

        if($trips->additional_rider_amount > 0) {
            $item = [
                'key' => __('messages.additional_rider_amount'),
                'value' => $symbol.$trips->additional_rider_amount,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if(($trips->extra_fare_amount ?? 0) > 0) {
            $item = [
                'key' => trim('Extra fare '.(($trips->extra_fare_percentage ?? 0) > 0 ? '('.($trips->extra_fare_percentage + 0).'%)' : '')),
                'value' => $symbol.$trips->extra_fare_amount,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->waiting_charge > 0) {
            $item = [
                'key' => 'Waiting Charge',
                'value' => $symbol.$trips->waiting_charge,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->toll_reason_id) {
            if($trips->toll_reason_id == 1) {
                $item['key'] = $trips->trip_toll_fee_reason;
            }
            else {
                $item['key'] = $trips->toll_fee_reason;   
            }
            $item['value'] = $symbol.$trips->toll_fee;

            $invoice[] = formatStatementItem($item);
        }

        if($trips->tips > 0) {
            $item = [
                'key' => 'Driver Tips',
                'value' => $symbol.$trips->tips,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if(LOGIN_USER_TYPE != 'company') {
            if($trips->admin_total_amount > 0) {
                $item = [
                    'key' => 'Total fare',
                    // 'value' => $symbol.(number_format($trips->base_fare + $trips->time_fare +  $trips->distance_fare +  $trips->peak_amount + $trips->access_fee + $trips->schedule_fare + $trips->tips+ $trips->waiting_charge,2,'.','')),
                    'value' => $symbol.(number_format($trips->admin_total_amount + $trips->additional_rider_amount,2,'.','')),
                ];
                $invoice[] = formatStatementItem($item);
            }

            if($trips->access_fee > 0) {
                $item = [
                    'key' => 'Admin Commission for Rider',
                    'value' => $symbol.$trips->access_fee,
                ];
                $invoice[] = formatStatementItem($item);
            }
        }
        if($trips->driver_or_company_commission > 0) {
            $item = [
                'key' => 'Admin Commission for Driver',
                'value' => $symbol.$trips->driver_or_company_commission,
            ];
            $invoice[] = formatStatementItem($item);
        }

        if($trips->owe_amount > 0) {
            $item['key'] = 'Owe Amount';
            if(LOGIN_USER_TYPE == 'company') {
                $item['key'] .= ' ( Service fee + Admin Commission)';
            }
            $item['value'] = $symbol.$trips->owe_amount;

            $invoice[] = formatStatementItem($item);
        }

        if($trips->applied_owe_amount > 0) {
            $item = [
                'key' => 'Applied Owe Amount',
                'value' => $symbol.$trips->applied_owe_amount,
            ];

            $invoice[] = formatStatementItem($item);
        }

        $item = [
            'key' => 'Remaining Owe amount',
            'value' => $symbol.$trips->remaining_owe_amount,
        ];

        if($trips->wallet_amount > 0 && LOGIN_USER_TYPE != 'company') {
            $item = [
                'key' => 'Wallet amount',
                'value' => $symbol.$trips->wallet_amount,
            ];

            $invoice[] = formatStatementItem($item);
        }

        if($trips->promo_amount > 0) {
            $item = [
                'key' => 'Promo amount',
                'value' => $symbol.$trips->promo_amount,
            ];

            $invoice[] = formatStatementItem($item);
        }

        if($trips->cash_collectable > 0) {
            $item = [
                'key' => 'Cash Collected by Driver',
                'value' => $symbol.$trips->cash_collectable,
            ];

            $invoice[] = formatStatementItem($item);
        }
        if(LOGIN_USER_TYPE != 'company' && $trips->payment_mode != 'Cash' && $trips->status == 'Completed' && $trips->driver_payout > 0) {
            $item = [
                'key' => 'Driver Payout Amount',
                'value' => $symbol.$trips->driver_payout,
            ];

            $invoice[] = formatStatementItem($item);
        }

        $item = [
                'key' => 'Driver Earnings',
                'value' => $symbol.$trips->company_driver_earnings,
            ];

            $invoice[] = formatStatementItem($item);

        $item = [
            'key' => 'Payment Mode',
            'value' => $trips->payment_mode,
        ];

        $invoice[] = formatStatementItem($item);

        return $invoice;
    }

    protected function getBaseFilteredQuery(Request $request)
    {
        $schema = TripOperations::schemaSupport();
        $effectiveRequest = $request;
        $selectedCompanyServiceArea = null;

        if ($this->shouldUseCompanyServiceAreasForTrips() && $request->filled('location_id')) {
            $selectedCompanyServiceArea = CompanyServiceArea::query()
                ->where('company_id', Auth::guard('company')->id())
                ->find((int) $request->input('location_id'));

            $effectiveRequest = $request->duplicate(
                collect($request->query())->except('location_id')->all(),
                $request->request->all()
            );
        }

        $query = TripOperations::baseQuery(LOGIN_USER_TYPE);
        $query = TripOperations::applyFilters($query, $effectiveRequest, $schema);

        if ($request->filled('location_id') && $this->shouldUseCompanyServiceAreasForTrips()) {
            if (!$selectedCompanyServiceArea || empty($selectedCompanyServiceArea->polygon)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereRaw(
                    'ST_WITHIN(POINT(CAST(trips.pickup_latitude AS DECIMAL(10,7)), CAST(trips.pickup_longitude AS DECIMAL(10,7))), ST_GeomFromText(?))',
                    [$selectedCompanyServiceArea->polygon]
                );
            }
        }

        return $query;
    }

    protected function getTripsPaginator(Request $request)
    {
        $schema = TripOperations::schemaSupport();
        $defaultCurrency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE === 'company' && session()->get('currency') !== null) {
            $defaultCurrency = Currency::whereCode(session()->get('currency'))->first() ?: $defaultCurrency;
        }

        $symbol = html_entity_decode($defaultCurrency->symbol ?? '$');
        $currencyRate = (float) ($defaultCurrency->rate ?? 1);
        $scheduledExpression = $schema['schedule_ride_scheduled_at']
            ? 'schedule_ride.scheduled_at'
            : "CONCAT(schedule_ride.schedule_date, ' ', schedule_ride.schedule_time)";
        $paymentStatusExpression = $schema['trips_payment_status']
            ? 'COALESCE(trips.payment_status, "Pending")'
            : '"Pending"';
        $dispatchModeExpression = $schema['schedule_ride_dispatch_mode']
            ? 'schedule_ride.dispatch_mode'
            : '"default"';

        return $this->getBaseFilteredQuery($request)
            ->selectRaw('trips.id')
            ->selectRaw('trips.status as status_raw')
            ->selectRaw($paymentStatusExpression.' as payment_status')
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
            ->selectRaw('ROUND(((COALESCE(trips.total_fare, 0) / COALESCE(NULLIF(currency.rate, 0), 1)) * '.$currencyRate.'), 2) as fare_amount')
            ->selectRaw('ROUND(((COALESCE(trips.driver_payout, 0) / COALESCE(NULLIF(currency.rate, 0), 1)) * '.$currencyRate.'), 2) as driver_earnings_amount')
            ->selectRaw('ROUND(((COALESCE(trips.driver_or_company_commission, 0) / COALESCE(NULLIF(currency.rate, 0), 1)) * '.$currencyRate.'), 2) as commission_amount')
            ->selectRaw('COALESCE(trips.total_km, 0) as total_km_raw')
            ->selectRaw('DATE_FORMAT(trips.created_at, "%d %b %Y %H:%i") as created_time')
            ->selectRaw('CASE WHEN '.$scheduledExpression.' IS NOT NULL THEN DATE_FORMAT('.$scheduledExpression.', "%d %b %Y %H:%i") ELSE "-" END as scheduled_time')
            ->selectRaw('CASE WHEN trips.end_trip IS NOT NULL AND trips.end_trip != "0000-00-00 00:00:00" THEN DATE_FORMAT(trips.end_trip, "%d %b %Y %H:%i") ELSE "-" END as completed_time')
            ->selectRaw('CASE 
                WHEN schedule_ride.booking_type = "Manual Booking" THEN "manual"
                WHEN schedule_ride.booking_type = "Schedule Booking" THEN "scheduled"
                ELSE "instant"
            END as trip_type')
            ->selectRaw('CASE 
                WHEN '.$dispatchModeExpression.' IS NULL THEN "Default"
                ELSE CONCAT(UCASE(LEFT('.$dispatchModeExpression.',1)), SUBSTRING('.$dispatchModeExpression.',2))
            END as dispatch_mode_label')
            ->selectRaw('trips.request_id')
            ->selectRaw('payment.id as payment_record_id')
            ->selectRaw('driver.mobile_number as driver_phone')
            ->selectRaw('driver.country_code as driver_country_code')
            ->selectRaw('rider.mobile_number as rider_phone')
            ->selectRaw('rider.country_code as rider_country_code')
            ->selectRaw('trips.begin_trip')
            ->selectRaw('trips.end_trip')
            ->selectRaw('trips.created_at')
            ->orderByDesc('trips.created_at')
            ->paginate((int) $request->get('per_page', 25))
            ->appends($request->query());
    }

    protected function formatTripRow(object $trip): array
    {
        $fare = (float) ($trip->fare_amount ?? 0);
        $driverEarnings = (float) ($trip->driver_earnings_amount ?? 0);
        $commission = (float) ($trip->commission_amount ?? 0);
        $currencySymbol = $trip->currency_symbol ?: '$';
        $distance = round((float) ($trip->total_km_raw ?? 0), 2);
        $driverPhone = trim((string) (($trip->driver_country_code ?? '') ? '+'.$trip->driver_country_code.$trip->driver_phone : $trip->driver_phone));
        $riderPhone = trim((string) (($trip->rider_country_code ?? '') ? '+'.$trip->rider_country_code.$trip->rider_phone : $trip->rider_phone));

        return [
            'id' => (int) $trip->id,
            'trip_reference' => '#'.$trip->id,
            'status_raw' => $trip->status_raw,
            'status_badge' => $this->formatStatusBadge($trip->status_raw),
            'payment_status' => $trip->payment_status,
            'payment_status_badge' => $this->formatPaymentStatusBadge($trip->payment_status),
            'trip_type' => $trip->trip_type,
            'trip_type_badge' => $this->formatTripTypeBadge($trip->trip_type),
            'rider_name' => trim((string) $trip->rider_name) ?: '-',
            'driver_name' => trim((string) $trip->driver_name) ?: 'Unassigned',
            'company_name' => trim((string) $trip->company_name) ?: 'Platform',
            'vehicle_type' => $trip->vehicle_type ?: '-',
            'pickup_location_full' => $trip->pickup_location_full ?: '-',
            'drop_location_full' => $trip->drop_location_full ?: '-',
            'pickup_short' => $this->formatTextWithTooltip($trip->pickup_location_full),
            'drop_short' => $this->formatTextWithTooltip($trip->drop_location_full),
            'pickup_latitude' => $trip->pickup_latitude,
            'pickup_longitude' => $trip->pickup_longitude,
            'drop_latitude' => $trip->drop_latitude,
            'drop_longitude' => $trip->drop_longitude,
            'payment_method' => $trip->payment_method ?: '-',
            'fare_display' => $currencySymbol.number_format($fare, 2),
            'driver_earnings_display' => $currencySymbol.number_format($driverEarnings, 2),
            'commission_display' => $currencySymbol.number_format($commission, 2),
            'distance_display' => $distance.' KM',
            'duration_display' => $this->formatDuration($trip->begin_trip, $trip->end_trip),
            'created_time' => $trip->created_time ?: '-',
            'scheduled_time' => $trip->scheduled_time ?: '-',
            'completed_time' => $trip->completed_time ?: '-',
            'dispatch_mode_label' => $trip->dispatch_mode_label ?: 'Default',
            'request_id' => $trip->request_id,
            'payment_record_id' => $trip->payment_record_id,
            'driver_phone' => $driverPhone,
            'rider_phone' => $riderPhone,
            'begin_trip' => $trip->begin_trip,
            'end_trip' => $trip->end_trip,
            'timeline' => $this->buildTripTimeline($trip),
        ];
    }

    protected function buildTripTimeline(object $trip): array
    {
        return collect([
            ['label' => 'Booking created', 'value' => $trip->created_time ?: null],
            ['label' => 'Scheduled time', 'value' => ($trip->scheduled_time ?? '-') !== '-' ? $trip->scheduled_time : null],
            ['label' => 'Trip started', 'value' => !empty($trip->begin_trip) && $trip->begin_trip !== '0000-00-00 00:00:00' ? $trip->begin_trip : null],
            ['label' => 'Trip completed', 'value' => ($trip->completed_time ?? '-') !== '-' ? $trip->completed_time : null],
            ['label' => 'Payment status', 'value' => $trip->payment_status ?? null],
        ])->filter(fn ($event) => !empty($event['value']))->values()->all();
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

    protected function formatDuration(?string $beginTrip, ?string $endTrip): string
    {
        if (
            empty($beginTrip) ||
            empty($endTrip) ||
            $beginTrip === '0000-00-00 00:00:00' ||
            $endTrip === '0000-00-00 00:00:00'
        ) {
            return '-';
        }

        try {
            return Carbon::parse($beginTrip)->diffInMinutes(Carbon::parse($endTrip)).' min';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    protected function getTripSummary(Request $request): array
    {
        $schema = TripOperations::schemaSupport();
        $baseQuery = $this->getBaseFilteredQuery($request);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $summary = [
            'total_trips' => ['value' => (clone $baseQuery)->count(), 'change' => 0, 'filter' => 'all', 'icon' => 'bi bi-speedometer2', 'tone' => 'primary'],
            'live_trips' => ['value' => (clone TripOperations::applyStatusToken(clone $baseQuery, 'live', $schema))->count(), 'change' => 0, 'filter' => 'live', 'icon' => 'bi bi-broadcast-pin', 'tone' => 'info'],
            'pending_trips' => ['value' => (clone TripOperations::applyStatusToken(clone $baseQuery, 'pending', $schema))->count(), 'change' => 0, 'filter' => 'pending', 'icon' => 'bi bi-hourglass-split', 'tone' => 'warning'],
            'scheduled_today' => ['value' => (clone TripOperations::applyQuickFilter(clone $baseQuery, 'scheduled_today', $schema))->count(), 'change' => 0, 'filter' => 'scheduled_today', 'icon' => 'bi bi-calendar2-check', 'tone' => 'secondary'],
            'completed_trips' => ['value' => (clone TripOperations::applyStatusToken(clone $baseQuery, 'completed', $schema))->count(), 'change' => 0, 'filter' => 'completed', 'icon' => 'bi bi-check2-circle', 'tone' => 'success'],
            'cancelled_trips' => ['value' => (clone TripOperations::applyStatusToken(clone $baseQuery, 'cancelled', $schema))->count(), 'change' => 0, 'filter' => 'cancelled', 'icon' => 'bi bi-x-circle', 'tone' => 'danger'],
            'failed_trips' => ['value' => (clone TripOperations::applyStatusToken(clone $baseQuery, 'failed', $schema))->count(), 'change' => 0, 'filter' => 'no_driver', 'icon' => 'bi bi-exclamation-triangle', 'tone' => 'danger'],
            'total_revenue' => ['value' => $this->sumTripColumn(clone $baseQuery, 'total_fare'), 'change' => 0, 'filter' => 'completed', 'icon' => 'bi bi-cash-stack', 'tone' => 'primary', 'money' => true],
            'cash_collected' => ['value' => (float) (clone $baseQuery)->whereIn('trips.payment_mode', ['Cash', 'Cash & Wallet'])->sum('trips.total_fare'), 'change' => 0, 'filter' => 'all', 'icon' => 'bi bi-cash-coin', 'tone' => 'success', 'money' => true],
            'wallet_card_collected' => ['value' => (float) (clone $baseQuery)->whereNotIn('trips.payment_mode', ['Cash', 'Cash & Wallet'])->sum('trips.total_fare'), 'change' => 0, 'filter' => 'all', 'icon' => 'bi bi-credit-card-2-front', 'tone' => 'info', 'money' => true],
            'company_commission' => ['value' => $this->sumTripColumn(clone $baseQuery, 'driver_or_company_commission'), 'change' => 0, 'filter' => 'completed', 'icon' => 'bi bi-buildings', 'tone' => 'secondary', 'money' => true],
            'driver_earnings' => ['value' => $this->sumTripColumn(clone $baseQuery, 'driver_payout'), 'change' => 0, 'filter' => 'completed', 'icon' => 'bi bi-person-badge', 'tone' => 'dark', 'money' => true],
        ];

        foreach ($summary as $key => $item) {
            $todayValue = $this->metricForDay($request, $key, $today);
            $yesterdayValue = $this->metricForDay($request, $key, $yesterday);
            $summary[$key]['change'] = $this->calculatePercentChange($todayValue, $yesterdayValue);
        }

        return $summary;
    }

    protected function metricForDay(Request $request, string $metric, Carbon $date): float|int
    {
        $schema = TripOperations::schemaSupport();
        $query = $this->getBaseFilteredQuery($request)->whereDate('trips.created_at', $date);

        return match ($metric) {
            'live_trips' => (clone TripOperations::applyStatusToken($query, 'live', $schema))->count(),
            'pending_trips' => (clone TripOperations::applyStatusToken($query, 'pending', $schema))->count(),
            'scheduled_today' => (clone TripOperations::applyQuickFilter($query, 'scheduled_today', $schema))->count(),
            'completed_trips' => (clone TripOperations::applyStatusToken($query, 'completed', $schema))->count(),
            'cancelled_trips' => (clone TripOperations::applyStatusToken($query, 'cancelled', $schema))->count(),
            'failed_trips' => (clone TripOperations::applyStatusToken($query, 'failed', $schema))->count(),
            'total_revenue', 'cash_collected', 'wallet_card_collected' => (float) (clone $query)->sum('trips.total_fare'),
            'company_commission' => (float) (clone $query)->sum('trips.driver_or_company_commission'),
            'driver_earnings' => (float) (clone $query)->sum('trips.driver_payout'),
            default => (clone $query)->count(),
        };
    }

    protected function calculatePercentChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function getTripAlerts(Request $request): array
    {
        $schema = TripOperations::schemaSupport();
        $baseQuery = $this->getBaseFilteredQuery($request);

        $pendingTooLong = (clone $baseQuery)
            ->whereIn('trips.status', TripOperations::pendingStatuses())
            ->where('trips.created_at', '<=', now()->subMinutes(10))
            ->count();

        $withoutDriver = (clone $baseQuery)
            ->where(function ($query) {
                $query->whereNull('trips.driver_id')->orWhere('trips.driver_id', 0);
            })
            ->whereNotIn('trips.status', array_merge(TripOperations::completedStatuses(), TripOperations::cancelledStatuses()))
            ->count();

        $paymentIssues = $schema['trips_payment_status']
            ? (clone $baseQuery)->whereIn('trips.status', TripOperations::completedStatuses())->where('trips.payment_status', '!=', 'Completed')->count()
            : 0;

        $cancelledToday = (clone $baseQuery)->whereIn('trips.status', TripOperations::cancelledStatuses())->whereDate('trips.created_at', today())->count();
        $cancelledYesterday = (clone $baseQuery)->whereIn('trips.status', TripOperations::cancelledStatuses())->whereDate('trips.created_at', today()->subDay())->count();

        $scheduledSoon = $schema['schedule_ride_dispatch_mode']
            ? (clone $baseQuery)->where('schedule_ride.status', 'Pending')->whereBetween('schedule_ride.dispatch_at', [now(), now()->addMinutes(30)])->count()
            : 0;

        return [
            ['label' => 'Trips pending too long', 'count' => $pendingTooLong, 'filter' => 'pending', 'tone' => 'warning', 'icon' => 'bi bi-alarm'],
            ['label' => 'Trips without driver', 'count' => $withoutDriver, 'filter' => 'no_driver', 'tone' => 'danger', 'icon' => 'bi bi-person-x'],
            ['label' => 'Trips with payment issue', 'count' => $paymentIssues, 'filter' => 'payment_issues', 'tone' => 'danger', 'icon' => 'bi bi-credit-card-2-back'],
            ['label' => 'Cancelled trips spike', 'count' => max(0, $cancelledToday - $cancelledYesterday), 'filter' => 'cancelled', 'tone' => 'warning', 'icon' => 'bi bi-graph-down-arrow'],
            ['label' => 'Scheduled trips starting soon', 'count' => $scheduledSoon, 'filter' => 'scheduled_today', 'tone' => 'info', 'icon' => 'bi bi-calendar2-event'],
            ['label' => 'Failed payment trips', 'count' => $paymentIssues, 'filter' => 'payment_issues', 'tone' => 'danger', 'icon' => 'bi bi-exclamation-diamond'],
            ['label' => 'Disputed trips', 'count' => 0, 'filter' => 'all', 'tone' => 'secondary', 'icon' => 'bi bi-flag'],
            ['label' => 'Driver no-show', 'count' => 0, 'filter' => 'all', 'tone' => 'secondary', 'icon' => 'bi bi-person-slash'],
        ];
    }

    protected function getTripAnalytics(Request $request): array
    {
        $baseQuery = $this->getBaseFilteredQuery($request);

        $statusBreakdown = (clone $baseQuery)
            ->select('trips.status', DB::raw('COUNT(*) as total'))
            ->groupBy('trips.status')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $revenueTrend = (clone $baseQuery)
            ->selectRaw('DATE(trips.created_at) as day')
            ->selectRaw('SUM(trips.total_fare) as total_revenue')
            ->where('trips.created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(trips.created_at)'))
            ->orderBy('day')
            ->get();

        $paymentSplit = (clone $baseQuery)
            ->select('trips.payment_mode', DB::raw('COUNT(*) as total'))
            ->groupBy('trips.payment_mode')
            ->orderByDesc('total')
            ->get();

        $topDrivers = (clone $baseQuery)
            ->whereIn('trips.status', TripOperations::completedStatuses())
            ->select('driver.first_name', 'driver.last_name', DB::raw('COUNT(*) as total'))
            ->whereNotNull('trips.driver_id')
            ->groupBy('trips.driver_id', 'driver.first_name', 'driver.last_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $pickupZones = (clone $baseQuery)
            ->select('trips.pickup_location', DB::raw('COUNT(*) as total'))
            ->whereNotNull('trips.pickup_location')
            ->groupBy('trips.pickup_location')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $cancelReasonExpression = TripOperations::cancellationReasonExpression();
        $cancelReasons = collect();

        if (TripOperations::schemaSupport()['cancel_table']) {
            $cancelReasons = (clone $baseQuery)
                ->selectRaw($cancelReasonExpression.' as cancel_reason')
                ->selectRaw('COUNT(*) as total')
                ->where(function ($query) {
                    $schema = TripOperations::schemaSupport();

                    if ($schema['cancel_reasons_table'] && $schema['cancel_reasons_reason']) {
                        $query->whereNotNull('cancel_reasons.reason')
                            ->where('cancel_reasons.reason', '!=', '');
                    }

                    if ($schema['cancel_cancel_comments']) {
                        $method = ($schema['cancel_reasons_table'] && $schema['cancel_reasons_reason']) ? 'orWhere' : 'where';
                        $query->{$method}(function ($commentsQuery) {
                            $commentsQuery->whereNotNull('cancel.cancel_comments')
                                ->where('cancel.cancel_comments', '!=', '');
                        });
                    }
                })
                ->groupBy(DB::raw($cancelReasonExpression))
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }

        return [
            'status_chart' => [
                'labels' => $statusBreakdown->pluck('status')->map(fn ($item) => $item ?: 'Unknown')->values(),
                'series' => $statusBreakdown->pluck('total')->values(),
            ],
            'revenue_chart' => [
                'labels' => $revenueTrend->pluck('day')->map(fn ($day) => Carbon::parse($day)->format('d M'))->values(),
                'series' => $revenueTrend->pluck('total_revenue')->map(fn ($value) => round((float) $value, 2))->values(),
            ],
            'payment_chart' => [
                'labels' => $paymentSplit->pluck('payment_mode')->map(fn ($item) => $item ?: 'Unknown')->values(),
                'series' => $paymentSplit->pluck('total')->values(),
            ],
            'top_drivers' => $topDrivers,
            'pickup_zones' => $pickupZones,
            'cancel_reasons' => $cancelReasons,
        ];
    }

    protected function getFilterOptions(): array
    {
        $companyScoped = LOGIN_USER_TYPE === 'company';

        return [
            'statuses' => [
                'all' => 'All Trips',
                'pending' => 'Pending / Searching Driver',
                'live' => 'Live / Ongoing',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'failed' => 'Failed / No Driver',
                'payment_issues' => 'Payment Issues',
            ],
            'trip_types' => [
                'instant' => 'Instant',
                'manual' => 'Manual',
                'scheduled' => 'Scheduled',
            ],
            'payment_methods' => Trips::query()->select('payment_mode')->distinct()->orderBy('payment_mode')->pluck('payment_mode')->filter()->values(),
            'payment_statuses' => ['Pending', 'Completed', 'Trip Cancelled'],
            'companies' => $companyScoped ? collect() : Company::query()->orderBy('name')->pluck('name', 'id'),
            'drivers' => UserModel::query()->where('user_type', 'Driver')->when($companyScoped, fn ($query) => $query->where('company_id', Auth::guard('company')->id()))->orderBy('first_name')->limit(200)->get(['id', 'first_name', 'last_name']),
            'riders' => UserModel::query()->where('user_type', 'Rider')->orderBy('first_name')->limit(200)->get(['id', 'first_name', 'last_name']),
            'vehicle_types' => CarType::query()->orderBy('car_name')->pluck('car_name', 'id'),
            'locations' => $this->tripLocationOptions($companyScoped),
            'dispatch_modes' => ['manual' => 'Manual', 'auto' => 'Auto', 'global' => 'Global', 'saas' => 'SaaS', 'hybrid' => 'Hybrid'],
            'operator_types' => ['admin' => 'Admin / Global', 'company' => 'Company'],
            'quick_filters' => [
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'this_week' => 'This Week',
                'this_month' => 'This Month',
                'live' => 'Live Trips',
                'payment_issues' => 'Payment Issues',
                'cancelled' => 'Cancelled',
                'no_driver' => 'No Driver',
            ],
        ];
    }

    protected function sumTripColumn($query, string $column): float
    {
        return round((float) $query->sum('trips.'.$column), 2);
    }

    protected function tripLocationOptions(bool $companyScoped)
    {
        if (
            $companyScoped &&
            $this->shouldUseCompanyServiceAreasForTrips()
        ) {
            $companyId = Auth::guard('company')->id();
            return CompanyServiceArea::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->pluck('name', 'id');
        }

        return class_exists(Location::class)
            ? Location::query()->orderBy('name')->pluck('name', 'id')
            : collect();
    }

    protected function shouldUseCompanyServiceAreasForTrips(): bool
    {
        if (
            LOGIN_USER_TYPE !== 'company' ||
            !function_exists('saas_dispatch_enabled') ||
            !saas_dispatch_enabled() ||
            !class_exists('Modules\SaaSDispatch\Entities\CompanyServiceArea')
        ) {
            return false;
        }

        $settings = function_exists('company_saas_settings')
            ? company_saas_settings(Auth::guard('company')->id())
            : null;

        return (bool) optional($settings)->is_saas_enabled && (bool) optional($settings)->use_own_locations;
    }
}
