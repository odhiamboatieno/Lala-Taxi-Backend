<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile_number', 30);
            $table->string('country_code', 20)->nullable();
            $table->string('otp', 6);
            $table->string('purpose', 50)->default('general');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['mobile_number', 'country_code', 'purpose'], 'otp_verifications_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_verifications');
    }
}
