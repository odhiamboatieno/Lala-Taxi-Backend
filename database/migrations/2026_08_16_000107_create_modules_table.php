<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('modules')) {
            return;
        }

        Schema::create('modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191)->unique();
            $table->string('alias', 191)->nullable();
            $table->string('version', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('author', 191)->nullable();
            $table->string('path', 191)->nullable();
            $table->json('providers')->nullable();
            $table->json('requires')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('autoload')->default(true);
            $table->boolean('has_migrations')->default(false);
            $table->boolean('migrated')->default(false);
            $table->string('checksum', 191)->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
