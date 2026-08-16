<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_category_lang', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name', 191);
            $table->longText('description');
            $table->string('locale', 5);
            $table->unique(['category_id', 'locale'], 'help_category_lang_category_id_locale_unique');
            $table->index('locale', 'help_category_lang_locale_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_category_lang');
    }
};
