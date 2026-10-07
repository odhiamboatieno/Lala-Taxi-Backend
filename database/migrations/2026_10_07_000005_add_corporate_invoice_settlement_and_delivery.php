<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('company_corporate_settlements')) {
            Schema::create('company_corporate_settlements', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('invoice_id')->unique();
                $t->decimal('amount', 12, 2);
                $t->string('currency_code', 10);
                $t->string('reference', 191);
                $t->date('settlement_date');
                $t->string('actor', 100);
                $t->timestamp('created_at');
                $t->foreign('invoice_id', 'ccs_invoice_fk')->references('id')->on('company_corporate_invoices')->restrictOnDelete();
            });
        }
        if (!Schema::hasTable('company_corporate_invoice_deliveries')) {
            Schema::create('company_corporate_invoice_deliveries', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('invoice_id')->unique();
                $t->string('recipient', 191);
                $t->string('locale', 10);
                $t->string('channel', 20)->default('email');
                $t->string('status', 20)->default('pending');
                $t->unsignedInteger('attempts')->default(0);
                $t->timestamp('locked_at')->nullable();
                $t->timestamp('available_at');
                $t->timestamp('delivered_at')->nullable();
                $t->text('last_error')->nullable();
                $t->timestamps();
                $t->index(['status', 'available_at'], 'ccid_due_idx');
                $t->foreign('invoice_id', 'ccid_invoice_fk')->references('id')->on('company_corporate_invoices')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Retain settlement evidence and delivery history on rollback.
    }
};
