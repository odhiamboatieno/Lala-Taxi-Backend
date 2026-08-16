<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_peak_fare_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_fare_rule_id');
            $table->unsignedTinyInteger('day')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 8, 2);
            $table->string('type', 20);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_peak_fare_details');
    }
};
