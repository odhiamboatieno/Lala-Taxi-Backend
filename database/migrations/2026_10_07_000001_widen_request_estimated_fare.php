<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request', function (Blueprint $table) {
            $table->decimal('estimated_fare', 12, 2)->default('0.00')->change();
        });
    }

    public function down(): void
    {
        // Retain the widened range: shrinking can corrupt existing valid fares.
    }
};
