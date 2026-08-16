<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_subcategory', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name', 25);
            $table->text('description');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->index('category_id', 'help_subcategory_category_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_subcategory');
    }
};
