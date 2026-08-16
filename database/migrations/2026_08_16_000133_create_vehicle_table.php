<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id')->nullable();
            $table->integer('vehicle_make_id');
            $table->integer('vehicle_model_id');
            $table->string('vehicle_id', 255);
            $table->string('vehicle_type', 100);
            $table->string('vehicle_name', 100);
            $table->string('vehicle_number', 191);
            $table->tinyInteger('is_active')->default('0');
            $table->string('year', 191);
            $table->string('color', 191);
            $table->enum('default_type', ['0', '1'])->default('0');
            $table->enum('status', ['Active', 'Inactive']);
            $table->index('user_id', 'vehicle_user_id_foreign');
            $table->index('company_id', 'vehicle_company_id_foreign');
            $table->index('status', 'vehicle_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle');
    }
};
