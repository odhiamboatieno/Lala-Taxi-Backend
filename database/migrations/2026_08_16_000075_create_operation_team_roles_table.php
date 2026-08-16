<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_team_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->string('name', 120);
            $table->longText('permissions')->nullable();
            $table->tinyInteger('status')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['company_id', 'name'], 'ot_roles_company_name_unq');
            $table->index('company_id', 'ot_roles_company_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_team_roles');
    }
};
