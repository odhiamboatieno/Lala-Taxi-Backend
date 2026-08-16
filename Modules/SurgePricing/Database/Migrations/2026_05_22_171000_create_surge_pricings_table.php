<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surge_pricings')) {
            return;
        }

        Schema::create('surge_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('name', 160);
            $table->string('applies_to', 20)->default('ride');
            $table->string('zone_scope', 20)->default('all');
            $table->string('ride_rate_mode', 20)->default('same');
            $table->decimal('ride_rate', 8, 2)->default(0);
            $table->decimal('parcel_rate', 8, 2)->default(0);
            $table->json('ride_rate_by_vehicle')->nullable();
            $table->string('schedule_type', 20)->default('daily');
            $table->date('range_start')->nullable();
            $table->date('range_end')->nullable();
            $table->json('weekdays')->nullable();
            $table->text('customer_note')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'status'], 'sp_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_pricings');
    }
};
