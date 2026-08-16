<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('referral_id');
            $table->enum('user_type', ['Rider', 'Driver']);
            $table->unsignedInteger('days');
            $table->unsignedInteger('trips');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('currency_code', 10)->nullable();
            $table->decimal('amount', 11, 2);
            $table->decimal('pending_amount', 11, 2);
            $table->enum('payment_status', ['Pending', 'Expired', 'Completed'])->default('Pending');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'referral_users_user_id_foreign');
            $table->index('referral_id', 'referral_users_referral_id_foreign');
            $table->index('currency_code', 'referral_users_currency_code_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_users');
    }
};
