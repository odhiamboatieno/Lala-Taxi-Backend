<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->default('0');
            $table->string('event_key', 120);
            $table->string('category', 60);
            $table->string('section', 120)->nullable();
            $table->string('audience', 30)->nullable();
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->string('push_title', 191)->nullable();
            $table->text('push_message')->nullable();
            $table->text('sms_message')->nullable();
            $table->tinyInteger('push_enabled')->default('1');
            $table->tinyInteger('sms_enabled')->default('0');
            $table->tinyInteger('is_active')->default('1');
            $table->longText('placeholders')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['company_id', 'event_key'], 'notification_templates_scope_event_unique');
            $table->index(['company_id', 'category'], 'notification_templates_company_id_category_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
