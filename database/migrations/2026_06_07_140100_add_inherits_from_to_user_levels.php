<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the `inherits_from_id` column to `user_levels`.
     *
     * When a company-owned level (company_id != NULL) sets
     * `inherits_from_id` to an admin level (company_id = NULL) the
     * company is "inheriting" from that admin level.  The company's own
     * values are layered on top of the admin defaults, so companies can
     * pick a base level from the admin and override only the bits they
     * need (e.g. lower point thresholds, different reward amount).
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_levels')) {
            return;
        }

        if (Schema::hasColumn('user_levels', 'inherits_from_id')) {
            return;
        }

        Schema::table('user_levels', function (Blueprint $table) {
            $table->unsignedBigInteger('inherits_from_id')->nullable()->after('company_id');
            $table->index(['inherits_from_id'], 'user_levels_inherits_from_idx');
            $table->foreign('inherits_from_id')
                ->references('id')->on('user_levels')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_levels')) {
            return;
        }

        if (! Schema::hasColumn('user_levels', 'inherits_from_id')) {
            return;
        }

        Schema::table('user_levels', function (Blueprint $table) {
            $table->dropForeign(['inherits_from_id']);
            $table->dropIndex('user_levels_inherits_from_idx');
            $table->dropColumn('inherits_from_id');
        });
    }
};
