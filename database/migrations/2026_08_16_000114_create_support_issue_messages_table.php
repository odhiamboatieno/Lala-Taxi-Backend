<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_issue_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('support_issue_id');
            $table->unsignedInteger('sender_id')->nullable();
            $table->enum('sender_type', ['rider', 'admin', 'system'])->default('rider');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable()->default('current_timestamp()');
            $table->timestamp('updated_at')->nullable()->default('current_timestamp() ON UPDATE current_timestamp()');
            $table->index(['support_issue_id', 'created_at'], 'idx_support_issue_messages_issue_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_issue_messages');
    }
};
