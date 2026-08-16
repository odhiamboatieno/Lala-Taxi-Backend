<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trips')) {
            return;
        }

        Schema::table('trips', function (Blueprint $table) {
            $this->addIndexIfMissing($table, 'trips_status_idx', ['status']);
            $this->addIndexIfMissing($table, 'trips_created_at_idx', ['created_at']);

            if (Schema::hasColumn('trips', 'company_id')) {
                $this->addIndexIfMissing($table, 'trips_company_id_idx', ['company_id']);
            }

            $this->addIndexIfMissing($table, 'trips_driver_id_idx', ['driver_id']);
            $this->addIndexIfMissing($table, 'trips_user_id_idx', ['user_id']);
            $this->addIndexIfMissing($table, 'trips_car_id_idx', ['car_id']);

            if (Schema::hasColumn('trips', 'payment_status')) {
                $this->addIndexIfMissing($table, 'trips_payment_status_idx', ['payment_status']);
            }

            if (Schema::hasColumn('trips', 'operator_type') && Schema::hasColumn('trips', 'operator_id')) {
                $this->addIndexIfMissing($table, 'trips_operator_lookup_idx', ['operator_type', 'operator_id']);
            }

            if (Schema::hasColumn('trips', 'company_vehicle_type_id')) {
                $this->addIndexIfMissing($table, 'trips_company_vehicle_type_idx', ['company_vehicle_type_id']);
            }

            if (Schema::hasColumn('trips', 'global_vehicle_category_id')) {
                $this->addIndexIfMissing($table, 'trips_global_vehicle_category_idx', ['global_vehicle_category_id']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trips')) {
            return;
        }

        Schema::table('trips', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'trips_status_idx');
            $this->dropIndexIfExists($table, 'trips_created_at_idx');
            $this->dropIndexIfExists($table, 'trips_company_id_idx');
            $this->dropIndexIfExists($table, 'trips_driver_id_idx');
            $this->dropIndexIfExists($table, 'trips_user_id_idx');
            $this->dropIndexIfExists($table, 'trips_car_id_idx');
            $this->dropIndexIfExists($table, 'trips_payment_status_idx');
            $this->dropIndexIfExists($table, 'trips_operator_lookup_idx');
            $this->dropIndexIfExists($table, 'trips_company_vehicle_type_idx');
            $this->dropIndexIfExists($table, 'trips_global_vehicle_category_idx');
        });
    }

    private function addIndexIfMissing(Blueprint $table, string $indexName, array $columns): void
    {
        if (! $this->indexExists('trips', $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        if ($this->indexExists('trips', $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);

        return ! empty($result);
    }
};
