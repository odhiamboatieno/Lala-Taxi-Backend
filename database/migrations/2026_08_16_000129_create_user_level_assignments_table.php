<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_level_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_level_id');
            $table->string('level_type', 20);
            $table->decimal('points', 12, 2)->default('0.00');
            $table->longText('metrics_snapshot')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['user_id', 'company_id', 'level_type'], 'user_level_assignments_unique_scope');
            $table->index(['user_id', 'level_type'], 'user_level_assignments_user_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_level_assignments');
    }
};
