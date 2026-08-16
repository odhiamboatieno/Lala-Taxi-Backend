<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 10);
            $table->string('symbol', 10);
            $table->decimal('rate', 10, 2);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('default_currency', ['1', '0']);
            $table->enum('paypal_currency', ['Yes', 'No'])->default('No');
            $table->unique('code', 'currency_code_unique');
            $table->index(['status', 'default_currency', 'paypal_currency'], 'currency_status_default_currency_paypal_currency_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency');
    }
};
