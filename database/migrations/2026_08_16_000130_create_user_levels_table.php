<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedBigInteger('inherits_from_id')->nullable();
            $table->string('level_type', 20);
            $table->unsignedInteger('sequence')->default('1');
            $table->string('name', 191);
            $table->string('slug', 191);
            $table->string('icon', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('Active');
            $table->tinyInteger('is_default')->default('0');
            $table->longText('target_config')->nullable();
            $table->longText('reward_config')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['company_id', 'level_type', 'sequence'], 'user_levels_company_type_sequence_uk');
            $table->index(['level_type', 'status'], 'user_levels_type_status_idx');
            $table->index(['company_id', 'level_type'], 'user_levels_company_type_idx');
            $table->index('inherits_from_id', 'user_levels_inherits_from_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
