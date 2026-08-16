<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedule_ride')) {
            return;
        }

        Schema::table('schedule_ride', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_ride', 'via_location')) {
                $table->string('via_location')->nullable()->after('drop_location');
            }
            if (! Schema::hasColumn('schedule_ride', 'via_latitude')) {
                $table->decimal('via_latitude', 10, 7)->nullable()->after('via_location');
            }
            if (! Schema::hasColumn('schedule_ride', 'via_longitude')) {
                $table->decimal('via_longitude', 10, 7)->nullable()->after('via_latitude');
            }
            if (! Schema::hasColumn('schedule_ride', 'booking_channel')) {
                $table->string('booking_channel', 40)->nullable()->after('booking_type');
            }
            if (! Schema::hasColumn('schedule_ride', 'workspace_label')) {
                $table->string('workspace_label', 120)->nullable()->after('booking_channel');
            }
            if (! Schema::hasColumn('schedule_ride', 'draft_id')) {
                $table->unsignedInteger('draft_id')->nullable()->after('workspace_label');
            }
            if (! Schema::hasColumn('schedule_ride', 'template_id')) {
                $table->unsignedInteger('template_id')->nullable()->after('draft_id');
            }
            if (! Schema::hasColumn('schedule_ride', 'return_parent_id')) {
                $table->unsignedInteger('return_parent_id')->nullable()->after('template_id');
            }
            if (! Schema::hasColumn('schedule_ride', 'booking_meta')) {
                $table->longText('booking_meta')->nullable()->after('return_parent_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedule_ride')) {
            return;
        }

        Schema::table('schedule_ride', function (Blueprint $table) {
            foreach ([
                'via_location',
                'via_latitude',
                'via_longitude',
                'booking_channel',
                'workspace_label',
                'draft_id',
                'template_id',
                'return_parent_id',
                'booking_meta',
            ] as $column) {
                if (Schema::hasColumn('schedule_ride', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
