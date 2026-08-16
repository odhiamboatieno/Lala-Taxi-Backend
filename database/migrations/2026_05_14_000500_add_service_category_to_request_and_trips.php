<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request', function (Blueprint $table) {
            if (!Schema::hasColumn('request', 'service_category')) {
                $table->string('service_category', 30)->default('ride')->after('status');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'service_category')) {
                $table->string('service_category', 30)->default('ride')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('request', function (Blueprint $table) {
            if (Schema::hasColumn('request', 'service_category')) {
                $table->dropColumn('service_category');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'service_category')) {
                $table->dropColumn('service_category');
            }
        });
    }
};
