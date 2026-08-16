<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_code', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 191);
            $table->integer('amount');
            $table->string('currency_code', 10);
            $table->string('expire_date', 191);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('currency_code', 'promo_code_currency_code_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code');
    }
};
