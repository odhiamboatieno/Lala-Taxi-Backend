<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_dispatch_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('dispatch_strategy', 191)->nullable();
            $table->decimal('driver_search_radius', 8, 2)->default('5.00');
            $table->decimal('max_driver_search_radius', 8, 2)->default('12.00');
            $table->unsignedInteger('request_timeout_seconds')->default('30');
            $table->unsignedInteger('retry_rounds')->default('3');
            $table->tinyInteger('allow_auto_dispatch')->default('1');
            $table->tinyInteger('allow_manual_dispatch')->default('1');
            $table->tinyInteger('allow_scheduled_trips')->default('1');
            $table->tinyInteger('allow_airport_trips')->default('1');
            $table->tinyInteger('allow_out_of_area_trips')->default('0');
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('company_id', 'company_dispatch_settings_company_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_dispatch_settings');
    }
};
