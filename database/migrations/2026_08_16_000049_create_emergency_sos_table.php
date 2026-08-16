<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_sos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 100);
            $table->string('country_code', 5)->nullable();
            $table->string('mobile_number', 20);
            $table->unsignedInteger('country_id')->nullable();
            $table->index('user_id', 'emergency_sos_user_id_foreign');
            $table->index('country_id', 'emergency_sos_country_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_sos');
    }
};
