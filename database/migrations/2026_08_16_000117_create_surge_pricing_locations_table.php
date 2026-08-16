<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surge_pricing_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('surge_pricing_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['surge_pricing_id', 'location_id'], 'sp_loc_unique');
            $table->index('location_id', 'sp_loc_location_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_pricing_locations');
    }
};
