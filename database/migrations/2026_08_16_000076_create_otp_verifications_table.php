<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile_number', 30);
            $table->string('country_code', 20)->nullable();
            $table->string('otp', 6);
            $table->string('purpose', 50)->default('general');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['mobile_number', 'country_code', 'purpose'], 'otp_verifications_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
