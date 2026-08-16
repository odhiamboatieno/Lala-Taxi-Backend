<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_ride')) {
            return;
        }

        Schema::table('schedule_ride', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_ride', 'booking_capabilities')) {
                $table->longText('booking_capabilities')->nullable()->after('booking_meta');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedule_ride')) {
            return;
        }

        Schema::table('schedule_ride', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_ride', 'booking_capabilities')) {
                $table->dropColumn('booking_capabilities');
            }
        });
    }
};
