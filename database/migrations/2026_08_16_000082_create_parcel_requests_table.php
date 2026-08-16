<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('parcel_category_id')->nullable();
            $table->unsignedInteger('parcel_task_id')->nullable();
            $table->string('category', 30);
            $table->string('task_type', 50);
            $table->string('recipient_name', 120);
            $table->string('recipient_phone', 40);
            $table->string('recipient_phone_dial_code', 10)->default('+254');
            $table->string('recipient_phone_iso_code', 5)->default('KE');
            $table->string('recipient_address', 255);
            $table->text('parcel_description')->nullable();
            $table->string('estimated_weight', 50)->nullable();
            $table->text('special_instructions')->nullable();
            $table->string('pickup_contact_name', 120)->nullable();
            $table->string('pickup_contact_phone', 40)->nullable();
            $table->tinyInteger('proof_of_delivery_required')->default('1');
            $table->text('pod_signature')->nullable();
            $table->string('pod_photo', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('request_id', 'parcel_requests_request_id_foreign');
            $table->index('parcel_category_id', 'parcel_requests_parcel_category_id_foreign');
            $table->index('parcel_task_id', 'parcel_requests_parcel_task_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_requests');
    }
};
