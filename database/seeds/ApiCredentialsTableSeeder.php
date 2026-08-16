<?php

use Illuminate\Database\Seeder;

class ApiCredentialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('api_credentials')->delete();
        
        DB::table('api_credentials')->insert([
            ['name' => 'key', 'value' => 'AIzaSyB6lCQnISdsSUVFdcQYxaHxXXjvKDn9wcs', 'site' => 'GoogleMap'],
            ['name' => 'server_key', 'value' => 'AIzaSyB6lCQnISdsSUVFdcQYxaHxXXjvKDn9wcs', 'site' => 'GoogleMap'],
            ['name' => 'sid', 'value' => 'ACf64f4d6b2a55e7c56b592b6dec3919ae', 'site' => 'Twillo'],
            ['name' => 'token', 'value' => 'bc887b0e7159ab5cb0945c3fc59b345a', 'site' => 'Twillo'],
            ['name' => 'service_sid', 'value' => 'ACf64f4d6b2a55e7c56b592b6dec3919ae', 'site' => 'Twillo'],
            ['name' => 'from', 'value' => '#', 'site' => 'Twillo'],
            ['name' => 'server_key', 'value' => 'AAAAN8uxiFw:APA91bF_yPwrQdSe3cm2Ns7HzoI_5UjPpLIq2Z0Xz3-smpNlY2cvdrKmDBO-ls23kEgP3hQdwJOg1_4NObbWkFB_sYrekkDhwGlVm_RwCMtMDSLxZVU_LDLqwx81R3rNutQz7QgxUXqo', 'site' => 'FCM'],
            ['name' => 'sender_id', 'value' => '239640610908', 'site' => 'FCM'],                
            ['name' => 'client_id', 'value' => '1105678852897547', 'site' => 'Facebook'],
            ['name' => 'client_secret', 'value' => '64c4d6d3dc2ba3471297c17585a60aff', 'site' => 'Facebook'],
            ['name' => 'client_id', 'value' => '#', 'site' => 'Google'],
            ['name' => 'client_secret', 'value' => '#', 'site' => 'Google'],
            ['name' => 'sinch_key', 'value' => '55992d18-0a40-44b9-8cf6-456f729031e7', 'site' => 'Sinch'],
            ['name' => 'sinch_secret_key', 'value' => 'yx4js89/Y0KxBNHwJWv+3w==', 'site' => 'Sinch'],
            ['name' => 'service_id', 'value' => '#', 'site' => 'Apple'],
            ['name' => 'team_id', 'value' => '#', 'site' => 'Apple'],
            ['name' => 'key_id', 'value' => '#', 'site' => 'Apple'],
            ['name' => 'key_file', 'value' => '/public/key.txt', 'site' => 'Apple'],
            ['name' => 'database_url', 'value' => '#', 'site' => 'Firebase'],
            ['name' => 'service_account', 'value' => '/resources/credentials/service_account.json', 'site' => 'Firebase'],
            ['name' => 'site_key', 'value' => '#', 'site' => 'Recaptcha'],
            ['name' => 'secret_key', 'value' => '#', 'site' => 'Recaptcha'],
        ]);
    }
}
