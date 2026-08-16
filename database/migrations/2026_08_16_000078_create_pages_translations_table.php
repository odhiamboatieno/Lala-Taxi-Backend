<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pages_id');
            $table->string('name', 191);
            $table->longText('description');
            $table->string('locale', 5);
            $table->unique(['pages_id', 'locale'], 'pages_translations_pages_id_locale_unique');
            $table->index('locale', 'pages_translations_locale_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages_translations');
    }
};
