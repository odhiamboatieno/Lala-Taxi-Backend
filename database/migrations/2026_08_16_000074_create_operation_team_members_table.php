<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_team_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('mobile_number', 32)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('identity_type', 120)->nullable();
            $table->string('identity_number', 120)->nullable();
            $table->string('image', 191)->nullable();
            $table->string('identity_image', 191)->nullable();
            $table->string('password', 191);
            $table->tinyInteger('status')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
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
