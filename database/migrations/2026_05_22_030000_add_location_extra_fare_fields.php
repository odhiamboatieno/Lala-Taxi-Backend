<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (! Schema::hasColumn('locations', 'extra_fare_enabled')) {
                $table->boolean('extra_fare_enabled')->default(false)->after('status');
            }

            if (! Schema::hasColumn('locations', 'extra_fare_percentage')) {
                $table->decimal('extra_fare_percentage', 5, 2)->default(0)->after('extra_fare_enabled');
            }

            if (! Schema::hasColumn('locations', 'extra_fare_reason')) {
                $table->string('extra_fare_reason', 191)->nullable()->after('extra_fare_percentage');
            }
        });

        Schema::table('request', function (Blueprint $table) {
            if (! Schema::hasColumn('request', 'extra_fare_percentage')) {
                $table->decimal('extra_fare_percentage', 5, 2)->default(0)->after('peak_fare');
            }

            if (! Schema::hasColumn('request', 'extra_fare_reason')) {
                $table->string('extra_fare_reason', 191)->nullable()->after('extra_fare_percentage');
            }
        });

        if (Schema::hasTable('schedule_ride')) {
            Schema::table('schedule_ride', function (Blueprint $table) {
                if (! Schema::hasColumn('schedule_ride', 'extra_fare_percentage')) {
                    $table->decimal('extra_fare_percentage', 5, 2)->default(0)->after('fare_estimation');
                }

                if (! Schema::hasColumn('schedule_ride', 'extra_fare_reason')) {
                    $table->string('extra_fare_reason', 191)->nullable()->after('extra_fare_percentage');
                }
            });
        }

        Schema::table('trips', function (Blueprint $table) {
            if (! Schema::hasColumn('trips', 'extra_fare_percentage')) {
                $table->decimal('extra_fare_percentage', 5, 2)->default(0)->after('peak_amount');
            }

            if (! Schema::hasColumn('trips', 'extra_fare_amount')) {
                $table->decimal('extra_fare_amount', 11, 2)->default(0)->after('extra_fare_percentage');
            }

            if (! Schema::hasColumn('trips', 'extra_fare_reason')) {
                $table->string('extra_fare_reason', 191)->nullable()->after('extra_fare_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            foreach (['extra_fare_reason', 'extra_fare_amount', 'extra_fare_percentage'] as $column) {
                if (Schema::hasColumn('trips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('request', function (Blueprint $table) {
            foreach (['extra_fare_reason', 'extra_fare_percentage'] as $column) {
                if (Schema::hasColumn('request', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('schedule_ride')) {
            Schema::table('schedule_ride', function (Blueprint $table) {
                foreach (['extra_fare_reason', 'extra_fare_percentage'] as $column) {
                    if (Schema::hasColumn('schedule_ride', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::table('locations', function (Blueprint $table) {
            foreach (['extra_fare_reason', 'extra_fare_percentage', 'extra_fare_enabled'] as $column) {
                if (Schema::hasColumn('locations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
