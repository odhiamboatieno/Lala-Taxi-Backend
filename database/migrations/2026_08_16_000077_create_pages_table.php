<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191);
            $table->string('url', 191);
            $table->enum('footer', ['yes', 'no']);
            $table->enum('under', ['company', 'discover', 'hosting']);
            $table->longText('content');
            $table->enum('status', ['Active', 'Inactive']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
