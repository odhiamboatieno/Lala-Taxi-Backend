<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191);
            $table->string('display_name', 191)->nullable();
            $table->string('description', 191)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('name', 'permissions_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
