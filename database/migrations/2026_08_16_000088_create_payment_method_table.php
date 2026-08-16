<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('customer_id', 100)->nullable();
            $table->string('intent_id', 100)->nullable();
            $table->string('payment_method_id', 100)->nullable();
            $table->string('brand', 20)->nullable();
            $table->string('last4', 5)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'payment_method_user_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method');
    }
};
