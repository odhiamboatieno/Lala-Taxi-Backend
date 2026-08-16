<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_issues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->unsignedInteger('assigned_admin_id')->nullable();
            $table->enum('type', ['chat', 'issue'])->default('issue');
            $table->string('category', 80);
            $table->string('subject', 160);
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['open', 'in_progress', 'waiting_rider', 'resolved', 'closed'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index(['user_id', 'status'], 'idx_support_issues_user_status');
            $table->index('trip_id', 'idx_support_issues_trip_id');
            $table->index(['status', 'last_message_at'], 'idx_support_issues_status_last_message');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_issues');
    }
};
