<?php

use App\Models\SiteSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('global_vehicle_categories')) {
            Schema::create('global_vehicle_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->boolean('is_visible_to_rider')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status')->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_saas_settings')) {
            Schema::create('company_saas_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->boolean('is_saas_enabled')->default(false);
                $table->enum('dispatch_mode', ['global', 'saas', 'hybrid'])->default('global');
                $table->boolean('use_own_locations')->default(false);
                $table->boolean('use_own_fares')->default(false);
                $table->boolean('use_own_fees')->default(false);
                $table->boolean('use_own_vehicle_types')->default(false);
                $table->boolean('use_own_dispatch_settings')->default(false);
                $table->boolean('use_own_branding')->default(false);
                $table->string('domain')->nullable();
                $table->string('subdomain')->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->unique('company_id');
            });
        }
        $this->ensureForeignKey('company_saas_settings', 'sd_csset_company_fk', ['company_id'], 'companies', ['id'], 'cascade');

        if (!Schema::hasTable('company_service_areas')) {
            Schema::create('company_service_areas', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('name');
                $table->string('country')->nullable();
                $table->string('state')->nullable();
                $table->string('city')->nullable();
                $table->string('zone_name')->nullable();
                $table->longText('polygon')->nullable();
                $table->boolean('pickup_enabled')->default(true);
                $table->boolean('dropoff_enabled')->default(true);
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->index(['company_id', 'status']);
            });
        }
        $this->ensureForeignKey('company_service_areas', 'sd_csa_company_fk', ['company_id'], 'companies', ['id'], 'cascade');

        if (!Schema::hasTable('company_vehicle_types')) {
            Schema::create('company_vehicle_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('global_vehicle_category_id')->nullable();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->unsignedTinyInteger('passenger_capacity')->default(4);
                $table->unsignedTinyInteger('luggage_capacity')->default(0);
                $table->boolean('is_ev')->default(false);
                $table->boolean('is_accessible')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->unique(['company_id', 'slug']);
            });
        }
        $this->ensureForeignKey('company_vehicle_types', 'sd_cvt_company_fk', ['company_id'], 'companies', ['id'], 'cascade');
        $this->ensureForeignKey('company_vehicle_types', 'sd_cvt_gvc_fk', ['global_vehicle_category_id'], 'global_vehicle_categories', ['id'], 'set null');

        if (!Schema::hasTable('company_vehicle_category_mappings')) {
            Schema::create('company_vehicle_category_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('global_vehicle_category_id');
                $table->unsignedBigInteger('company_vehicle_type_id');
                $table->unsignedInteger('priority')->default(0);
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->unique(['company_id', 'global_vehicle_category_id', 'company_vehicle_type_id'], 'company_vehicle_category_mapping_unique');
            });
        }
        $this->ensureForeignKey('company_vehicle_category_mappings', 'sd_cvcm_company_fk', ['company_id'], 'companies', ['id'], 'cascade');
        $this->ensureForeignKey('company_vehicle_category_mappings', 'sd_cvcm_gvc_fk', ['global_vehicle_category_id'], 'global_vehicle_categories', ['id'], 'cascade');
        $this->ensureForeignKey('company_vehicle_category_mappings', 'sd_cvcm_cvt_fk', ['company_vehicle_type_id'], 'company_vehicle_types', ['id'], 'cascade');

        if (!Schema::hasTable('company_fare_rules')) {
            Schema::create('company_fare_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('company_vehicle_type_id');
                $table->unsignedBigInteger('company_service_area_id')->nullable();
                $table->decimal('base_fare', 11, 2)->default(0);
                $table->decimal('per_km', 11, 2)->default(0);
                $table->decimal('per_minute', 11, 2)->default(0);
                $table->decimal('minimum_fare', 11, 2)->default(0);
                $table->decimal('waiting_charge_per_minute', 11, 2)->default(0);
                $table->decimal('cancellation_fee', 11, 2)->default(0);
                $table->string('night_surcharge_type')->nullable();
                $table->decimal('night_surcharge_value', 11, 2)->nullable();
                $table->decimal('airport_fee', 11, 2)->nullable();
                $table->string('commission_type')->nullable();
                $table->decimal('commission_value', 11, 2)->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->index(['company_id', 'company_vehicle_type_id', 'status']);
            });
        }
        $this->ensureForeignKey('company_fare_rules', 'sd_cfr_company_fk', ['company_id'], 'companies', ['id'], 'cascade');
        $this->ensureForeignKey('company_fare_rules', 'sd_cfr_cvt_fk', ['company_vehicle_type_id'], 'company_vehicle_types', ['id'], 'cascade');
        $this->ensureForeignKey('company_fare_rules', 'sd_cfr_csa_fk', ['company_service_area_id'], 'company_service_areas', ['id'], 'set null');

        if (!Schema::hasTable('company_fee_settings')) {
            Schema::create('company_fee_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('fee_name');
                $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
                $table->decimal('fee_value', 11, 2)->default(0);
                $table->enum('applies_to', ['rider', 'driver', 'company', 'trip'])->default('trip');
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->index(['company_id', 'status']);
            });
        }
        $this->ensureForeignKey('company_fee_settings', 'sd_cfs_company_fk', ['company_id'], 'companies', ['id'], 'cascade');

        if (!Schema::hasTable('company_dispatch_settings')) {
            Schema::create('company_dispatch_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('dispatch_strategy')->nullable();
                $table->decimal('driver_search_radius', 8, 2)->default(5);
                $table->decimal('max_driver_search_radius', 8, 2)->default(12);
                $table->unsignedInteger('request_timeout_seconds')->default(30);
                $table->unsignedInteger('retry_rounds')->default(3);
                $table->boolean('allow_auto_dispatch')->default(true);
                $table->boolean('allow_manual_dispatch')->default(true);
                $table->boolean('allow_scheduled_trips')->default(true);
                $table->boolean('allow_airport_trips')->default(true);
                $table->boolean('allow_out_of_area_trips')->default(false);
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->unique('company_id');
            });
        }
        $this->ensureForeignKey('company_dispatch_settings', 'sd_cds_company_fk', ['company_id'], 'companies', ['id'], 'cascade');

        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'operator_type')) {
                $table->string('operator_type')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('trips', 'operator_id')) {
                $table->unsignedInteger('operator_id')->nullable()->after('operator_type');
            }
            if (!Schema::hasColumn('trips', 'company_vehicle_type_id')) {
                $table->unsignedBigInteger('company_vehicle_type_id')->nullable()->after('car_id');
            }
            if (!Schema::hasColumn('trips', 'global_vehicle_type_id')) {
                $table->unsignedInteger('global_vehicle_type_id')->nullable()->after('company_vehicle_type_id');
            }
            if (!Schema::hasColumn('trips', 'global_vehicle_category_id')) {
                $table->unsignedBigInteger('global_vehicle_category_id')->nullable()->after('global_vehicle_type_id');
            }
        });

        Schema::table('request', function (Blueprint $table) {
            if (!Schema::hasColumn('request', 'company_id')) {
                $table->unsignedInteger('company_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('request', 'operator_type')) {
                $table->string('operator_type')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('request', 'operator_id')) {
                $table->unsignedInteger('operator_id')->nullable()->after('operator_type');
            }
            if (!Schema::hasColumn('request', 'company_vehicle_type_id')) {
                $table->unsignedBigInteger('company_vehicle_type_id')->nullable()->after('car_id');
            }
            if (!Schema::hasColumn('request', 'global_vehicle_type_id')) {
                $table->unsignedInteger('global_vehicle_type_id')->nullable()->after('company_vehicle_type_id');
            }
            if (!Schema::hasColumn('request', 'global_vehicle_category_id')) {
                $table->unsignedBigInteger('global_vehicle_category_id')->nullable()->after('global_vehicle_type_id');
            }
        });

        Schema::table('schedule_ride', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_ride', 'operator_type')) {
                $table->string('operator_type')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('schedule_ride', 'operator_id')) {
                $table->unsignedInteger('operator_id')->nullable()->after('operator_type');
            }
            if (!Schema::hasColumn('schedule_ride', 'company_vehicle_type_id')) {
                $table->unsignedBigInteger('company_vehicle_type_id')->nullable()->after('car_id');
            }
            if (!Schema::hasColumn('schedule_ride', 'global_vehicle_type_id')) {
                $table->unsignedInteger('global_vehicle_type_id')->nullable()->after('company_vehicle_type_id');
            }
            if (!Schema::hasColumn('schedule_ride', 'global_vehicle_category_id')) {
                $table->unsignedBigInteger('global_vehicle_category_id')->nullable()->after('global_vehicle_type_id');
            }
        });

        SiteSettings::updateOrCreate(
            ['name' => config('saas_dispatch.module_site_setting_key', 'saas_dispatch_module_enabled')],
            ['value' => '0']
        );
    }

    public function down(): void
    {
        SiteSettings::where('name', config('saas_dispatch.module_site_setting_key', 'saas_dispatch_module_enabled'))->delete();

        Schema::table('schedule_ride', function (Blueprint $table) {
            foreach (['operator_type', 'operator_id', 'company_vehicle_type_id', 'global_vehicle_type_id', 'global_vehicle_category_id'] as $column) {
                if (Schema::hasColumn('schedule_ride', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('request', function (Blueprint $table) {
            foreach (['company_id', 'operator_type', 'operator_id', 'company_vehicle_type_id', 'global_vehicle_type_id', 'global_vehicle_category_id'] as $column) {
                if (Schema::hasColumn('request', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            foreach (['operator_type', 'operator_id', 'company_vehicle_type_id', 'global_vehicle_type_id', 'global_vehicle_category_id'] as $column) {
                if (Schema::hasColumn('trips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('company_dispatch_settings');
        Schema::dropIfExists('company_fee_settings');
        Schema::dropIfExists('company_fare_rules');
        Schema::dropIfExists('company_vehicle_category_mappings');
        Schema::dropIfExists('company_vehicle_types');
        Schema::dropIfExists('company_service_areas');
        Schema::dropIfExists('company_saas_settings');
        Schema::dropIfExists('global_vehicle_categories');
    }

    protected function ensureForeignKey(
        string $table,
        string $constraintName,
        array $columns,
        string $referencedTable,
        array $referencedColumns,
        ?string $onDelete = null
    ): void {
        if (!Schema::hasTable($table) || $this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($constraintName, $columns, $referencedTable, $referencedColumns, $onDelete) {
            $foreign = $blueprint->foreign($columns, $constraintName)
                ->references($referencedColumns)
                ->on($referencedTable);

            if ($onDelete === 'cascade') {
                $foreign->onDelete('cascade');
            }

            if ($onDelete === 'set null') {
                $foreign->nullOnDelete();
            }
        });
    }

    protected function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        return (bool) $result;
    }
};
