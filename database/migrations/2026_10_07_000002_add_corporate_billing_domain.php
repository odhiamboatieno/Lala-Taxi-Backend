<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check legacy headers before any MySQL DDL; never repair financial ownership by guessing.
        if (DB::table('company_corporate_accounts as a')->leftJoin('companies as c', 'c.id', '=', 'a.company_id')->whereNull('c.id')->exists()
            || DB::table('company_corporate_invoices as i')->leftJoin('company_corporate_accounts as a', 'a.id', '=', 'i.corporate_account_id')
                ->where(fn ($q) => $q->whereNull('a.id')->orWhereColumn('a.company_id', '<>', 'i.company_id'))->exists()) {
            throw new RuntimeException('Corporate account/invoice ownership is inconsistent. Reconcile the legacy records before migration.');
        }

        $this->columns('company_corporate_accounts', [
            'currency_code' => fn ($t) => $t->string('currency_code', 10)->nullable(),
            'billing_timezone' => fn ($t) => $t->string('billing_timezone', 64)->default('UTC'),
            'payment_terms_days' => fn ($t) => $t->unsignedSmallInteger('payment_terms_days')->default(30),
            'legal_name' => fn ($t) => $t->string('legal_name', 191)->nullable(),
            'billing_address' => fn ($t) => $t->text('billing_address')->nullable(),
            'archived_at' => fn ($t) => $t->timestamp('archived_at')->nullable(),
            'archived_by' => fn ($t) => $t->string('archived_by', 100)->nullable(),
        ]);
        $this->index('company_corporate_accounts', 'cca_owner_id_unique', fn ($t) => $t->unique(['company_id', 'id'], 'cca_owner_id_unique'));
        $this->foreign('company_corporate_accounts', 'cca_company_fk', ['company_id'], 'companies', ['id']);
        $this->foreign('company_corporate_accounts', 'cca_currency_fk', ['currency_code'], 'currency', ['code']);

        $this->columns('users', [
            'corporate_account_id' => fn ($t) => $t->unsignedBigInteger('corporate_account_id')->nullable(),
            'corporate_role' => fn ($t) => $t->string('corporate_role', 20)->nullable(),
        ]);
        $this->foreign('users', 'users_corporate_account_fk', ['corporate_account_id'], 'company_corporate_accounts', ['id']);

        foreach (['schedule_ride', 'request', 'trips'] as $table) {
            $this->columns($table, [
                'billing_mode' => fn ($t) => $t->string('billing_mode', 20)->default('personal'),
                'corporate_account_id' => fn ($t) => $t->unsignedBigInteger('corporate_account_id')->nullable(),
                'billing_snapshot' => fn ($t) => $t->json('billing_snapshot')->nullable(),
            ]);
            $this->foreign($table, $table.'_corporate_account_fk', ['corporate_account_id'], 'company_corporate_accounts', ['id']);
        }
        $this->columns('trips', [
            'billing_status' => fn ($t) => $t->string('billing_status', 20)->nullable(),
            'billing_finalized_at' => fn ($t) => $t->timestamp('billing_finalized_at')->nullable(),
            'corporate_amount' => fn ($t) => $t->decimal('corporate_amount', 12, 2)->nullable(),
            'corporate_currency_code' => fn ($t) => $t->string('corporate_currency_code', 10)->nullable(),
            'corporate_settled_at' => fn ($t) => $t->timestamp('corporate_settled_at')->nullable(),
        ]);
        $this->index('trips', 'trips_corporate_billing_idx', fn ($t) => $t->index(['corporate_account_id', 'corporate_currency_code', 'billing_status', 'billing_finalized_at'], 'trips_corporate_billing_idx'));
        if (DB::getDriverName() === 'mysql') {
            $type = DB::selectOne("SHOW COLUMNS FROM trips WHERE Field = 'payment_status'")->Type;
            preg_match_all("/'([^']*)'/", $type, $matches);
            $values = $matches[1];
            if (!$values) {
                throw new RuntimeException('Expected trips.payment_status enum; review the deployed schema before migration.');
            }
            if (!in_array('Deferred', $values, true)) {
                $values[] = 'Deferred';
                Schema::table('trips', fn (Blueprint $t) => $t->enum('payment_status', $values)->default('Pending')->change());
            }
        }

        $this->columns('company_corporate_invoices', [
            'trip_backed' => fn ($t) => $t->boolean('trip_backed')->default(false),
            'currency_code' => fn ($t) => $t->string('currency_code', 10)->nullable(),
            'billing_timezone' => fn ($t) => $t->string('billing_timezone', 64)->nullable(),
            'bill_to_snapshot' => fn ($t) => $t->json('bill_to_snapshot')->nullable(),
            'period_start' => fn ($t) => $t->timestamp('period_start')->nullable(),
            'period_end' => fn ($t) => $t->timestamp('period_end')->nullable(),
            'revision' => fn ($t) => $t->unsignedInteger('revision')->default(1),
            'sent_at' => fn ($t) => $t->timestamp('sent_at')->nullable(),
            'paid_at' => fn ($t) => $t->timestamp('paid_at')->nullable(),
            'voided_at' => fn ($t) => $t->timestamp('voided_at')->nullable(),
            'sent_by' => fn ($t) => $t->string('sent_by', 100)->nullable(),
            'paid_by' => fn ($t) => $t->string('paid_by', 100)->nullable(),
            'voided_by' => fn ($t) => $t->string('voided_by', 100)->nullable(),
            'void_reason' => fn ($t) => $t->text('void_reason')->nullable(),
            'settlement_reference' => fn ($t) => $t->string('settlement_reference', 191)->nullable(),
        ]);
        $this->foreign('company_corporate_invoices', 'cci_owner_account_fk', ['company_id', 'corporate_account_id'], 'company_corporate_accounts', ['company_id', 'id']);

        // A durable accepted-trip record blocks existing parent cascades before invoicing.
        if (!Schema::hasTable('company_corporate_trip_records')) {
            Schema::create('company_corporate_trip_records', function (Blueprint $t) {
                $t->unsignedInteger('trip_id')->primary();
                $t->unsignedBigInteger('corporate_account_id');
                $t->timestamp('created_at');
                $t->foreign('trip_id', 'cctr_trip_fk')->references('id')->on('trips')->restrictOnDelete();
                $t->foreign('corporate_account_id', 'cctr_account_fk')->references('id')->on('company_corporate_accounts')->restrictOnDelete();
            });
        }
        if (!Schema::hasTable('company_corporate_invoice_items')) {
            Schema::create('company_corporate_invoice_items', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('invoice_id');
                $t->unsignedInteger('trip_id');
                $t->decimal('amount', 12, 2);
                $t->decimal('tax_amount', 12, 2)->default('0.00');
                $t->decimal('total_amount', 12, 2);
                $t->string('currency_code', 10);
                $t->string('description', 255);
                $t->json('trip_snapshot');
                $t->timestamps();
                $t->unique(['invoice_id', 'trip_id'], 'ccii_invoice_trip_unique');
                $t->foreign('invoice_id', 'ccii_invoice_fk')->references('id')->on('company_corporate_invoices')->restrictOnDelete();
                $t->foreign('trip_id', 'ccii_trip_fk')->references('id')->on('trips')->restrictOnDelete();
            });
        }
        if (!Schema::hasTable('company_corporate_invoice_allocations')) {
            Schema::create('company_corporate_invoice_allocations', function (Blueprint $t) {
                $t->unsignedInteger('trip_id')->primary();
                $t->unsignedBigInteger('invoice_item_id')->unique();
                $t->timestamp('created_at');
                $t->foreign('trip_id', 'ccia_trip_fk')->references('id')->on('trips')->restrictOnDelete();
                $t->foreign('invoice_item_id', 'ccia_item_fk')->references('id')->on('company_corporate_invoice_items')->restrictOnDelete();
            });
        }
        if (!Schema::hasTable('company_corporate_audits')) {
            Schema::create('company_corporate_audits', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('corporate_account_id');
                $t->string('action', 50);
                $t->string('actor', 100);
                $t->string('reference', 191)->nullable();
                $t->json('data');
                $t->timestamp('created_at');
                $t->index(['corporate_account_id', 'created_at'], 'ccaudit_account_time_idx');
                $t->foreign('corporate_account_id', 'ccaudit_account_fk')->references('id')->on('company_corporate_accounts')->restrictOnDelete();
            });
        }
    }

    private function columns(string $table, array $columns): void
    {
        foreach ($columns as $name => $definition) {
            if (!Schema::hasColumn($table, $name)) {
                Schema::table($table, $definition);
            }
        }
    }

    private function index(string $table, string $name, Closure $definition): void
    {
        if (!collect(Schema::getIndexes($table))->contains('name', $name)) {
            Schema::table($table, $definition);
        }
    }

    private function foreign(string $table, string $name, array $columns, string $parent, array $references): void
    {
        $exists = collect(Schema::getForeignKeys($table))->contains(fn ($key) => $key['name'] === $name
            || ($key['columns'] === $columns && $key['foreign_table'] === $parent && $key['foreign_columns'] === $references));
        if (!$exists) {
            Schema::table($table, fn (Blueprint $t) => $t->foreign($columns, $name)->references($references)->on($parent)->restrictOnDelete());
        }
    }

    public function down(): void
    {
        // Financial evidence and additive columns are retained on application rollback.
    }
};
