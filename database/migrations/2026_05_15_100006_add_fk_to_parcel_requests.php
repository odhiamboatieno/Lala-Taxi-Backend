<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->unsignedInteger('parcel_category_id')->nullable()->after('request_id');
            $table->unsignedInteger('parcel_task_id')->nullable()->after('parcel_category_id');

            $table->foreign('parcel_category_id')->references('id')->on('parcel_categories')->onDelete('set null');
            $table->foreign('parcel_task_id')->references('id')->on('parcel_tasks')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->dropForeign(['parcel_category_id']);
            $table->dropForeign(['parcel_task_id']);
            $table->dropColumn(['parcel_category_id', 'parcel_task_id']);
        });
    }
};
