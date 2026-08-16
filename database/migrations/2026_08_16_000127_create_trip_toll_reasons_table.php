<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_toll_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->text('reason');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('trip_id', 'trip_toll_reasons_trip_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_toll_reasons');
    }
};
