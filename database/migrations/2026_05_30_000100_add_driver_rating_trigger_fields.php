<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDriverRatingTriggerFields extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (! Schema::hasColumn('trips', 'cash_collected')) {
                $table->boolean('cash_collected')->default(false)->after('payment_status');
            }
            if (! Schema::hasColumn('trips', 'driver_confirmed_cash')) {
                $table->boolean('driver_confirmed_cash')->default(false)->after('cash_collected');
            }
            if (! Schema::hasColumn('trips', 'driver_rating_triggered_at')) {
                $table->timestamp('driver_rating_triggered_at')->nullable()->after('driver_confirmed_cash');
            }
        });

        Schema::table('rating', function (Blueprint $table) {
            if (! Schema::hasColumn('rating', 'rater_id')) {
                $table->unsignedInteger('rater_id')->nullable()->after('driver_id');
            }
            if (! Schema::hasColumn('rating', 'rater_type')) {
                $table->string('rater_type', 20)->nullable()->after('rater_id');
            }
        });

        Schema::table('rating', function (Blueprint $table) {
            $table->unique(['trip_id', 'rater_id', 'rater_type'], 'rating_trip_rater_unique');
        });
    }

    public function down()
    {
        Schema::table('rating', function (Blueprint $table) {
            $table->dropUnique('rating_trip_rater_unique');
            if (Schema::hasColumn('rating', 'rater_type')) {
                $table->dropColumn('rater_type');
            }
            if (Schema::hasColumn('rating', 'rater_id')) {
                $table->dropColumn('rater_id');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'driver_rating_triggered_at')) {
                $table->dropColumn('driver_rating_triggered_at');
            }
            if (Schema::hasColumn('trips', 'driver_confirmed_cash')) {
                $table->dropColumn('driver_confirmed_cash');
            }
            if (Schema::hasColumn('trips', 'cash_collected')) {
                $table->dropColumn('cash_collected');
            }
        });
    }
}
