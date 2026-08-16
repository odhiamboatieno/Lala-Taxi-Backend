<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operation_team_roles')) {
            return;
        }

        Schema::create('operation_team_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 120);
            $table->json('permissions')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name'], 'ot_roles_company_name_unq');
            $table->index('company_id', 'ot_roles_company_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_team_roles');
    }
};
