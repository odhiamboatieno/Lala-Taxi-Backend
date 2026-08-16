<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_scores')) {
            return;
        }

        Schema::create('driver_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id')->unique();
            $table->decimal('score_percentage', 5, 2)->default(100);
            $table->string('status', 40)->default('Good');
            $table->string('marketplace_access_status', 40)->default('active');
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamp('next_calculation_at')->nullable();
            $table->unsignedInteger('total_completed_trips')->default(0);
            $table->unsignedInteger('total_cancelled_trips')->default(0);
            $table->decimal('acceptance_rate', 5, 2)->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('average_rating', 4, 2)->default(0);
            $table->decimal('complaint_rate', 5, 2)->default(0);
            $table->decimal('late_arrival_rate', 5, 2)->default(0);
            $table->unsignedInteger('safety_incident_count')->default(0);
            $table->unsignedInteger('fraud_flag_count')->default(0);
            $table->unsignedInteger('appeal_pending_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'marketplace_access_status'], 'driver_scores_status_market_idx');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_scores');
    }
};
