<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SiteSettings;

return new class extends Migration
{
    public function up(): void
    {
        SiteSettings::updateOrCreate(
            ['name' => 'manual_booking_module_enabled'],
            ['value' => '1']
        );
    }

    public function down(): void
    {
        SiteSettings::where('name', 'manual_booking_module_enabled')->delete();
    }
};
