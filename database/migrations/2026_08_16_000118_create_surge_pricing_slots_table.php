<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surge_pricing_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('surge_pricing_id');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['slot_date', 'start_time', 'end_time'], 'sp_slots_date_time_idx');
            $table->index('surge_pricing_id', 'sp_slots_pricing_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_pricing_slots');
    }
};
