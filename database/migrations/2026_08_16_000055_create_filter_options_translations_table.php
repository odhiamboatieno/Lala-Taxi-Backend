<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_options_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('filter_option_id');
            $table->string('name', 255);
            $table->string('locale', 5);
            $table->timestamp('created_at')->default('current_timestamp()');
            $table->timestamp('updated_at')->default('current_timestamp()');
            $table->index('filter_option_id', 'filter_options_translations_filter_option_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_options_translations');
    }
};
