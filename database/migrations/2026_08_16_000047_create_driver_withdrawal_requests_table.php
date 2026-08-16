<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_withdrawal_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('payout_credential_id')->nullable();
            $table->decimal('amount', 11, 2);
            $table->string('currency_code', 10);
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'paid'])->default('pending');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['driver_id', 'status'], 'driver_withdrawal_requests_driver_id_status_index');
            $table->index(['driver_id', 'created_at'], 'driver_withdrawal_requests_driver_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_withdrawal_requests');
    }
};
