<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_feature_modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug', 191);
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->tinyInteger('is_installed')->default('0');
            $table->tinyInteger('is_enabled')->default('0');
            $table->unsignedInteger('sort_order')->default('0');
            $table->string('status', 191)->default('Inactive');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('slug', 'saas_feature_modules_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_feature_modules');
    }
};
