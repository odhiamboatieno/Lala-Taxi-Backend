<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->string('value', 5);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
