<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->string('value', 255);
            $table->string('site', 25);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }
};
