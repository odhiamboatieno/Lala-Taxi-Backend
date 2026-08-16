<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 191);
            $table->string('email', 191);
            $table->string('password', 60);
            $table->string('country_code', 5);
            $table->unsignedInteger('country_id')->nullable();
            $table->string('mobile_number', 20);
            $table->string('remember_token', 100)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unique('username', 'admins_username_unique');
            $table->unique('email', 'admins_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
