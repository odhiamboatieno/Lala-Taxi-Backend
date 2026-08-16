<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('help_id');
            $table->string('name', 191);
            $table->longText('description');
            $table->string('locale', 5);
            $table->unique(['help_id', 'locale'], 'help_translations_help_id_locale_unique');
            $table->index('locale', 'help_translations_locale_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_translations');
    }
};
