<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('documents_id');
            $table->string('document_name', 191);
            $table->string('locale', 5);
            $table->unique(['documents_id', 'locale'], 'documents_langs_documents_id_locale_unique');
            $table->index('locale', 'documents_langs_locale_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_langs');
    }
};
