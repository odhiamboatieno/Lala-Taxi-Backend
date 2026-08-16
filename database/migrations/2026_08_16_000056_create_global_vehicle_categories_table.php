<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_vehicle_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('slug', 191);
            $table->text('description')->nullable();
            $table->string('icon', 191)->nullable();
            $table->tinyInteger('is_visible_to_rider')->default('1');
            $table->unsignedInteger('sort_order')->default('0');
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('slug', 'global_vehicle_categories_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_vehicle_categories');
    }
};
