<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->decimal('amount', 7, 2);
            $table->string('currency_code', 10);
            $table->text('paykey')->nullable();
            $table->unique('user_id', 'wallet_user_id_unique');
            $table->index('currency_code', 'wallet_currency_code_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet');
    }
};
