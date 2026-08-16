<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pay_for', 255);
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id', 255);
            $table->string('currency_code', 3);
            $table->text('description');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable()->default('current_timestamp()');
            $table->timestamp('updated_at')->nullable()->default('\'0000-00-00 00:00:00\' ON UPDATE current_timestamp()');
            $table->unique('transaction_id', 'transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
