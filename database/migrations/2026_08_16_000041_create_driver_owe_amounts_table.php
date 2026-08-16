<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_owe_amounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->decimal('amount', 11, 2)->nullable();
            $table->char('currency_code', 3)->nullable();
            $table->index('user_id', 'driver_owe_amounts_user_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_owe_amounts');
    }
};
