<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_payout_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('preference_id', 10);
            $table->enum('default', ['no', 'yes']);
            $table->string('type', 30);
            $table->string('payout_id', 100);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('company_id', 'company_payout_credentials_company_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payout_credentials');
    }
};
