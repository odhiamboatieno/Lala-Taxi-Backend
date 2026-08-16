<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('type')->default('Ride')->after('name');
            $table->string('title')->nullable()->after('type');
            $table->string('subtitle')->nullable()->after('title');
        });

        Schema::table('request', function (Blueprint $table) {
            $table->string('booking_for_type', 30)->nullable()->after('drop_location');
            $table->string('booking_contact_name', 120)->nullable()->after('booking_for_type');
            $table->string('booking_contact_phone', 40)->nullable()->after('booking_contact_name');
            $table->text('stop_points')->nullable()->after('booking_contact_phone');
        });

        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->string('booking_for_type', 30)->nullable()->after('drop_location');
            $table->string('booking_contact_name', 120)->nullable()->after('booking_for_type');
            $table->string('booking_contact_phone', 40)->nullable()->after('booking_contact_name');
            $table->text('stop_points')->nullable()->after('booking_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->dropColumn(['booking_for_type', 'booking_contact_name', 'booking_contact_phone', 'stop_points']);
        });

        Schema::table('request', function (Blueprint $table) {
            $table->dropColumn(['booking_for_type', 'booking_contact_name', 'booking_contact_phone', 'stop_points']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['type', 'title', 'subtitle']);
        });
    }
};
