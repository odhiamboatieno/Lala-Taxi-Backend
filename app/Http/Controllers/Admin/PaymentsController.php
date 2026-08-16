<?php

/**
 * Payments Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Payments
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\InvoiceHelper;
use App\Mail\MailQueue;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Trips;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;

class PaymentsController extends Controller
{
    protected InvoiceHelper $invoiceHelper;

    public function __construct(InvoiceHelper $invoiceHelper)
    {
        $this->invoiceHelper = $invoiceHelper;
    }

    public function index(Request $request)
    {
        $defaultCurrency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE == 'company' && session()->get('currency') != null) {
            $defaultCurrency = Currency::whereCode(session()->get('currency'))->first() ?: $defaultCurrency;
        }

        $symbol = $defaultCurrency->symbol ?? '$';
        $currencyRate = $defaultCurrency->rate ?? 1;
        $search = trim((string) $request->get('search'));

        $payments = DB::table('trips')
            ->join('currency', 'currency.code', '=', 'trips.currency_code')
            ->leftJoin('users as driver', 'driver.id', '=', 'trips.driver_id')
            ->leftJoin('users as rider', 'rider.id', '=', 'trips.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'driver.company_id')
            ->select([
                'trips.id',
                'trips.created_at as trip_date',
                'trips.payment_status',
                'trips.status',
                'trips.payment_mode',
                'companies.name as company_name',
                DB::raw("CONCAT(COALESCE(driver.first_name,''), ' ', COALESCE(driver.last_name,'')) as driver_name"),
                DB::raw("CONCAT(COALESCE(rider.first_name,''), ' ', COALESCE(rider.last_name,'')) as rider_name"),
                DB::raw("ROUND(((trips.time_fare / currency.rate) * {$currencyRate}), 2) as time_fare_amount"),
                DB::raw("ROUND(((trips.distance_fare / currency.rate) * {$currencyRate}), 2) as distance_fare_amount"),
                DB::raw("ROUND(((trips.base_fare / currency.rate) * {$currencyRate}), 2) as base_fare_amount"),
                DB::raw("ROUND(((trips.additional_rider_amount / currency.rate) * {$currencyRate}), 2) as additional_rider_amount"),
                DB::raw("ROUND(((trips.access_fee / currency.rate) * {$currencyRate}), 2) as access_fee_amount"),
                DB::raw("ROUND(((trips.tips / currency.rate) * {$currencyRate}), 2) as tips_amount"),
                DB::raw("ROUND(((trips.toll_fee / currency.rate) * {$currencyRate}), 2) as toll_fee_amount"),
                DB::raw("ROUND(((trips.driver_payout / currency.rate) * {$currencyRate}), 2) as driver_payout_amount"),
                DB::raw("ROUND((((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * {$currencyRate}), 2) as commission_amount"),
                DB::raw("ROUND((((trips.subtotal_fare + trips.peak_amount + trips.access_fee + trips.schedule_fare + IF(trips.payment_status='Completed', trips.tips, 0) - trips.toll_fee + trips.waiting_charge) / currency.rate) * {$currencyRate}), 2) as total_fare_amount"),
            ]);

        if (LOGIN_USER_TYPE == 'company') {
            $payments->where('driver.company_id', auth('company')->id());
        }

        if ($search !== '') {
            $payments->where(function ($query) use ($search) {
                $query->where('trips.id', 'like', "%{$search}%")
                    ->orWhere('trips.status', 'like', "%{$search}%")
                    ->orWhere('trips.payment_status', 'like', "%{$search}%")
                    ->orWhere('trips.payment_mode', 'like', "%{$search}%")
                    ->orWhere('driver.first_name', 'like', "%{$search}%")
                    ->orWhere('rider.first_name', 'like', "%{$search}%")
                    ->orWhere('companies.name', 'like', "%{$search}%");
            });
        }

        $payments = $payments->orderByDesc('trips.created_at')->paginate(20)->withQueryString();
        $payments->getCollection()->transform(function ($payment) use ($symbol) {
            foreach ([
                'time_fare_amount',
                'distance_fare_amount',
                'base_fare_amount',
                'additional_rider_amount',
                'access_fee_amount',
                'tips_amount',
                'toll_fee_amount',
                'driver_payout_amount',
                'commission_amount',
                'total_fare_amount',
            ] as $field) {
                $payment->{$field.'_display'} = $symbol.number_format((float) $payment->{$field}, 2);
            }
            return $payment;
        });

        return view('admin.payments.payments', [
            'payments' => $payments,
            'search' => $search,
        ]);
    }

    public function view(int $id)
    {
        $result = $this->resolveTripForPayment($id);
        $invoiceData = $this->invoiceHelper->getWebInvoice($result);

        return view('admin.payments.view', [
            'result' => $result,
            'invoice_data' => $invoiceData,
            'printReceiptUrl' => url(LOGIN_USER_TYPE.'/view_payments/'.$id.'/receipt/print'),
            'downloadReceiptUrl' => url(LOGIN_USER_TYPE.'/view_payments/'.$id.'/receipt/download'),
            'sendReceiptUrl' => url(LOGIN_USER_TYPE.'/view_payments/'.$id.'/receipt/send'),
            'mapKeyAvailable' => !empty(site_settings('map_key') ?? ''),
        ]);
    }

    public function printReceipt(int $id)
    {
        $result = $this->resolveTripForPayment($id);
        $invoice_data = $this->invoiceHelper->getWebInvoice($result);

        return view('admin.payments.receipt_print', compact('result', 'invoice_data'));
    }

    public function downloadReceipt(int $id)
    {
        $result = $this->resolveTripForPayment($id);
        $invoice_data = $this->invoiceHelper->getWebInvoice($result);

        $pdf = PDF::loadView('admin.payments.receipt_pdf', compact('result', 'invoice_data'));

        return $pdf->download('trip-receipt-'.$result->id.'.pdf');
    }

    public function sendReceipt(int $id)
    {
        $result = $this->resolveTripForPayment($id);
        $invoiceData = $this->invoiceHelper->getWebInvoice($result);
        $rider = $result->users;

        if (!optional($rider)->email) {
            flashMessage('danger', 'Rider email is not available for this trip.');
            return redirect()->back();
        }

        $downloadUrl = url('download_rider_invoice/'.$result->id);
        $viewUrl = url('trip_invoice/'.$result->id);
        $content = view('emails.trip_receipt', [
            'trip' => $result,
            'invoice_data' => $invoiceData,
            'downloadUrl' => $downloadUrl,
            'viewUrl' => $viewUrl,
        ])->render();

        Mail::to($rider->email, trim(($rider->first_name ?? '').' '.($rider->last_name ?? '')))
            ->send(new MailQueue([
                'locale' => app()->getLocale(),
                'subject' => 'Trip Receipt #'.$result->id,
                'view_file' => 'emails.custom_email',
                'first_name' => $rider->first_name ?? 'Customer',
                'content' => $content,
            ]));

        flashMessage('success', 'Receipt sent to rider successfully.');
        return redirect()->back();
    }

    protected function resolveTripForPayment(int $id): Trips
    {
        $payment = Payment::query()->find($id);
        $tripId = (int) ($payment?->trip_id ?: $id);

        return Trips::query()
            ->with([
                'car_type',
                'users',
                'driver.default_payout_credentials',
                'driver.company',
                'currency',
                'cancel.cancel_reason',
                'driver_payment',
            ])
            ->when(LOGIN_USER_TYPE == 'company', function ($query) {
                $query->whereHas('driver', function ($driverQuery) {
                    $driverQuery->where('company_id', auth('company')->id());
                });
            })
            ->where(function ($query) use ($tripId, $id) {
                $query->where('id', $tripId)
                    ->orWhereHas('driver_payment', function ($paymentQuery) use ($id) {
                        $paymentQuery->where('id', $id);
                    });
            })
            ->firstOrFail();
    }
}
