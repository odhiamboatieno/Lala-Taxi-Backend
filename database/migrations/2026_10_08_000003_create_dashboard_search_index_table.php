<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dashboard_search_index')) {
            return;
        }

        Schema::create('dashboard_search_index', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 64);
            $table->string('entity_id', 64);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('title', 255)->default('');
            $table->text('description')->nullable();
            $table->string('group', 100)->default('');
            $table->string('icon', 64)->nullable();
            $table->string('scope', 16);
            $table->string('permission', 128)->nullable();
            $table->string('feature', 64)->nullable();
            $table->string('detail_permission', 128)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('path_admin', 255)->nullable();
            $table->string('list_path', 255)->nullable();
            $table->string('fallback_search', 255)->nullable();
            $table->text('search_blob')->nullable();
            $table->string('status', 32)->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id'], 'dashboard_search_entity_unique');
            $table->index(['scope', 'company_id'], 'dashboard_search_scope_company');
            $table->index('permission', 'dashboard_search_permission');
            $table->index(['entity_type', 'company_id'], 'dashboard_search_type_company');
            $table->index('source_updated_at', 'dashboard_search_source_updated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_search_index');
    }
};
