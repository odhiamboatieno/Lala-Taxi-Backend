<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('thread_id');
            $table->string('sender_type', 191)->default('company');
            $table->unsignedInteger('sender_id')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['thread_id', 'created_at'], 'company_messages_thread_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_messages');
    }
};
