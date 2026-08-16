<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 30);
            $table->string('title', 120);
            $table->string('icon_path', 255)->nullable();
            $table->integer('sort_order')->default('0');
            $table->tinyInteger('is_active')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_tasks');
    }
};
