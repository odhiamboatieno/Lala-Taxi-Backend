<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_sub_category_lang', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sub_category_id');
            $table->string('name', 191);
            $table->longText('description');
            $table->string('locale', 5);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['sub_category_id', 'locale'], 'help_sub_category_lang_sub_category_id_locale_unique');
            $table->index('locale', 'help_sub_category_lang_locale_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_sub_category_lang');
    }
};
