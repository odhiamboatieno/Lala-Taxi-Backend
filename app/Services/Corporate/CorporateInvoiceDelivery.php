<?php

namespace App\Services\Corporate;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;

class CorporateInvoiceDelivery
{
    /** Durable outbox: retries never create another invoice or settlement. */
    public function deliver(int $id): bool
    {
        $candidate = DB::table('company_corporate_invoice_deliveries')->find($id);
        if (!$candidate) {
            return false;
        }
        $candidateInvoice = CompanyCorporateInvoice::findOrFail($candidate->invoice_id);
        $claimed = DB::transaction(function () use ($id, $candidateInvoice) {
            CompanyCorporateAccount::lockForUpdate()->findOrFail($candidateInvoice->corporate_account_id);
            $invoice = CompanyCorporateInvoice::lockForUpdate()->findOrFail($candidateInvoice->id);
            $delivery = DB::table('company_corporate_invoice_deliveries')->where('id', $id)->lockForUpdate()->first();
            if (!$delivery || in_array($delivery->status, ['delivered', 'cancelled'], true)
                || $delivery->available_at > now('UTC')->format('Y-m-d H:i:s')
                || ($delivery->status === 'processing' && $delivery->locked_at > now('UTC')->subMinutes(15)->format('Y-m-d H:i:s'))) {
                return null;
            }
            if (!in_array($invoice->status, ['Sent', 'Paid'], true)) {
                DB::table('company_corporate_invoice_deliveries')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now('UTC')]);
                return null;
            }
            DB::table('company_corporate_invoice_deliveries')->where('id', $id)->update([
                'status' => 'processing', 'locked_at' => now('UTC'), 'attempts' => $delivery->attempts + 1, 'updated_at' => now('UTC'),
            ]);

            return ['invoice' => $invoice->load('items'), 'delivery' => $delivery];
        }, 3);
        if (!$claimed) {
            return false;
        }
        $originalLocale = app()->getLocale();
        try {
            $invoice = $claimed['invoice'];
            $delivery = $claimed['delivery'];
            app()->setLocale($delivery->locale);
            $pdf = \PDF::loadView('corporate.invoice_pdf', compact('invoice'))->output();
            Mail::to($delivery->recipient)->send((new \App\Mail\CorporateInvoiceMail($invoice, $pdf))->locale($delivery->locale));
            DB::transaction(function () use ($id, $invoice, $delivery) {
                CompanyCorporateAccount::lockForUpdate()->findOrFail($invoice->corporate_account_id);
                $updated = DB::table('company_corporate_invoice_deliveries')->where('id', $id)->where('status', 'processing')->where('attempts', $delivery->attempts + 1)->update([
                    'status' => 'delivered', 'delivered_at' => now('UTC'), 'locked_at' => null, 'last_error' => null, 'updated_at' => now('UTC'),
                ]);
                if (!$updated) {
                    return;
                }
                CompanyCorporateAudit::create(['corporate_account_id' => $invoice->corporate_account_id, 'action' => 'invoice.delivered',
                    'actor' => 'system', 'reference' => 'invoice:'.$invoice->id, 'data' => ['recipient' => $delivery->recipient, 'channel' => $delivery->channel], 'created_at' => now('UTC')]);
            }, 3);

            return true;
        } catch (\Throwable $e) {
            DB::table('company_corporate_invoice_deliveries')->where('id', $id)->where('status', 'processing')->where('attempts', $claimed['delivery']->attempts + 1)->update([
                'status' => 'failed', 'locked_at' => null, 'available_at' => now('UTC')->addMinutes(5),
                'last_error' => substr(get_class($e), 0, 100), 'updated_at' => now('UTC'),
            ]);
            \Log::warning('Corporate invoice delivery failed', ['delivery_id' => $id, 'exception' => get_class($e)]);

            return false;
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
