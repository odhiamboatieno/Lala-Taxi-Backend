<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->string('value', 25);
            $table->enum('user_type', ['Driver', 'Rider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_settings');
    }
};
