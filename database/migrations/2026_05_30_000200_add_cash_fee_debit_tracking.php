<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashFeeDebitTracking extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (! Schema::hasColumn('trips', 'cash_fees_debited_at')) {
                $table->timestamp('cash_fees_debited_at')->nullable()->after('driver_rating_triggered_at');
            }
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'cash_fees_debited_at')) {
                $table->dropColumn('cash_fees_debited_at');
            }
        });
    }
}
