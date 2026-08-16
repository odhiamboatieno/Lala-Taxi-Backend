<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_settings');
    }
};
