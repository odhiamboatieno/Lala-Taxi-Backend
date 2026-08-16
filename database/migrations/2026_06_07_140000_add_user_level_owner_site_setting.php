<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the platform-wide `user_level_owner` site setting.
     *
     * Allowed values:
     *   - admin   : every driver / customer level lookup uses the global
     *               `user_levels` rows (company_id = NULL).  Companies
     *               inherit those levels automatically.
     *   - company : SaaS companies may inherit an admin level and override
     *               its values, or define their own levels from scratch.
     */
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $exists = DB::table('site_settings')->where('name', 'user_level_owner')->exists();
        if (! $exists) {
            DB::table('site_settings')->insert([
                'name'  => 'user_level_owner',
                'value' => 'admin',
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')->where('name', 'user_level_owner')->delete();
    }
};
