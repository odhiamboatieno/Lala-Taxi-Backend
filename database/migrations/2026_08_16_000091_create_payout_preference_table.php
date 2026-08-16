<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_preference', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('address1', 191);
            $table->string('address2', 191)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 25)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('payout_method', 20);
            $table->string('paypal_email', 191);
            $table->string('currency_code', 10);
            $table->string('routing_number', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('holder_name', 100);
            $table->enum('holder_type', ['Individual', 'Company']);
            $table->string('document_id', 100)->nullable();
            $table->string('document_image', 100)->nullable();
            $table->string('additional_document_id', 100)->nullable();
            $table->string('additional_document_image', 100)->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->string('address_kanji', 255)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_location', 100)->nullable();
            $table->string('branch_name', 100)->nullable();
            $table->string('branch_code', 100)->nullable();
            $table->string('ssn_last_4', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_preference');
    }
};
