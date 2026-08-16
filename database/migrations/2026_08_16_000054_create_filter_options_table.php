<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->timestamp('created_at')->default('current_timestamp()');
            $table->timestamp('updated_at')->default('current_timestamp()');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_options');
    }
};
