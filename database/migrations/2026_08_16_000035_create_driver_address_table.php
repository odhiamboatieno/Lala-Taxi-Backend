<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_address', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('address_line1', 191);
            $table->string('address_line2', 191);
            $table->string('city', 191);
            $table->string('state', 191);
            $table->string('postal_code', 191);
            $table->unique('user_id', 'driver_address_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_address');
    }
};
