<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_picture', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->text('src');
            $table->text('pending_src')->nullable();
            $table->enum('photo_source', ['Facebook', 'Google', 'Local'])->nullable();
            $table->string('approval_status', 30)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unique('user_id', 'profile_picture_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_picture');
    }
};
