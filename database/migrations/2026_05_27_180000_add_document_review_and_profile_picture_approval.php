<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_documents')) {
            Schema::table('driver_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_documents', 'original_name')) {
                    $table->string('original_name')->nullable()->after('document');
                }
                if (!Schema::hasColumn('driver_documents', 'mime_type')) {
                    $table->string('mime_type', 120)->nullable()->after('original_name');
                }
                if (!Schema::hasColumn('driver_documents', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
                }
                if (!Schema::hasColumn('driver_documents', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('status');
                }
                if (!Schema::hasColumn('driver_documents', 'uploaded_at')) {
                    $table->timestamp('uploaded_at')->nullable()->after('rejection_reason');
                }
                if (!Schema::hasColumn('driver_documents', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('uploaded_at');
                }
                if (!Schema::hasColumn('driver_documents', 'reviewed_by')) {
                    $table->unsignedInteger('reviewed_by')->nullable()->after('reviewed_at');
                }
            });
        }

        if (Schema::hasTable('profile_picture')) {
            Schema::table('profile_picture', function (Blueprint $table) {
                if (!Schema::hasColumn('profile_picture', 'pending_src')) {
                    $table->text('pending_src')->nullable()->after('src');
                }
                if (!Schema::hasColumn('profile_picture', 'approval_status')) {
                    $table->string('approval_status', 30)->nullable()->after('photo_source');
                }
                if (!Schema::hasColumn('profile_picture', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('approval_status');
                }
                if (!Schema::hasColumn('profile_picture', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
                }
                if (!Schema::hasColumn('profile_picture', 'reviewed_by')) {
                    $table->unsignedInteger('reviewed_by')->nullable()->after('reviewed_at');
                }
                if (!Schema::hasColumn('profile_picture', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('reviewed_by');
                }
            });
        }

        DB::table('site_settings')->updateOrInsert(
            ['name' => 'driver_profile_image_auto_approval'],
            ['value' => '1']
        );
    }

    public function down(): void
    {
    }
};
