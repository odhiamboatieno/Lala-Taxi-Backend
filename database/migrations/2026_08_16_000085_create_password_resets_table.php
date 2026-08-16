<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email', 191);
            $table->string('token', 191);
            $table->timestamp('created_at')->default('current_timestamp() ON UPDATE current_timestamp()');
            $table->index('email', 'password_resets_email_index');
            $table->index('token', 'password_resets_token_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
