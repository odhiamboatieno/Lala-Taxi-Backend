<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportIssuesTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('support_issues')) {
            Schema::create('support_issues', function (Blueprint $table) {
                $table->id();
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
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['trip_id']);
                $table->index(['status', 'last_message_at']);
            });
        }

        if (!Schema::hasTable('support_issue_messages')) {
            Schema::create('support_issue_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('support_issue_id');
                $table->unsignedInteger('sender_id')->nullable();
                $table->enum('sender_type', ['rider', 'admin', 'system'])->default('rider');
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->foreign('support_issue_id')
                    ->references('id')
                    ->on('support_issues')
                    ->onDelete('cascade');
                $table->index(['support_issue_id', 'created_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('support_issue_messages');
        Schema::dropIfExists('support_issues');
    }
}
