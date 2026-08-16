<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_corporate_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('corporate_account_id');
            $table->string('invoice_number', 191);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default('0.00');
            $table->decimal('tax_amount', 12, 2)->default('0.00');
            $table->decimal('total_amount', 12, 2)->default('0.00');
            $table->string('status', 191)->default('Draft');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('invoice_number', 'company_corporate_invoices_invoice_number_unique');
            $table->index(['company_id', 'status'], 'company_corporate_invoices_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_corporate_invoices');
    }
};
