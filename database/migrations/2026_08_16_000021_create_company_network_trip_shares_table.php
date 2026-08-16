<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_network_trip_shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('source_company_id');
            $table->unsignedInteger('target_company_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->unsignedInteger('request_id')->nullable();
            $table->string('status', 191)->default('pending');
            $table->text('share_note')->nullable();
            $table->text('response_note')->nullable();
            $table->timestamp('shared_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->unsignedInteger('accepted_by_company_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['source_company_id', 'status'], 'company_network_trip_shares_source_company_id_status_index');
            $table->index(['target_company_id', 'status'], 'company_network_trip_shares_target_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_network_trip_shares');
    }
};
