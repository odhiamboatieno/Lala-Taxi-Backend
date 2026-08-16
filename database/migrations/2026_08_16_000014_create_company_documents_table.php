<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('document_id', 50);
            $table->text('document');
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('expired_date')->nullable();
            $table->enum('status', ['0', '1', '2'])->default('0');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->index('company_id', 'company_documents_company_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_documents');
    }
};
