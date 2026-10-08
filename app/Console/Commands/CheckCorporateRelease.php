<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class CheckCorporateRelease extends Command
{
    protected $signature = 'corporate:release-check {--schema-only : Check schema/data without runtime delivery checks} {--allow-bookings : Accept an explicitly authorized enabled pilot}';
    protected $description = 'Read-only corporate release integrity and runtime preflight; never sends mail or changes finance';

    public function handle(): int
    {
        $failed = false;
        $check = function (bool $valid, string $label) use (&$failed): void {
            $this->line(($valid ? 'PASS ' : 'FAIL ').$label);
            $failed = $failed || !$valid;
        };
        $check(!$this->option('allow-bookings') ? !config('corporate.bookings_enabled') : true, 'New bookings disabled unless pilot explicitly approved');
        foreach (['saasdispatch.admin.accounts.index', 'saasdispatch.self.accounts.index', 'saasdispatch.companies.accounts.index',
            'corporate.portal.login', 'corporate.portal.dashboard', 'corporate.portal.staff', 'corporate.portal.password.reset'] as $name) {
            $check(Route::has($name), 'Retained management route '.$name);
        }
        $required = [
            'company_corporate_accounts' => ['company_id', 'currency_code', 'billing_timezone', 'archived_at'],
            'trips' => ['corporate_account_id', 'billing_mode', 'billing_status', 'billing_finalized_at', 'corporate_amount', 'corporate_currency_code', 'payment_status'],
            'company_corporate_trip_records' => ['trip_id', 'corporate_account_id', 'payment_id'],
            'company_corporate_invoices' => ['corporate_account_id', 'trip_backed', 'currency_code', 'period_start', 'period_end'],
            'company_corporate_invoice_items' => ['invoice_id', 'trip_id'],
            'company_corporate_invoice_allocations' => ['trip_id', 'invoice_item_id'],
            'company_corporate_settlements' => ['invoice_id', 'amount', 'currency_code'],
            'company_corporate_invoice_deliveries' => ['invoice_id', 'status', 'attempts'],
            'corporate_portal_users' => ['corporate_account_id', 'email', 'password', 'role', 'status', 'session_version', 'password_set_at'],
            'corporate_password_resets' => ['email', 'token', 'created_at'],
        ];
        try {
            $schemaReady = true;
            foreach ($required as $table => $columns) {
                $valid = Schema::hasTable($table) && Schema::hasColumns($table, $columns);
                $check($valid, 'Schema '.$table);
                $schemaReady = $schemaReady && $valid;
            }
            if ($schemaReady) {
                $orphans = DB::table('company_corporate_invoice_items as item')
                    ->leftJoin('company_corporate_invoices as invoice', 'invoice.id', '=', 'item.invoice_id')
                    ->leftJoin('trips as trip', 'trip.id', '=', 'item.trip_id')
                    ->where(fn ($q) => $q->whereNull('invoice.id')->orWhereNull('trip.id')
                        ->orWhereNull('trip.corporate_account_id')
                        ->orWhereColumn('invoice.corporate_account_id', '!=', 'trip.corporate_account_id'))->count();
                $check($orphans === 0, 'Invoice item ownership and trip retention');
                $invalidAllocations = DB::table('company_corporate_invoice_allocations as allocation')
                    ->leftJoin('company_corporate_invoice_items as item', 'item.id', '=', 'allocation.invoice_item_id')
                    ->leftJoin('company_corporate_invoices as invoice', 'invoice.id', '=', 'item.invoice_id')
                    ->where(fn ($q) => $q->whereNull('item.id')->orWhereNull('invoice.id')
                        ->orWhereColumn('allocation.trip_id', '!=', 'item.trip_id')
                        ->orWhere('invoice.status', 'Void'))->count();
                $check($invalidAllocations === 0, 'Active allocation matches retained non-void invoice item');
                $invalid = DB::table('trips')->where('billing_mode', 'corporate')->whereNotNull('billing_finalized_at')
                    ->where(fn ($q) => $q->whereNull('corporate_account_id')->orWhereNull('corporate_amount')->orWhereNull('corporate_currency_code')
                        ->orWhere('corporate_amount', '<', 0)->orWhere('corporate_currency_code', '')
                        ->orWhereNull('billing_status')->orWhereNotIn('billing_status', ['unbilled', 'invoiced', 'paid'])
                        ->orWhereNull('payment_status')
                        ->orWhere(fn ($q) => $q->where('billing_status', 'paid')->where('payment_status', '!=', 'Completed'))
                        ->orWhere(fn ($q) => $q->whereIn('billing_status', ['unbilled', 'invoiced'])->where('payment_status', '!=', 'Deferred')))->count();
                $check($invalid === 0, 'Finalized payer, amount, currency and Deferred/paid status');
                if (DB::getDriverName() === 'mysql') {
                    $type = DB::selectOne("SHOW COLUMNS FROM trips WHERE Field = 'payment_status'")->Type;
                    $check(str_contains($type, "'Deferred'"), 'MySQL Deferred enum');
                    foreach (['company_corporate_trip_records', 'company_corporate_invoice_items', 'company_corporate_invoice_allocations', 'company_corporate_settlements'] as $table) {
                        $foreignKeys = Schema::getForeignKeys($table);
                        $check(count($foreignKeys) > 0 && collect($foreignKeys)->every(fn ($key) => in_array(strtolower($key['on_delete']), ['restrict', 'no action'], true)), 'Restrictive finance foreign keys '.$table);
                    }
                }
            }
            if (!$this->option('schema-only')) {
                $check(DB::getDriverName() === 'mysql', 'Production MySQL schema');
                $check(!config('app.debug'), 'Production debug disabled');
                $check(config('cache.default') === 'redis', 'Shared Redis scheduler locks');
                $check(config('queue.default') === 'redis', 'Durable Redis queue');
                try {
                    $pong = app('redis')->connection()->ping();
                    $check($pong === true || strtoupper((string) $pong) === 'PONG', 'Configured Redis client connects to Redis');
                } catch (\Throwable) {
                    $check(false, 'Configured Redis client connects to Redis');
                }
                $check(!in_array(config('mail.default'), ['log', 'array'], true), 'Real mail transport configured (delivery still requires observation)');
                if (config('mail.default') === 'smtp') {
                    $check(trim((string) config('mail.mailers.smtp.host')) !== '' && (int) config('mail.mailers.smtp.port') > 0, 'SMTP host and port configured');
                }
                $check((string) config('mail.from.address') !== '' && !str_ends_with((string) config('mail.from.address'), '@example.com'), 'Production mail sender');
                $this->line('Host service health, observed scheduler/SMTP and authorized device QA are separate release gates.');
            }
        } catch (\Throwable $e) {
            $check(false, 'Database inspection failed: '.class_basename($e));
        }
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
