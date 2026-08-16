<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_score_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 120);
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string');
            $table->string('group', 60)->default('general');
            $table->string('label', 160)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('is_public')->default('0');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('key', 'driver_score_settings_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_score_settings');
    }
};
