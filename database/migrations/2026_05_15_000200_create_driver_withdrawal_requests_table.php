<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverWithdrawalRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('driver_withdrawal_requests')) {
            return;
        }

        Schema::create('driver_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('payout_credential_id')->nullable();
            $table->decimal('amount', 11, 2);
            $table->string('currency_code', 10);
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'paid'])->default('pending');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['driver_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_withdrawal_requests');
    }
}
