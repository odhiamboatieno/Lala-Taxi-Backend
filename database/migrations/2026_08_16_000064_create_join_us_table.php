<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('join_us', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->string('value', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('join_us');
    }
};
