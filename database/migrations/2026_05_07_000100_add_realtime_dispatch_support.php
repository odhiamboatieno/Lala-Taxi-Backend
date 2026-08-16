<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('device_id');
            }

            if (! Schema::hasColumn('users', 'is_available')) {
                $table->boolean('is_available')->default(false)->after('is_online');
            }

            if (! Schema::hasColumn('users', 'current_trip_id')) {
                $table->unsignedInteger('current_trip_id')->nullable()->after('is_available');
            }

            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('current_trip_id');
            }

            if (! Schema::hasColumn('users', 'current_lat')) {
                $table->decimal('current_lat', 10, 7)->nullable()->after('last_seen_at');
            }

            if (! Schema::hasColumn('users', 'current_lng')) {
                $table->decimal('current_lng', 10, 7)->nullable()->after('current_lat');
            }

            if (! Schema::hasColumn('users', 'geohash')) {
                $table->string('geohash')->nullable()->after('current_lng');
            }

            if (! Schema::hasColumn('users', 'heading')) {
                $table->decimal('heading', 8, 2)->nullable()->after('geohash');
            }

            if (! Schema::hasColumn('users', 'speed')) {
                $table->decimal('speed', 8, 2)->nullable()->after('heading');
            }

            if (! Schema::hasColumn('users', 'socket_id')) {
                $table->string('socket_id')->nullable()->after('speed');
            }

            if (! Schema::hasColumn('users', 'service_type_id')) {
                $table->unsignedInteger('service_type_id')->nullable()->after('socket_id');
            }

            if (! Schema::hasColumn('users', 'vehicle_type_id')) {
                $table->unsignedInteger('vehicle_type_id')->nullable()->after('service_type_id');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (! Schema::hasColumn('trips', 'company_id')) {
                $table->unsignedInteger('company_id')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('trips', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('updated_at');
            }

            if (! Schema::hasColumn('trips', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('accepted_at');
            }

            if (! Schema::hasColumn('trips', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('trips', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
        });

        Schema::create('trip_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('rider_id');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rider_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['trip_id', 'driver_id', 'status'], 'trip_requests_trip_driver_status_idx');
        });

        Schema::create('driver_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('heading', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->index(['driver_id', 'recorded_at'], 'driver_locations_driver_recorded_idx');
        });

        DB::statement('ALTER TABLE trips DROP FOREIGN KEY trips_driver_id_foreign');
        DB::statement('ALTER TABLE trips MODIFY driver_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE trips ADD CONSTRAINT trips_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE trips DROP FOREIGN KEY trips_request_id_foreign');
        DB::statement('ALTER TABLE trips MODIFY request_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE trips ADD CONSTRAINT trips_request_id_foreign FOREIGN KEY (request_id) REFERENCES request(id) ON DELETE CASCADE');

        DB::statement("ALTER TABLE trips MODIFY status ENUM('requested','searching_driver','driver_assigned','driver_arriving','arrived','started','completed','cancelled','Scheduled','Cancelled','Begin trip','End trip','Payment','Rating','Completed','Null') DEFAULT 'Null'");

        try {
            DB::statement('CREATE INDEX users_dispatch_presence_idx ON users (user_type, company_id, is_online, is_available)');
        } catch (\Throwable $e) {
        }

        try {
            DB::statement('CREATE INDEX users_dispatch_location_idx ON users (current_lat, current_lng)');
        } catch (\Throwable $e) {
        }

        try {
            DB::statement('CREATE INDEX trips_dispatch_lookup_idx ON trips (status, company_id, driver_id, user_id)');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
        Schema::dropIfExists('trip_requests');
    }
};
