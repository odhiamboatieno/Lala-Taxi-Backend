<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('preference_id', 10);
            $table->enum('default', ['no', 'yes']);
            $table->string('type', 30);
            $table->string('payout_id', 100);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('type', 'payout_credentials_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_credentials');
    }
};
