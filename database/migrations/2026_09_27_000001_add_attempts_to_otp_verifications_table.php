<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('otp_verifications', 'attempts')) {
            return;
        }

        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->after('purpose');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('otp_verifications', 'attempts')) {
            return;
        }

        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->dropColumn('attempts');
        });
    }
};
