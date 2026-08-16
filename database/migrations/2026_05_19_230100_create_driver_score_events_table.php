<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_score_events')) {
            return;
        }

        Schema::create('driver_score_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->unsignedBigInteger('support_issue_id')->nullable();
            $table->unsignedBigInteger('driver_score_id')->nullable();
            $table->string('event_key', 190)->nullable()->unique();
            $table->string('source_type', 60)->default('system');
            $table->string('event_type', 80);
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->decimal('impact_score', 7, 2)->default(0);
            $table->string('impact_direction', 20)->default('neutral');
            $table->boolean('excluded_from_scoring')->default(false);
            $table->boolean('requires_admin_confirmation')->default(false);
            $table->timestamp('admin_confirmed_at')->nullable();
            $table->unsignedInteger('admin_confirmed_by')->nullable();
            $table->timestamp('event_date')->nullable();
            $table->json('weight_snapshot')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'event_date'], 'driver_score_events_driver_date_idx');
            $table->index(['driver_id', 'event_type'], 'driver_score_events_driver_type_idx');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_score_events');
    }
};
