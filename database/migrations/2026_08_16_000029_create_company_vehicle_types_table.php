<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_vehicle_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('global_vehicle_category_id')->nullable();
            $table->string('name', 191);
            $table->string('slug', 191);
            $table->text('description')->nullable();
            $table->string('is_pool', 10)->default('No');
            $table->string('icon', 191)->nullable();
            $table->string('vehicle_image', 191)->nullable();
            $table->string('active_image', 191)->nullable();
            $table->string('app_image', 191)->nullable();
            $table->unsignedTinyInteger('passenger_capacity')->default('4');
            $table->unsignedTinyInteger('luggage_capacity')->default('0');
            $table->tinyInteger('is_ev')->default('0');
            $table->tinyInteger('is_accessible')->default('0');
            $table->unsignedInteger('sort_order')->default('0');
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['company_id', 'slug'], 'company_vehicle_types_company_id_slug_unique');
            $table->index('global_vehicle_category_id', 'sd_cvt_gvc_fk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_vehicle_types');
    }
};
