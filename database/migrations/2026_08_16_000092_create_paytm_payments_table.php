<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paytm_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('payment_id', 120);
            $table->decimal('amount', 11, 2);
            $table->string('pay_for', 120);
            $table->enum('status', ['Pending', 'Paid'])->default('Pending');
            $table->timestamp('created_at')->default('current_timestamp()');
            $table->timestamp('updated_at')->nullable()->default('NULL ON UPDATE current_timestamp()');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paytm_payments');
    }
};
