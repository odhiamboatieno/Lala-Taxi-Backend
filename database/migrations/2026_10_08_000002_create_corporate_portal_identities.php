<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('corporate_portal_users')) {
            Schema::create('corporate_portal_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('corporate_account_id');
                $table->string('name', 255);
                $table->string('email', 255)->unique();
                $table->string('password', 255);
                $table->string('role', 20);
                $table->string('status', 20)->default('Active');
                $table->unsignedInteger('session_version')->default(1);
                $table->timestamp('password_set_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->foreign('corporate_account_id')->references('id')->on('company_corporate_accounts')->restrictOnDelete();
                $table->index(['corporate_account_id', 'status']);
            });
        }
        if (!Schema::hasTable('corporate_password_resets')) {
            Schema::create('corporate_password_resets', function (Blueprint $table) {
                $table->string('email', 255)->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Retain identity/audit references and credentials on code rollback.
        // Access can be disabled without destroying employer history.
    }
};
