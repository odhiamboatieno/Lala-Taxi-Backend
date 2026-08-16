<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('cancel_reason_id');
            $table->text('cancel_comments');
            $table->enum('cancelled_by', ['Rider', 'Driver'])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index('trip_id', 'cancel_trip_id_foreign');
            $table->index('user_id', 'cancel_user_id_foreign');
            $table->index('cancel_reason_id', 'cancel_cancel_reason_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancel');
    }
};
