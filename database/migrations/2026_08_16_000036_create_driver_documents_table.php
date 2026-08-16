<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['Driver', 'Vehicle'])->default('Driver');
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('user_id');
            $table->string('document_id', 50);
            $table->text('document');
            $table->string('original_name', 191)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['0', '1', '2'])->default('0');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->date('expired_date')->nullable();
            $table->index(['type', 'vehicle_id', 'user_id'], 'driver_documents_type_vehicle_id_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};
