<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_levels')) {
            Schema::create('user_levels', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->string('level_type', 20);
                $table->unsignedInteger('sequence')->default(1);
                $table->string('name');
                $table->string('slug');
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('Active');
                $table->boolean('is_default')->default(false);
                $table->json('target_config')->nullable();
                $table->json('reward_config')->nullable();
                $table->timestamps();

                $table->index(['level_type', 'status'], 'user_levels_type_status_idx');
                $table->index(['company_id', 'level_type'], 'user_levels_company_type_idx');
                $table->unique(['company_id', 'level_type', 'sequence'], 'user_levels_company_type_sequence_uk');
            });
        }

        if (! Schema::hasTable('user_level_assignments')) {
            Schema::create('user_level_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('user_level_id');
                $table->string('level_type', 20);
                $table->decimal('points', 12, 2)->default(0);
                $table->json('metrics_snapshot')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'level_type'], 'user_level_assignments_user_type_idx');
                $table->unique(['user_id', 'company_id', 'level_type'], 'user_level_assignments_unique_scope');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_level_assignments');
        Schema::dropIfExists('user_levels');
    }
};
