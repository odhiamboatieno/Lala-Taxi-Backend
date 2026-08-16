<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_version', function (Blueprint $table) {
            $table->increments('id');
            $table->string('version', 10);
            $table->tinyInteger('device_type');
            $table->tinyInteger('user_type');
            $table->tinyInteger('force_update');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_version');
    }
};
