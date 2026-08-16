<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_service_areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('name', 191);
            $table->string('country', 191)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('city', 191)->nullable();
            $table->string('zone_name', 191)->nullable();
            $table->longText('polygon')->nullable();
            $table->tinyInteger('pickup_enabled')->default('1');
            $table->tinyInteger('dropoff_enabled')->default('1');
            $table->tinyInteger('extra_fare_enabled')->default('0');
            $table->decimal('extra_fare_percentage', 8, 2)->default('0.00');
            $table->string('extra_fare_reason', 191)->nullable();
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'status'], 'company_service_areas_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_service_areas');
    }
};
