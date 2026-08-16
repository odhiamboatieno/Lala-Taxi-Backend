<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notification_templates')) {
            return;
        }

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->default(0);
            $table->string('event_key', 120);
            $table->string('category', 60);
            $table->string('section', 120)->nullable();
            $table->string('audience', 30)->nullable();
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->string('push_title', 191)->nullable();
            $table->text('push_message')->nullable();
            $table->text('sms_message')->nullable();
            $table->boolean('push_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('placeholders')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'event_key'], 'notification_templates_scope_event_unique');
            $table->index(['company_id', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_templates');
    }
}
