<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_verification_otps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('type', 20);
            $table->string('otp', 20);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('attempts')->default('0');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['company_id', 'type'], 'company_verification_otps_company_id_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_verification_otps');
    }
};
