<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trips;
use App\Services\Corporate\CorporateBillingContext;
use App\Services\Corporate\CorporateBillingPeriod;
use App\Services\Corporate\CorporateExposure;
use App\Services\Corporate\CorporateMobileAccess;
use App\Services\Corporate\CorporateMoney;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;

class CorporateController extends Controller
{
    public function __construct(private CorporateMobileAccess $access) {}

    public function profile(Request $request)
    {
        $user = $this->access->user($request);
        $account = $this->access->account($user, false);
        if (!$account) {
            return $this->success(['corporate' => null, 'default_billing' => ['mode' => 'personal']]);
        }
        $available = true;
        try { app(CorporateBillingContext::class)->resolveForBooking($user); }
        catch (ValidationException) { $available = false; }
        $capabilities = $this->access->capabilities($user);
        $profile = ['account_id' => $account->id, 'account_name' => $account->account_name, 'role' => $user->corporate_role,
            'status' => $account->archived_at ? 'Archived' : $account->status, 'currency_code' => $account->currency_code,
            'billing_timezone' => $account->billing_timezone, 'billing_cycle' => $account->billing_cycle,
            'capabilities' => $capabilities, 'booking_available' => $available];
        if ($capabilities['view_credit']) { $profile['credit'] = app(CorporateExposure::class)->summary($account); }
        return $this->success(['corporate' => $profile, 'default_billing' => ['mode' => 'corporate', 'account_name' => $account->account_name,
            'label_key' => 'corporate.billed_to_company', 'collection_required' => false]]);
    }

    public function dashboard(Request $request)
    {
        $request->validate(['period_start' => 'nullable|date_format:Y-m-d']);
        $user = $this->access->user($request);
        $account = $this->access->account($user);
        $periods = app(CorporateBillingPeriod::class);
        $period = $request->filled('period_start') ? $periods->requested($account, $request->input('period_start')) : $periods->containing($account, now('UTC'));
        $charges = $this->access->trips($account, $user)->whereNotNull('billing_finalized_at')
            ->where('completed_at', '>=', $period['start'])->where('completed_at', '<', $period['end']);
        if ((clone $charges)->where(fn ($q) => $q->whereNull('corporate_currency_code')->orWhere('corporate_currency_code', '!=', $account->currency_code))->exists()) {
            throw ValidationException::withMessages(['currency_code' => __('corporate.finalization_invalid')]);
        }
        $metrics = ['scope' => $this->access->capabilities($user)['view_account_trips'] ? 'account' : 'self',
            'currency_code' => $account->currency_code, 'period_start' => $period['start']->toIso8601String(),
            'period_end' => $period['end']->toIso8601String(), 'billing_timezone' => $period['timezone'],
            'finalized_trip_count' => (clone $charges)->count(),
            'finalized_amount' => CorporateMoney::aggregate((clone $charges)->orderBy('id')->select('id', 'corporate_amount')->lazyById(500)->pluck('corporate_amount'))];
        if ($metrics['scope'] === 'account') {
            $metrics['active_member_count'] = $account->members()->where('user_type', 'Rider')->where('status', 'Active')
                ->whereIn('corporate_role', ['member', 'manager', 'finance'])->count();
            $metrics['credit'] = app(CorporateExposure::class)->summary($account);
        }
        if ($this->access->capabilities($user)['view_invoices']) {
            $invoices = $account->invoices()->where('trip_backed', true);
            $metrics['draft_invoice_count'] = (clone $invoices)->where('status', 'Draft')->count();
            $metrics['issued_unpaid_invoice_count'] = (clone $invoices)->where('status', 'Sent')->count();
            $metrics['issued_unpaid_amount'] = CorporateMoney::aggregate((clone $invoices)->where('status', 'Sent')->orderBy('id')->select('id', 'total_amount')->lazyById(500)->pluck('total_amount'));
        }
        return $this->success(['dashboard' => $metrics]);
    }

    public function trips(Request $request)
    {
        $this->validatePagination($request, ['billing_status' => 'nullable|in:unbilled,invoiced,paid']);
        $user = $this->access->user($request);
        $query = $this->access->trips($this->access->account($user), $user);
        if ($request->filled('billing_status')) { $query->where('billing_status', $request->input('billing_status')); }
        $page = $query->latest('id')->paginate($request->integer('per_page', 20));
        $page->setCollection($page->getCollection()->map(fn ($trip) => $this->tripData($trip)));
        return $this->success(['data' => $page->items(), 'pagination' => $this->pagination($page)]);
    }

