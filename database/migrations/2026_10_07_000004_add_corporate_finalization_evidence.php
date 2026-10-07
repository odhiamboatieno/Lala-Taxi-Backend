<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('trips', 'corporate_financial_snapshot')) {
            Schema::table('trips', fn (Blueprint $t) => $t->json('corporate_financial_snapshot')->nullable());
        }
        if (!Schema::hasColumn('company_corporate_trip_records', 'payment_id')) {
            Schema::table('company_corporate_trip_records', function (Blueprint $t) {
                $t->unsignedInteger('payment_id')->nullable()->unique();
                $t->foreign('payment_id', 'cctr_payment_fk')->references('id')->on('payment')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Preserve finalized financial evidence and payout linkage on code rollback.
    }
};
