<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_score_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->unsignedBigInteger('support_issue_id')->nullable();
            $table->unsignedBigInteger('driver_score_id')->nullable();
            $table->string('event_key', 190)->nullable();
            $table->string('source_type', 60)->default('system');
            $table->string('event_type', 80);
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->decimal('impact_score', 7, 2)->default('0.00');
            $table->string('impact_direction', 20)->default('neutral');
            $table->tinyInteger('excluded_from_scoring')->default('0');
            $table->tinyInteger('requires_admin_confirmation')->default('0');
            $table->timestamp('admin_confirmed_at')->nullable();
            $table->unsignedInteger('admin_confirmed_by')->nullable();
            $table->timestamp('event_date')->nullable();
            $table->longText('weight_snapshot')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('event_key', 'driver_score_events_event_key_unique');
            $table->index(['driver_id', 'event_date'], 'driver_score_events_driver_date_idx');
            $table->index(['driver_id', 'event_type'], 'driver_score_events_driver_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_score_events');
    }
};
