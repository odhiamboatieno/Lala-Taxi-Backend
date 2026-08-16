<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('alias')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('description')->nullable();
            $table->string('author')->nullable();
            $table->string('path');                       // absolute path on disk
            $table->json('providers')->nullable();        // service provider FQCNs
            $table->json('requires')->nullable();         // declared module dependencies
            $table->boolean('enabled')->default(true);
            $table->boolean('autoload')->default(true);   // auto-register providers
            $table->boolean('has_migrations')->default(false);
            $table->boolean('migrated')->default(false);
            $table->string('checksum')->nullable();       // sha1 of module.json
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
