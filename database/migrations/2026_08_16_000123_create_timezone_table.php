<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timezone', function (Blueprint $table) {
            $table->increments('id');
            $table->string('timezone', 100);
            $table->unique('timezone', 'timezone_timezone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timezone');
    }
};
