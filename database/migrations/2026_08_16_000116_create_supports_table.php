<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191)->nullable();
            $table->string('link', 191)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->enum('Owner', ['Rider', 'Driver', 'All']);
            $table->string('image', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('status', 'supports_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};
