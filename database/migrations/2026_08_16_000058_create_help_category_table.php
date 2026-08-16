<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->text('description');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_category');
    }
};
