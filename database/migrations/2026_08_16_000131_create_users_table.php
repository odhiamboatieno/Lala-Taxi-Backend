<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('country_code', 100);
            $table->enum('gender', ['1', '2'])->nullable();
            $table->string('mobile_number', 20);
            $table->string('password', 191)->nullable();
            $table->enum('user_type', ['Rider', 'Driver'])->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->text('firebase_token')->nullable();
            $table->string('fb_id', 50)->nullable();
            $table->string('google_id', 50)->nullable();
            $table->string('apple_id', 191)->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Pending', 'Car_details', 'Document_details'])->nullable();
            $table->enum('device_type', ['1', '2'])->nullable();
            $table->text('device_id');
            $table->tinyInteger('is_online')->default('0');
            $table->tinyInteger('is_available')->default('0');
            $table->unsignedInteger('current_trip_id')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('online_started_at')->nullable();
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->string('geohash', 191)->nullable();
            $table->decimal('heading', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->string('socket_id', 191)->nullable();
            $table->unsignedInteger('service_type_id')->nullable();
            $table->unsignedInteger('vehicle_type_id')->nullable();
            $table->string('referral_code', 12);
            $table->string('used_referral_code', 12)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->string('language', 50);
            $table->integer('driver_radius')->default('10');
            $table->unsignedInteger('country_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unique('fb_id', 'users_fb_id_unique');
            $table->unique('google_id', 'users_google_id_unique');
            $table->unique('apple_id', 'users_apple_id_unique');
            $table->index('company_id', 'users_company_id_foreign');
            $table->index('country_id', 'users_country_id_foreign');
            $table->index(['status', 'user_type'], 'users_status_user_type_index');
            $table->index(['user_type', 'company_id', 'is_online', 'is_available'], 'users_dispatch_presence_idx');
            $table->index(['current_lat', 'current_lng'], 'users_dispatch_location_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
