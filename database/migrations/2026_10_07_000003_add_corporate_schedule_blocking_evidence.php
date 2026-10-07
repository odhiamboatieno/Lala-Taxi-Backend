<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['billing_blocked_at', 'billing_blocked_reason'] as $column) {
            if (!Schema::hasColumn('schedule_ride', $column)) {
                Schema::table('schedule_ride', function (Blueprint $table) use ($column) {
                    $column === 'billing_blocked_at' ? $table->timestamp($column)->nullable() : $table->string($column)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        // Retain scheduling evidence on application rollback.
    }
};
