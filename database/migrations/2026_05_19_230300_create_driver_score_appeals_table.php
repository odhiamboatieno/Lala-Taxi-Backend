<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_score_appeals')) {
            return;
        }

        Schema::create('driver_score_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->unsignedBigInteger('driver_score_id')->nullable();
            $table->unsignedBigInteger('driver_score_event_id')->nullable();
            $table->text('reason');
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('resolution_metadata')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status'], 'driver_score_appeals_driver_status_idx');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_score_appeals');
    }
};
