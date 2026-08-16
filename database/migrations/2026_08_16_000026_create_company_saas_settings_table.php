<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_saas_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->tinyInteger('is_saas_enabled')->default('0');
            $table->enum('dispatch_mode', ['global', 'saas', 'hybrid'])->default('global');
            $table->tinyInteger('use_own_locations')->default('0');
            $table->tinyInteger('use_own_fares')->default('0');
            $table->tinyInteger('use_own_fees')->default('0');
            $table->tinyInteger('use_own_vehicle_types')->default('0');
            $table->tinyInteger('use_own_dispatch_settings')->default('0');
            $table->tinyInteger('use_own_branding')->default('0');
            $table->string('domain', 191)->nullable();
            $table->string('subdomain', 191)->nullable();
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('company_id', 'company_saas_settings_company_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_saas_settings');
    }
};
