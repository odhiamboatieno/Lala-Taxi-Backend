<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->default('0');
            $table->string('created_by_type', 20)->default('admin');
            $table->unsignedInteger('created_by_id')->nullable();
            $table->string('title', 191);
            $table->text('message');
            $table->string('channel', 20)->default('push');
            $table->string('target_mode', 20)->default('all');
            $table->string('audience_type', 20)->default('all');
            $table->longText('recipient_ids')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('recurrence', 20)->default('none');
            $table->dateTime('scheduled_for')->nullable();
            $table->dateTime('next_run_at')->nullable();
            $table->dateTime('last_sent_at')->nullable();
            $table->unsignedInteger('last_recipient_count')->default('0');
            $table->unsignedInteger('successful_count')->default('0');
            $table->unsignedInteger('failed_count')->default('0');
            $table->tinyInteger('is_active')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'status'], 'notification_campaigns_company_id_status_index');
            $table->index(['is_active', 'next_run_at'], 'notification_campaigns_is_active_next_run_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_campaigns');
    }
};
