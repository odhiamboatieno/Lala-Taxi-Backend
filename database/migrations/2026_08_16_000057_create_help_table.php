<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->integer('subcategory_id')->nullable();
            $table->string('question', 191);
            $table->binary('answer')->nullable();
            $table->enum('suggested', ['yes', 'no'])->default('no');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('category_id', 'help_category_id_foreign');
        });

        // The source schema stores the answer as MEDIUMBLOB (up to 16MB of HTML).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `help` MODIFY `answer` MEDIUMBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('help');
    }
};
