<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_driver_fee_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->enum('fee_type', ['percentage', 'weekly_subscription', 'monthly_subscription'])->default('percentage');
            $table->decimal('amount', 11, 2)->default('0.00');
            $table->enum('status', ['Active', 'Inactive'])->default('Inactive');
            $table->string('title', 191)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('company_id', 'company_driver_fee_settings_company_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_driver_fee_settings');
    }
};
