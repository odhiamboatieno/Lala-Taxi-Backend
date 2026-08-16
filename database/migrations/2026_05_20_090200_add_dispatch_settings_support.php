<?php

use App\Models\SiteSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchSettingsSupport extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('companies', 'dispatch_settings')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->json('dispatch_settings')->nullable()->after('status');
            });
        }

        $defaults = [
            'minimum_scheduled_lead_minutes' => '15',
            'allow_asap_manual_booking' => '1',
            'allow_dispatch_time_override' => '1',
            'scheduled_dispatch_lead_minutes' => '5',
            'default_assignment_timeout_seconds' => '30',
            'asap_assignment_timeout_seconds' => '20',
            'scheduled_assignment_timeout_seconds' => '45',
            'max_assignment_attempts' => '10',
            'enable_broadcast_dispatch' => '1',
            'enable_force_dispatch' => '1',
            'enable_sequential_dispatch' => '1',
            'auto_expand_radius' => '1',
            'default_radius_miles' => '10',
        ];

        foreach ($defaults as $name => $value) {
            SiteSettings::updateOrCreate(['name' => $name], ['value' => $value]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('companies', 'dispatch_settings')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('dispatch_settings');
            });
        }

        SiteSettings::whereIn('name', [
            'minimum_scheduled_lead_minutes',
            'allow_asap_manual_booking',
            'allow_dispatch_time_override',
            'scheduled_dispatch_lead_minutes',
            'default_assignment_timeout_seconds',
            'asap_assignment_timeout_seconds',
            'scheduled_assignment_timeout_seconds',
            'max_assignment_attempts',
            'enable_broadcast_dispatch',
            'enable_force_dispatch',
            'enable_sequential_dispatch',
            'auto_expand_radius',
            'default_radius_miles',
        ])->delete();
    }
}
