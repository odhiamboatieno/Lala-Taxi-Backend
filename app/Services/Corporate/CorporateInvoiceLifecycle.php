<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Trips;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;

class CorporateInvoiceLifecycle
{
    public function __construct(private CorporateFinanceAuthorization $authorization) {}

    public function send(CompanyCorporateInvoice $candidate, Admin|Company $actor): CompanyCorporateInvoice
    {
        return $this->withInvoice($candidate, $actor, function ($invoice, $account, $actorId) {
            if ($invoice->status === 'Sent' || $invoice->status === 'Paid') {
                return $invoice;
            }
            if ($invoice->status !== 'Draft') {
                $this->invalid();
            }
            $recipient = data_get($invoice->bill_to_snapshot, 'contact_email');
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['contact_email' => __('corporate.invalid_recipient')]);
            }
            $this->verifyCharges($invoice);
            DB::table('company_corporate_invoice_deliveries')->insert([
                'invoice_id' => $invoice->id, 'recipient' => $recipient, 'locale' => app()->getLocale(),
                'channel' => 'email', 'status' => 'pending', 'available_at' => now('UTC'), 'created_at' => now('UTC'), 'updated_at' => now('UTC'),
            ]);
            DB::table($invoice->getTable())->where('id', $invoice->id)->update(['status' => 'Sent', 'sent_at' => now('UTC'), 'sent_by' => $actorId, 'updated_at' => now('UTC')]);
            $this->audit($account, $invoice, $actorId, 'invoice.sent', ['recipient' => $recipient, 'channel' => 'email', 'delivery_status' => 'pending']);

            return $invoice->fresh();
        });
    }

    public function settle(CompanyCorporateInvoice $candidate, Admin|Company $actor, string $amount, string $currency, string $date, string $reference): CompanyCorporateInvoice
    {
        try {
            $amount = CorporateMoney::decimal(CorporateMoney::minor($amount));
        } catch (\InvalidArgumentException $e) {
            $this->invalid();
        }
        $reference = trim($reference);
        if (!$reference || mb_strlen($reference) > 191 || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            $this->invalid();
        }
        try {
            $settled = CarbonImmutable::createFromFormat('!Y-m-d', $date, 'UTC');
        } catch (\Exception $e) {
            $this->invalid();
        }
        if (!$settled || $settled->format('Y-m-d') !== $date) {
            $this->invalid();
        }

        return $this->withInvoice($candidate, $actor, function ($invoice, $account, $actorId) use ($amount, $currency, $date, $reference) {
            if ($date > CarbonImmutable::now($invoice->billing_timezone)->toDateString()
                || $currency !== $invoice->currency_code || $amount !== $invoice->total_amount) {
                $this->invalid();
            }
            if ($invoice->status === 'Paid') {
                $existing = DB::table('company_corporate_settlements')->where('invoice_id', $invoice->id)->first();
                if (!$existing || $existing->reference !== $reference || $existing->settlement_date !== $date
                    || CorporateMoney::decimal(CorporateMoney::minor((string) $existing->amount)) !== $amount || $existing->currency_code !== $currency) {
                    $this->invalid();
                }

                return $invoice;
            }
            if ($invoice->status !== 'Sent') {
                $this->invalid();
            }
            $trips = $this->verifyCharges($invoice);
            DB::table('company_corporate_settlements')->insert([
                'invoice_id' => $invoice->id, 'amount' => $amount, 'currency_code' => $currency,
                'reference' => $reference, 'settlement_date' => $date, 'actor' => $actorId, 'created_at' => now('UTC'),
            ]);
            DB::table('trips')->whereIn('id', $trips->pluck('id'))->update(['billing_status' => 'paid', 'payment_status' => 'Completed', 'corporate_settled_at' => now('UTC')]);
            DB::table($invoice->getTable())->where('id', $invoice->id)->update([
                'status' => 'Paid', 'paid_at' => now('UTC'), 'paid_by' => $actorId, 'settlement_reference' => $reference, 'updated_at' => now('UTC'),
            ]);
            $this->audit($account, $invoice, $actorId, 'invoice.paid', ['amount' => $amount, 'currency_code' => $currency, 'settlement_date' => $date, 'reference' => $reference]);

            return $invoice->fresh();
        });
    }

    public function void(CompanyCorporateInvoice $candidate, Admin|Company $actor, string $reason): CompanyCorporateInvoice
    {
        $reason = trim($reason);
        if (!$reason || mb_strlen($reason) > 2000) {
            $this->invalid();
        }

        return $this->withInvoice($candidate, $actor, function ($invoice, $account, $actorId) use ($reason) {
            if ($invoice->status === 'Void' && $invoice->void_reason === $reason) {
                return $invoice;
            }
            if (!in_array($invoice->status, ['Draft', 'Sent'], true)) {
                $this->invalid();
            }
            $delivery = DB::table('company_corporate_invoice_deliveries')->where('invoice_id', $invoice->id)->lockForUpdate()->first();
            if ($delivery && $delivery->status === 'processing' && $delivery->locked_at > now('UTC')->subMinutes(15)->format('Y-m-d H:i:s')) {
                throw ValidationException::withMessages(['invoice' => __('corporate.delivery_in_progress')]);
            }
            $trips = $this->verifyCharges($invoice);
            // Only active allocations are released; immutable items and the old invoice remain.
            DB::table('company_corporate_invoice_allocations')->whereIn('invoice_item_id', $invoice->items->pluck('id'))->delete();
            DB::table('trips')->whereIn('id', $trips->pluck('id'))->update(['billing_status' => 'unbilled', 'payment_status' => 'Deferred']);
            DB::table($invoice->getTable())->where('id', $invoice->id)->update([
                'status' => 'Void', 'voided_at' => now('UTC'), 'voided_by' => $actorId, 'void_reason' => $reason, 'updated_at' => now('UTC'),
            ]);
            DB::table('company_corporate_invoice_deliveries')->where('invoice_id', $invoice->id)->whereIn('status', ['pending', 'failed', 'processing'])->update(['status' => 'cancelled', 'locked_at' => null, 'updated_at' => now('UTC')]);
            $this->audit($account, $invoice, $actorId, 'invoice.voided', ['reason' => $reason]);

            return $invoice->fresh();
        });
    }

    private function withInvoice(CompanyCorporateInvoice $candidate, Admin|Company $actor, \Closure $operation): CompanyCorporateInvoice
    {
        return DB::transaction(function () use ($candidate, $actor, $operation) {
            $account = CompanyCorporateAccount::lockForUpdate()->findOrFail($candidate->corporate_account_id);
            $this->authorization->authorize($account, $actor);
            $invoice = CompanyCorporateInvoice::lockForUpdate()->findOrFail($candidate->id);
            if (!$invoice->trip_backed || (int) $invoice->corporate_account_id !== (int) $account->id || (int) $invoice->company_id !== (int) $account->company_id) {
                $this->invalid();
            }

            return $operation($invoice, $account, $this->authorization->identifier($actor));
        }, 3);
    }

    private function verifyCharges(CompanyCorporateInvoice $invoice)
    {
        $items = $invoice->items()->orderBy('trip_id')->lockForUpdate()->get();
        if ($items->isEmpty() || CorporateMoney::sum($items->pluck('total_amount')) !== $invoice->total_amount
            || CorporateMoney::sum($items->pluck('amount')) !== $invoice->subtotal || CorporateMoney::sum($items->pluck('tax_amount')) !== $invoice->tax_amount) {
            $this->invalid();
        }
        $allocations = DB::table('company_corporate_invoice_allocations')->whereIn('invoice_item_id', $items->pluck('id'))->lockForUpdate()->get()->keyBy('trip_id');
        $trips = Trips::whereIn('id', $items->pluck('trip_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        if ($allocations->count() !== $items->count() || $trips->count() !== $items->count()) {
            $this->invalid();
        }
        foreach ($items as $item) {
            $trip = $trips[$item->trip_id];
            if ($item->currency_code !== $invoice->currency_code || $item->amount !== $trip->corporate_amount
                || $trip->corporate_currency_code !== $invoice->currency_code || !$trip->billing_finalized_at || !$trip->isCorporate()
                || (int) $trip->corporate_account_id !== (int) $invoice->corporate_account_id
                || $trip->billing_status !== 'invoiced' || $trip->payment_status !== 'Deferred'
                || (int) $allocations[$item->trip_id]->invoice_item_id !== (int) $item->id) {
                $this->invalid();
            }
        }
        $invoice->setRelation('items', $items);

        return $trips;
    }

    private function audit($account, $invoice, string $actor, string $action, array $data): void
    {
        CompanyCorporateAudit::create(['corporate_account_id' => $account->id, 'action' => $action, 'actor' => $actor,
            'reference' => 'invoice:'.$invoice->id, 'data' => $data, 'created_at' => now('UTC')]);
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['invoice' => __('corporate.invoice_inconsistent')]);
    }
}
