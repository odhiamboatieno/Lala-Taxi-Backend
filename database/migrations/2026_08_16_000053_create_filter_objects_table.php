<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_objects', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['vehicle', 'rider']);
            $table->integer('object_id');
            $table->unsignedInteger('filter_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index('filter_id', 'filter_objects_filter_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_objects');
    }
};
