<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Trips;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoiceItem;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoiceAllocation;

class CorporateInvoiceGenerator
{
    public function __construct(private CorporateBillingPeriod $periods, private CorporateFinanceAuthorization $authorization) {}

    /** Null actor is reserved for the trusted scheduler/Artisan path, not HTTP input. */
    public function generate(CompanyCorporateAccount $candidate, string $periodStart, bool $dryRun = false, Admin|Company|null $actor = null): ?CompanyCorporateInvoice
    {
        return DB::transaction(function () use ($candidate, $periodStart, $dryRun, $actor) {
            $account = CompanyCorporateAccount::lockForUpdate()->findOrFail($candidate->id);
            if ($actor) {
                $this->authorization->authorize($account, $actor);
            }
            $period = $this->periods->requested($account, $periodStart);
            if ($period['end']->isAfter(CarbonImmutable::now('UTC'))) {
                throw ValidationException::withMessages(['period_start' => __('corporate.period_not_closed')]);
            }
            $trips = Trips::where('corporate_account_id', $account->id)->where('billing_mode', 'corporate')
                ->where('billing_status', 'unbilled')->where('payment_status', 'Deferred')->whereNotNull('billing_finalized_at')
                ->where('completed_at', '>=', $period['start'])->where('completed_at', '<', $period['end'])
                ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('company_corporate_invoice_allocations')->whereColumn('trip_id', 'trips.id'))
                ->orderBy('id')->lockForUpdate()->get();
            if ($trips->isEmpty()) {
                return null;
            }
            foreach ($trips as $trip) {
                if (!$account->currency_code || $trip->corporate_currency_code !== $account->currency_code
                    || (int) data_get($trip->billing_snapshot, 'company_id') !== (int) $account->company_id
                    || !$trip->corporate_financial_snapshot) {
                    throw ValidationException::withMessages(['account' => __('corporate.invoice_inconsistent')]);
                }
            }
            $total = CorporateMoney::sum($trips->pluck('corporate_amount'));
            $revision = ((int) $account->invoices()->where('period_start', $period['start'])->where('period_end', $period['end'])->max('revision')) + 1;
            $today = CarbonImmutable::now($period['timezone']);
            $invoice = new CompanyCorporateInvoice([
                'company_id' => $account->company_id, 'corporate_account_id' => $account->id,
                'invoice_number' => 'CORP-'.$today->format('Ymd').'-R'.$revision.'-'.Str::ulid(),
                'invoice_date' => $today->toDateString(), 'due_date' => $today->addDays($account->payment_terms_days)->toDateString(),
                'subtotal' => $total, 'tax_amount' => '0.00', 'total_amount' => $total, 'status' => 'Draft', 'trip_backed' => true,
                'currency_code' => $account->currency_code, 'billing_timezone' => $period['timezone'],
                'bill_to_snapshot' => $account->only(['account_name', 'legal_name', 'billing_address', 'contact_name', 'contact_email', 'contact_phone', 'payment_terms_days']),
                'period_start' => $period['start'], 'period_end' => $period['end'], 'revision' => $revision,
            ]);
            if ($dryRun) {
                return $invoice;
            }
            $invoice->save();
            foreach ($trips as $trip) {
                $item = CompanyCorporateInvoiceItem::create([
                    'invoice_id' => $invoice->id, 'trip_id' => $trip->id, 'amount' => $trip->corporate_amount,
                    'tax_amount' => '0.00', 'total_amount' => $trip->corporate_amount, 'currency_code' => $trip->corporate_currency_code,
                    'description' => __('corporate.trip_charge', ['id' => $trip->id]),
                    'trip_snapshot' => ['trip_id' => $trip->id, 'rider_id' => $trip->user_id, 'driver_id' => $trip->driver_id,
                        'completed_at' => $trip->completed_at->toIso8601String(), 'pickup_location' => $trip->pickup_location,
                        'drop_location' => $trip->drop_location, 'bill_to' => $trip->billing_snapshot, 'financial' => $trip->corporate_financial_snapshot],
                ]);
                CompanyCorporateInvoiceAllocation::create(['trip_id' => $trip->id, 'invoice_item_id' => $item->id, 'created_at' => now('UTC')]);
                DB::table('trips')->where('id', $trip->id)->update(['billing_status' => 'invoiced']);
            }
            CompanyCorporateAudit::create(['corporate_account_id' => $account->id, 'action' => 'invoice.generated',
                'actor' => $this->authorization->identifier($actor), 'reference' => 'invoice:'.$invoice->id,
                'data' => ['number' => $invoice->invoice_number, 'amount' => $total, 'currency_code' => $invoice->currency_code, 'revision' => $revision], 'created_at' => now('UTC')]);

            return $invoice->load('items');
        }, 3);
    }
}
