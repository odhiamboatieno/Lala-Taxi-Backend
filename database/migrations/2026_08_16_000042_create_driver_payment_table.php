<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('last_trip_id');
            $table->string('currency_code', 10);
            $table->decimal('paid_amount', 7, 2)->default('0.00');
            $table->index('driver_id', 'driver_payment_driver_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_payment');
    }
};
