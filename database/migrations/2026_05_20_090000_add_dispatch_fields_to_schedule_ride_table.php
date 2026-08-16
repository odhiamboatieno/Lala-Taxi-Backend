<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchFieldsToScheduleRideTable extends Migration
{
    public function up()
    {
        Schema::table('schedule_ride', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_ride', 'booking_mode')) {
                $table->string('booking_mode')->default('scheduled')->after('booking_type');
            }
            if (!Schema::hasColumn('schedule_ride', 'dispatch_mode')) {
                $table->string('dispatch_mode')->default('manual')->after('booking_mode');
            }
            if (!Schema::hasColumn('schedule_ride', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('dispatch_mode');
            }
            if (!Schema::hasColumn('schedule_ride', 'dispatch_at')) {
                $table->dateTime('dispatch_at')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('schedule_ride', 'assignment_status')) {
                $table->string('assignment_status')->default('unassigned')->after('dispatch_at');
            }
            if (!Schema::hasColumn('schedule_ride', 'assignment_timeout_seconds')) {
                $table->integer('assignment_timeout_seconds')->default(30)->after('assignment_status');
            }
            if (!Schema::hasColumn('schedule_ride', 'assignment_attempt_count')) {
                $table->integer('assignment_attempt_count')->default(0)->after('assignment_timeout_seconds');
            }
            if (!Schema::hasColumn('schedule_ride', 'assigned_driver_id')) {
                $table->unsignedInteger('assigned_driver_id')->nullable()->after('assignment_attempt_count');
            }
            if (!Schema::hasColumn('schedule_ride', 'last_assignment_attempt_at')) {
                $table->dateTime('last_assignment_attempt_at')->nullable()->after('assigned_driver_id');
            }
            if (!Schema::hasColumn('schedule_ride', 'dispatcher_note')) {
                $table->text('dispatcher_note')->nullable()->after('last_assignment_attempt_at');
            }
            if (!Schema::hasColumn('schedule_ride', 'driver_note')) {
                $table->text('driver_note')->nullable()->after('dispatcher_note');
            }
            if (!Schema::hasColumn('schedule_ride', 'priority')) {
                $table->string('priority')->default('normal')->after('driver_note');
            }
            if (!Schema::hasColumn('schedule_ride', 'is_dispatch_override')) {
                $table->boolean('is_dispatch_override')->default(false)->after('priority');
            }
            if (!Schema::hasColumn('schedule_ride', 'dispatch_policy_snapshot')) {
                $table->json('dispatch_policy_snapshot')->nullable()->after('is_dispatch_override');
            }
            if (!Schema::hasColumn('schedule_ride', 'assign_now')) {
                $table->boolean('assign_now')->default(false)->after('dispatch_policy_snapshot');
            }
        });
    }

    public function down()
    {
        Schema::table('schedule_ride', function (Blueprint $table) {
            $columns = [
                'booking_mode',
                'dispatch_mode',
                'scheduled_at',
                'dispatch_at',
                'assignment_status',
                'assignment_timeout_seconds',
                'assignment_attempt_count',
                'assigned_driver_id',
                'last_assignment_attempt_at',
                'dispatcher_note',
                'driver_note',
                'priority',
                'is_dispatch_override',
                'dispatch_policy_snapshot',
                'assign_now',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('schedule_ride', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
