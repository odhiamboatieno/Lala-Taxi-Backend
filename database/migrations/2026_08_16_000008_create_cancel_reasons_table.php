<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancel_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reason', 100);
            $table->enum('cancelled_by', ['Rider', 'Driver', 'Admin', 'Company'])->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancel_reasons');
    }
};
