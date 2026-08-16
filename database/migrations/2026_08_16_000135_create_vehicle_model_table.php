<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_model', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('vehicle_make_id');
            $table->string('model_name', 255)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_model');
    }
};
