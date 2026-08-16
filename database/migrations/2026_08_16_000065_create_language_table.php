<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('value', 5);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('default_language', ['1', '0']);
            $table->index('value', 'language_value_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language');
    }
};
