<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('document_name', 191);
            $table->enum('type', ['Driver', 'Vehicle', 'Company']);
            $table->string('country_code', 191);
            $table->enum('expire_on_date', ['Yes', 'No'])->default('No');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('status', 'documents_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
