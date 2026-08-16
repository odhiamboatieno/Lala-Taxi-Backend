<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_promo_code', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('promo_code_id');
            $table->integer('trip_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'users_promo_code_user_id_foreign');
            $table->index('promo_code_id', 'users_promo_code_promo_code_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_promo_code');
    }
};
