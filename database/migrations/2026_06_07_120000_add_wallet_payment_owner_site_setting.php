<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the platform-wide `wallet_payment_owner` site setting.
     *
     * Allowed values:
     *   - admin   : All payment gateway credentials live in the global
     *               `payment_gateway` table (default - existing behaviour).
     *   - company : Each SaaS company uses its own credentials stored in
     *               `company_payment_gateways`.  The global table is used
     *               only as a fallback.
     */
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $exists = DB::table('site_settings')->where('name', 'wallet_payment_owner')->exists();
        if (! $exists) {
            DB::table('site_settings')->insert([
                'name'  => 'wallet_payment_owner',
                'value' => 'admin',
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')->where('name', 'wallet_payment_owner')->delete();
    }
};
