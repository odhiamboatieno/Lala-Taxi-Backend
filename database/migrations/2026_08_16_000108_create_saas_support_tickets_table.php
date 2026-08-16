<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('created_by_type', 191)->default('company');
            $table->unsignedInteger('created_by_id')->nullable();
            $table->string('subject', 191);
            $table->string('category', 191)->default('general');
            $table->string('priority', 191)->default('normal');
            $table->string('status', 191)->default('open');
            $table->text('description')->nullable();
            $table->unsignedInteger('assigned_admin_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'status'], 'saas_support_tickets_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_support_tickets');
    }
};
