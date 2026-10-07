<?php

namespace App\Console\Commands;

use App\Http\Helper\InvoiceHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileCorporateTrips extends Command
{
    protected $signature = 'corporate:reconcile-trips {--account= : Corporate account ID} {--dry-run : Report without finalizing}';
    protected $description = 'Recover corporate financial finalization after physical completion';

    public function handle(): int
    {
        $id = $this->option('account');
        if ($id !== null && !ctype_digit((string) $id)) {
            $this->error(__('corporate.account_not_found'));
            return self::FAILURE;
        }
        $failed = false;
        DB::table('trips')->where('billing_mode', 'corporate')->whereNull('billing_finalized_at')->whereNotNull('completed_at')->whereNotNull('end_trip')
            ->when($id !== null, fn ($q) => $q->where('corporate_account_id', $id))->orderBy('id')->chunkById(100, function ($trips) use (&$failed) {
                foreach ($trips as $trip) {
                    try {
                        $this->line(__('corporate.reconcile_trip', ['id' => $trip->id]));
                        if (!$this->option('dry-run')) {
                            app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
                        }
                    } catch (\Throwable $e) {
                        $failed = true;
                        $this->error(__('corporate.reconciliation_failed', ['id' => $trip->id]));
                        \Log::warning('Corporate trip reconciliation failed', ['trip_id' => $trip->id, 'exception' => get_class($e)]);
                    }
                }
            });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
