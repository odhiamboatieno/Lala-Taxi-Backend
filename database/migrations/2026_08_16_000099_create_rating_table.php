<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('rater_id')->nullable();
            $table->string('rater_type', 20)->nullable();
            $table->integer('rider_rating');
            $table->text('rider_comments');
            $table->integer('driver_rating');
            $table->text('driver_comments');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unique(['trip_id', 'rater_id', 'rater_type'], 'rating_trip_rater_unique');
            $table->index('trip_id', 'rating_trip_id_foreign');
            $table->index('user_id', 'rating_user_id_foreign');
            $table->index('driver_id', 'rating_driver_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating');
    }
};
