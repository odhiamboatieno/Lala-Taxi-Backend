<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_payment_gateways')) {
            return;
        }

        Schema::create('company_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 50);
            $table->text('value')->nullable();
            $table->string('site', 50);
            $table->timestamps();

            $table->unique(['company_id', 'name', 'site'], 'company_payment_gateways_unique');
            $table->index(['company_id', 'site'], 'company_payment_gateways_company_site_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payment_gateways');
    }
};
