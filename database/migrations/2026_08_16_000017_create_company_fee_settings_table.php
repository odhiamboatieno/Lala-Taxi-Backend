<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_fee_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('fee_name', 191);
            $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('fee_value', 11, 2)->default('0.00');
            $table->enum('applies_to', ['rider', 'driver', 'company', 'trip'])->default('trip');
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'status'], 'company_fee_settings_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_fee_settings');
    }
};
