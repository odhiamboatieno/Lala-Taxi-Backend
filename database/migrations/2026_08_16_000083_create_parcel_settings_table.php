<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 50);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_settings');
    }
};
