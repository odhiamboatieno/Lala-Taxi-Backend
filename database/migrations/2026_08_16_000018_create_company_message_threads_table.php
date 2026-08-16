<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_message_threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('subject', 191);
            $table->string('participant_type', 191)->default('staff');
            $table->unsignedInteger('participant_id')->nullable();
            $table->string('status', 191)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->tinyInteger('green_flash')->default('0');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'status'], 'company_message_threads_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_message_threads');
    }
};
