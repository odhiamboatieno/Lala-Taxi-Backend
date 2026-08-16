<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancel_reason_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cancel_reason_id');
            $table->string('locale', 5);
            $table->string('reason', 191);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['cancel_reason_id', 'locale'], 'cancel_reason_translations_cancel_reason_id_locale_unique');
            $table->index('locale', 'cancel_reason_translations_locale_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancel_reason_translations');
    }
};