    public function trip(Request $request, string $trip)
    {
        $user = $this->access->user($request);
        $record = $this->access->trips($this->access->account($user), $user)->findOrFail($trip);
        $data = $this->tripData($record);
        $data['fare'] = array_intersect_key($record->corporate_financial_snapshot ?? [], array_flip(['base_fare', 'time_fare', 'distance_fare', 'waiting_charge', 'toll_fee', 'tips', 'total_fare']));
        return $this->success(['trip' => $data]);
    }

    public function users(Request $request)
    {
        $this->validatePagination($request);
        $user = $this->access->user($request);
        $account = $this->access->account($user);
        $this->access->requireCapability($user, 'view_users');
        $page = $account->members()->where('user_type', 'Rider')->where('status', 'Active')->whereIn('corporate_role', ['member', 'manager', 'finance'])
            ->orderBy('first_name')->orderBy('id')->paginate($request->integer('per_page', 20));
        $page->setCollection($page->getCollection()->map(fn ($member) => ['id' => $member->id,
            'name' => trim($member->first_name.' '.$member->last_name), 'role' => $member->corporate_role, 'status' => $member->status]));
        return $this->success(['data' => $page->items(), 'pagination' => $this->pagination($page)]);
    }

    public function invoices(Request $request)
    {
        $this->validatePagination($request, ['status' => 'nullable|in:Draft,Sent,Paid,Void']);
        $user = $this->access->user($request);
        $account = $this->access->account($user);
        $this->access->requireCapability($user, 'view_invoices');
        $query = $account->invoices()->where('trip_backed', true);
        if ($request->filled('status')) { $query->where('status', $request->input('status')); }
        $page = $query->latest('id')->paginate($request->integer('per_page', 20));
        $page->setCollection($page->getCollection()->map(fn ($invoice) => $this->invoiceData($invoice)));
        return $this->success(['data' => $page->items(), 'pagination' => $this->pagination($page)]);
    }

    public function invoice(Request $request, string $invoice)
    {
        $record = $this->invoiceFor($request, $invoice);
        $data = $this->invoiceData($record);
        $data['items'] = $record->items->map(fn ($item) => ['trip_id' => $item->trip_id, 'description' => $item->description,
            'pickup_location' => data_get($item->trip_snapshot, 'pickup_location'), 'drop_location' => data_get($item->trip_snapshot, 'drop_location'),
            'completed_at' => data_get($item->trip_snapshot, 'completed_at'), 'amount' => $item->total_amount, 'currency_code' => $item->currency_code])->all();
        return $this->success(['invoice' => $data]);
    }

    public function download(Request $request, string $invoice)
    {
        $record = $this->invoiceFor($request, $invoice);
        return \PDF::loadView('corporate.invoice_pdf', ['invoice' => $record->load('items')])->download($record->invoice_number.'.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    private function invoiceFor(Request $request, string $id): CompanyCorporateInvoice
    {
        $user = $this->access->user($request);
        $account = $this->access->account($user);
        $this->access->requireCapability($user, 'view_invoices');
        return $account->invoices()->where('trip_backed', true)->findOrFail($id);
    }

    private function tripData(Trips $trip): array
    {
        return ['id' => $trip->id, 'pickup_location' => $trip->pickup_location, 'drop_location' => $trip->drop_location,
            'completed_at' => $trip->completed_at?->toIso8601String(), 'status' => $trip->status,
            'amount' => $trip->billing_finalized_at ? $trip->corporate_amount : null, 'currency_code' => $trip->corporate_currency_code ?: $trip->billing['currency_code'],
            'payment_mode' => $trip->payment_mode, 'payment_status' => $trip->payment_status, 'billing' => $trip->billing];
    }

    private function invoiceData(CompanyCorporateInvoice $invoice): array
    {
        return ['id' => $invoice->id, 'invoice_number' => $invoice->invoice_number, 'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
            'due_date' => $invoice->due_date?->format('Y-m-d'), 'status' => $invoice->status, 'amount' => $invoice->total_amount,
            'currency_code' => $invoice->currency_code, 'period_start' => $invoice->period_start?->toIso8601String(),
            'period_end' => $invoice->period_end?->toIso8601String(), 'billing_timezone' => $invoice->billing_timezone, 'revision' => $invoice->revision,
            'pdf_url' => route('corporate.invoices.download', ['invoice' => $invoice->id])];
    }

    private function validatePagination(Request $request, array $extra = []): void
    {
        $request->validate(['page' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|min:1|max:50'] + $extra);
    }

    private function pagination($page): array
    {
        return ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'per_page' => $page->perPage(), 'total' => $page->total()];
    }

    private function success(array $data)
    {
        return response()->json(['status_code' => '1', 'status_message' => __('messages.api.success')] + $data)->header('Cache-Control', 'no-store, private');
    }
}
