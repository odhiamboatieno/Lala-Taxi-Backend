<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('global_vehicle_category_id');
            $table->unsignedBigInteger('company_vehicle_type_id');
            $table->unsignedInteger('priority')->default('0');
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['company_id', 'global_vehicle_category_id', 'company_vehicle_type_id'], 'company_vehicle_category_mapping_unique');
            $table->index('global_vehicle_category_id', 'sd_cvcm_gvc_fk');
            $table->index('company_vehicle_type_id', 'sd_cvcm_cvt_fk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_vehicle_category_mappings');
    }
};
