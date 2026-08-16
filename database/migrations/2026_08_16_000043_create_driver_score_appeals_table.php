<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_score_appeals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedBigInteger('driver_score_id')->nullable();
            $table->unsignedBigInteger('driver_score_event_id')->nullable();
            $table->text('reason');
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->longText('resolution_metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['driver_id', 'status'], 'driver_score_appeals_driver_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_score_appeals');
    }
};
