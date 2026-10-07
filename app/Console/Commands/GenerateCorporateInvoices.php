<?php

namespace App\Console\Commands;

use App\Models\Trips;
use App\Services\Corporate\CorporateBillingPeriod;
use App\Services\Corporate\CorporateInvoiceGenerator;
use Illuminate\Console\Command;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

class GenerateCorporateInvoices extends Command
{
    protected $signature = 'corporate:generate-invoices {--account= : Corporate account ID} {--period-start= : Cycle start YYYY-MM-DD} {--dry-run : Preview without saving}';
    protected $description = 'Generate trip-backed invoices for closed corporate billing periods';

    public function handle(CorporateInvoiceGenerator $generator, CorporateBillingPeriod $periods): int
    {
        $id = $this->option('account');
        if ($id !== null && (!ctype_digit((string) $id) || !CompanyCorporateAccount::whereKey($id)->exists())) {
            $this->error(__('corporate.account_not_found'));
            return self::FAILURE;
        }
        $failed = false;
        CompanyCorporateAccount::when($id !== null, fn ($q) => $q->whereKey($id))->orderBy('id')->chunkById(100, function ($accounts) use ($generator, $periods, &$failed) {
            foreach ($accounts as $account) {
                try {
                    if ($this->option('period-start')) {
                        $starts = [$this->option('period-start')];
                    } else {
                        $starts = [];
                        Trips::where('corporate_account_id', $account->id)->where('billing_mode', 'corporate')->where('billing_status', 'unbilled')
                            ->whereNotNull('billing_finalized_at')->whereNotNull('completed_at')->select('id', 'completed_at')
                            ->orderBy('id')->chunkById(500, function ($trips) use ($account, $periods, &$starts) {
                                foreach ($trips as $trip) {
                                    $period = $periods->containing($account, $trip->completed_at);
                                    if (!$period['end']->isFuture()) {
                                        $start = $period['start']->setTimezone($period['timezone'])->toDateString();
                                        $starts[$start] = $start;
                                    }
                                }
                            });
                        sort($starts);
                    }
                    foreach ($starts as $start) {
                        $invoice = $generator->generate($account, $start, (bool) $this->option('dry-run'));
                        if ($invoice) {
                            $this->info(__('corporate.generated_invoice', ['id' => $account->id, 'number' => $invoice->invoice_number, 'amount' => $invoice->currency_code.' '.$invoice->total_amount]));
                        }
                    }
                } catch (\Throwable $e) {
                    $failed = true;
                    $this->error(__('corporate.generation_failed', ['id' => $account->id]));
                    \Log::warning('Corporate invoice generation failed', ['account_id' => $account->id, 'exception' => get_class($e)]);
                }
            }
        });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
