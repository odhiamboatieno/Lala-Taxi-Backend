<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_scores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('driver_id');
            $table->decimal('score_percentage', 5, 2)->default('100.00');
            $table->string('status', 40)->default('Good');
            $table->string('marketplace_access_status', 40)->default('active');
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamp('next_calculation_at')->nullable();
            $table->unsignedInteger('total_completed_trips')->default('0');
            $table->unsignedInteger('total_cancelled_trips')->default('0');
            $table->decimal('acceptance_rate', 5, 2)->default('0.00');
            $table->decimal('completion_rate', 5, 2)->default('0.00');
            $table->decimal('average_rating', 4, 2)->default('0.00');
            $table->decimal('complaint_rate', 5, 2)->default('0.00');
            $table->decimal('late_arrival_rate', 5, 2)->default('0.00');
            $table->unsignedInteger('safety_incident_count')->default('0');
            $table->unsignedInteger('fraud_flag_count')->default('0');
            $table->unsignedInteger('appeal_pending_count')->default('0');
            $table->longText('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('driver_id', 'driver_scores_driver_id_unique');
            $table->index(['status', 'marketplace_access_status'], 'driver_scores_status_market_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_scores');
    }
};
