<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationDispatchLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notification_dispatch_logs')) {
            return;
        }

        Schema::create('notification_dispatch_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->default(0);
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('notification_template_id')->nullable();
            $table->string('event_key', 120)->nullable();
            $table->string('channel', 20);
            $table->string('recipient_type', 20);
            $table->unsignedInteger('recipient_id')->nullable();
            $table->string('recipient_name', 160)->nullable();
            $table->string('recipient_contact', 191)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('subject', 191)->nullable();
            $table->text('message')->nullable();
            $table->text('response')->nullable();
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['event_key', 'channel']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_dispatch_logs');
    }
}
