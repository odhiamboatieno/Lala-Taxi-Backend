<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['request', 'trips', 'schedule_ride'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'surge_pricing_id')) {
                    $table->unsignedBigInteger('surge_pricing_id')->nullable()->after('extra_fare_reason');
                }
                if (!Schema::hasColumn($tableName, 'surge_name')) {
                    $table->string('surge_name')->nullable()->after('surge_pricing_id');
                }
                if (!Schema::hasColumn($tableName, 'surge_percentage')) {
                    $table->decimal('surge_percentage', 8, 2)->default(0)->after('surge_name');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['request', 'trips', 'schedule_ride'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['surge_percentage', 'surge_name', 'surge_pricing_id'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
