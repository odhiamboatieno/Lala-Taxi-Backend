<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applied_referrals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->decimal('amount', 11, 2);
            $table->string('currency_code', 10)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'applied_referrals_user_id_foreign');
            $table->index('currency_code', 'applied_referrals_currency_code_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applied_referrals');
    }
};
