<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_cancel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_ride_id');
            $table->string('cancel_reason', 191);
            $table->unsignedInteger('cancel_reason_id');
            $table->enum('cancel_by', ['Rider', 'Driver', 'Admin', 'Company']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('schedule_ride_id', 'schedule_cancel_schedule_ride_id_foreign');
            $table->index('cancel_reason_id', 'schedule_cancel_cancel_reason_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_cancel');
    }
};
