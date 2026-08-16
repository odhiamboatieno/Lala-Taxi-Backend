<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('request_id');
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
            $table->boolean('proof_of_delivery_required')->default(true);
            $table->text('pod_signature')->nullable();
            $table->string('pod_photo', 255)->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('request')->onDelete('cascade');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->text('parcel_pod_signature')->nullable()->after('status');
            $table->string('parcel_pod_photo', 255)->nullable()->after('parcel_pod_signature');
            $table->timestamp('parcel_delivered_at')->nullable()->after('parcel_pod_photo');
            $table->timestamp('parcel_picked_up_at')->nullable()->after('parcel_delivered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_requests');
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['parcel_pod_signature', 'parcel_pod_photo', 'parcel_delivered_at', 'parcel_picked_up_at']);
        });
    }
};
