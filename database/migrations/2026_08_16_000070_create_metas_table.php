<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url', 191);
            $table->string('title', 191);
            $table->text('description');
            $table->text('keywords');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
