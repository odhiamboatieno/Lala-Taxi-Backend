<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->text('coordinates');
            $table->enum('status', ['Active', 'Inactive']);
            $table->tinyInteger('extra_fare_enabled')->default('0');
            $table->decimal('extra_fare_percentage', 5, 2)->default('0.00');
            $table->string('extra_fare_reason', 191)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
