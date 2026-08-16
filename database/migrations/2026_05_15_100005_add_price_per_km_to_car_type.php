<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_type', function (Blueprint $table) {
            $table->decimal('price_per_km', 8, 2)->default(15.00)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('car_type', function (Blueprint $table) {
            $table->dropColumn('price_per_km');
        });
    }
};
