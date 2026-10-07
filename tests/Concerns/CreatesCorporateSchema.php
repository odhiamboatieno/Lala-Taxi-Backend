<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesCorporateSchema
{
    protected function createCorporateSchema(bool $migrate = true): void
    {
        if (DB::getDriverName() !== 'sqlite' && DB::connection()->getDatabaseName() !== 'lala_corporate_schema_test') {
            throw new \RuntimeException('Corporate schema tests require an isolated test database.');
        }
        Schema::create('companies', function (Blueprint $t) {
            $t->increments('id'); $t->string('name'); $t->string('status')->default('Active');
        });
        Schema::create('currency', function (Blueprint $t) {
            $t->increments('id'); $t->string('code', 10)->unique(); $t->string('status')->default('Active');
            $t->string('default_currency')->default('1'); $t->string('symbol')->default('KES'); $t->decimal('rate', 10, 2)->default(1);
        });
        Schema::create('users', function (Blueprint $t) {
            $t->increments('id'); $t->unsignedInteger('company_id')->nullable();
            $t->string('user_type')->default('Rider'); $t->string('status')->default('Active');
            $t->string('first_name')->default('Rider'); $t->string('last_name')->default('Test');
            $t->string('currency_code', 10)->default('KES'); $t->timestamp('deleted_at')->nullable(); $t->timestamps();
        });
        Schema::create('request', function (Blueprint $t) {
            $t->increments('id'); $t->unsignedInteger('user_id')->nullable(); $t->unsignedInteger('driver_id')->nullable();
            $t->unsignedInteger('group_id')->nullable(); $t->string('schedule_id')->nullable();
            $t->string('status')->default('Pending'); $t->string('service_category')->default('ride');
            $t->unsignedInteger('car_id')->nullable(); $t->timestamp('deleted_at')->nullable();
            $t->decimal('estimated_fare', 5, 2)->default('0.00'); $t->string('payment_mode')->default('Cash'); $t->timestamps();
        });
        Schema::create('schedule_ride', function (Blueprint $t) {
            $t->increments('id'); $t->string('payment_method')->default('Cash');
            $t->unsignedInteger('user_id')->nullable(); $t->unsignedInteger('car_id')->nullable();
            $t->string('service_category')->default('ride'); $t->string('is_wallet')->default('No');
            $t->string('status')->default('Pending'); $t->string('schedule_date')->nullable(); $t->string('schedule_time')->nullable();
            $t->string('assignment_status')->nullable();
        });
        Schema::create('trips', function (Blueprint $t) {
            $t->increments('id'); $t->unsignedInteger('user_id'); $t->unsignedInteger('driver_id')->nullable();
            $t->unsignedInteger('request_id')->nullable();
            $t->string('status')->default('Payment'); $t->string('payment_mode')->default('Cash');
            $t->enum('payment_status', ['Pending', 'Completed', 'Trip Cancelled'])->default('Pending');
            $t->string('currency_code', 10)->default('KES'); $t->decimal('total_fare', 12, 2)->default('0.00');
            $t->timestamp('completed_at')->nullable(); $t->timestamp('end_trip')->nullable(); $t->timestamps();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        (require database_path('migrations/2026_08_16_000011_create_company_corporate_accounts_table.php'))->up();
        (require database_path('migrations/2026_08_16_000012_create_company_corporate_invoices_table.php'))->up();
        DB::table('companies')->insert([['id' => 1, 'name' => 'Platform'], ['id' => 2, 'name' => 'Operator']]);
        DB::table('currency')->insert(['code' => 'KES']);
        DB::table('users')->insert([['id' => 1, 'company_id' => 1], ['id' => 2, 'company_id' => 2]]);
        if ($migrate) {
            $this->corporateMigration()->up();
        }
    }

    protected function corporateMigration()
    {
        return require database_path('migrations/2026_10_07_000002_add_corporate_billing_domain.php');
    }

    protected function dropCorporateTestSchema(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        if (DB::connection()->getDatabaseName() !== 'lala_corporate_schema_test') {
            throw new \RuntimeException('Refusing cleanup outside isolated corporate schema database.');
        }
        Schema::disableForeignKeyConstraints();
        foreach (['company_corporate_invoice_allocations', 'company_corporate_invoice_items', 'company_corporate_audits', 'company_corporate_trip_records', 'company_corporate_invoices', 'company_corporate_accounts', 'company_saas_settings', 'saas_feature_modules', 'site_settings', 'car_type', 'trips', 'users', 'request', 'schedule_ride', 'currency', 'companies'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
}
