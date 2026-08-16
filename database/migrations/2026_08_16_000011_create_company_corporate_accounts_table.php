<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_corporate_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('account_name', 191);
            $table->string('contact_name', 191)->nullable();
            $table->string('contact_email', 191)->nullable();
            $table->string('contact_phone', 191)->nullable();
            $table->string('billing_cycle', 191)->default('monthly');
            $table->decimal('credit_limit', 12, 2)->default('0.00');
            $table->string('status', 191)->default('Active');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'status'], 'company_corporate_accounts_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_corporate_accounts');
    }
};
