<?php

namespace App\Console\Commands;

use App\Services\Corporate\CorporateInvoiceDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeliverCorporateInvoices extends Command
{
    protected $signature = 'corporate:deliver-invoices {--limit=100 : Maximum deliveries per run}';
    protected $description = 'Process and retry the durable corporate invoice email outbox';

    public function handle(CorporateInvoiceDelivery $delivery): int
    {
        $limit = $this->option('limit');
        if (!ctype_digit((string) $limit) || (int) $limit < 1 || (int) $limit > 1000) {
            $this->error(__('corporate.invalid_delivery_limit'));
            return self::FAILURE;
        }
        $ids = DB::table('company_corporate_invoice_deliveries')->where('available_at', '<=', now())
            ->where(fn ($q) => $q->whereIn('status', ['pending', 'failed'])->orWhere(fn ($q) => $q->where('status', 'processing')->where('locked_at', '<=', now()->subMinutes(15))))
            ->orderBy('id')->limit((int) $limit)->pluck('id');
        $failed = false;
        foreach ($ids as $id) {
            try {
                if (!$delivery->deliver($id)) {
                    $failed = $failed || DB::table('company_corporate_invoice_deliveries')->where('id', $id)->value('status') === 'failed';
                }
            } catch (\Throwable $e) {
                $failed = true;
                \Log::warning('Corporate invoice delivery worker failed', ['delivery_id' => $id, 'exception' => get_class($e)]);
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
