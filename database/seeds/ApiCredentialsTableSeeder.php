<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiCredentialsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'key', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'GoogleMap'],
            ['id' => 2, 'name' => 'server_key', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'GoogleMap'],
            ['id' => 3, 'name' => 'sid', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'Twillo'],
            ['id' => 4, 'name' => 'token', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'Twillo'],
            ['id' => 5, 'name' => 'service_sid', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'Twillo'],
            ['id' => 6, 'name' => 'from', 'value' => 'XXXXXXXXXXX', 'site' => 'Twillo'],
            ['id' => 7, 'name' => 'server_key', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'FCM'],
            ['id' => 8, 'name' => 'sender_id', 'value' => '884120994848', 'site' => 'FCM'],
            ['id' => 9, 'name' => 'client_id', 'value' => '#', 'site' => 'Facebook'],
            ['id' => 10, 'name' => 'client_secret', 'value' => '#', 'site' => 'Facebook'],
            ['id' => 11, 'name' => 'client_id', 'value' => '#', 'site' => 'Google'],
            ['id' => 12, 'name' => 'client_secret', 'value' => '#', 'site' => 'Google'],
            ['id' => 13, 'name' => 'sinch_key', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'Sinch'],
            ['id' => 14, 'name' => 'sinch_secret_key', 'value' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'site' => 'Sinch'],
            ['id' => 15, 'name' => 'service_id', 'value' => '#', 'site' => 'Apple'],
            ['id' => 16, 'name' => 'team_id', 'value' => '#', 'site' => 'Apple'],
            ['id' => 17, 'name' => 'key_id', 'value' => '#', 'site' => 'Apple'],
            ['id' => 18, 'name' => 'key_file', 'value' => '/public/key.txt', 'site' => 'Apple'],
            ['id' => 19, 'name' => 'database_url', 'value' => 'https://prime-taxi-dcd6e-default-rtdb.firebaseio.com/', 'site' => 'Firebase'],
            ['id' => 20, 'name' => 'service_account', 'value' => '/resources/credentials//service_account.json', 'site' => 'Firebase'],
            ['id' => 21, 'name' => 'site_key', 'value' => '6LfJKvoUAAAAAFe8tYNw85mY5Tur-_A4tp865bL3', 'site' => 'Recaptcha'],
            ['id' => 22, 'name' => 'secret_key', 'value' => '6LfJKvoUAAAAABh-36UFZrtp-_bZEtdgcg0kwWhy', 'site' => 'Recaptcha'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('api_credentials')->insert($chunk);
        }
    }
}
