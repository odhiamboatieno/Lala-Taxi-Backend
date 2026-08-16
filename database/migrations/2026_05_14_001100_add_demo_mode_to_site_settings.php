<?php

use App\Models\SiteSettings;
use Illuminate\Database\Migrations\Migration;

class AddDemoModeToSiteSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $legacySandboxValue = SiteSettings::where('name', 'otp_sandbox')->value('value');

        SiteSettings::updateOrCreate(
            ['name' => 'demo_mode'],
            ['value' => $legacySandboxValue !== null ? $legacySandboxValue : '0']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SiteSettings::where('name', 'demo_mode')->delete();
    }
}
