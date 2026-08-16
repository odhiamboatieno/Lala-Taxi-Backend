<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->text('correlation_id')->nullable();
            $table->text('admin_transaction_id')->nullable();
            $table->text('driver_transaction_id')->nullable();
            $table->enum('admin_payout_status', ['Pending', 'Processing', 'Paid'])->default('Pending');
            $table->enum('driver_payout_status', ['Pending', 'Processing', 'Paid'])->default('Pending');
            $table->index('trip_id', 'payment_trip_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
