<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('manual_booking_drafts')) {
            return;
        }

        Schema::create('manual_booking_drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->string('dispatcher_type', 20)->default('admin');
            $table->unsignedInteger('dispatcher_id')->nullable();
            $table->string('name', 160);
            $table->longText('payload')->nullable();
            $table->string('status', 20)->default('Active');
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['dispatcher_type', 'dispatcher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_booking_drafts');
    }
};
