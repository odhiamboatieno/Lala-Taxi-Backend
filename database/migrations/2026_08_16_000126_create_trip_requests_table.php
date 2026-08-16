<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('rider_id');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('driver_id', 'trip_requests_driver_id_foreign');
            $table->index('rider_id', 'trip_requests_rider_id_foreign');
            $table->index(['trip_id', 'driver_id', 'status'], 'trip_requests_trip_driver_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_requests');
    }
};
