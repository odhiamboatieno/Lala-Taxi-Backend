<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_owe_amount_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('transaction_id', 70)->nullable();
            $table->decimal('amount', 11, 2)->nullable();
            $table->string('currency_code', 50)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'driver_owe_amount_payments_user_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_owe_amount_payments');
    }
};
