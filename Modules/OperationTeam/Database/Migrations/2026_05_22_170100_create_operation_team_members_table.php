<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operation_team_members')) {
            return;
        }

        Schema::create('operation_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('mobile_number', 32)->nullable();
            $table->string('address')->nullable();
            $table->string('identity_type', 120)->nullable();
            $table->string('identity_number', 120)->nullable();
            $table->string('image')->nullable();
            $table->string('identity_image')->nullable();
            $table->string('password');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('company_id', 'ot_members_company_idx');
            $table->index('role_id', 'ot_members_role_idx');
            $table->index('email', 'ot_members_email_idx');
            $table->index('mobile_number', 'ot_members_mobile_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_team_members');
    }
};
