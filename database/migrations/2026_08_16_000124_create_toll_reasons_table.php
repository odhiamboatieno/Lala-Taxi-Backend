<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reason', 255);
            $table->enum('status', ['Active', 'Inactive']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_reasons');
    }
};
