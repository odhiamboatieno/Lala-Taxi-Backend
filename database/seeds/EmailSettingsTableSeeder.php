<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'driver', 'value' => 'smtp'],
            ['id' => 2, 'name' => 'host', 'value' => 'smtp.gmail.com'],
            ['id' => 3, 'name' => 'port', 'value' => '25'],
            ['id' => 4, 'name' => 'from_address', 'value' => 'newtaxi@gmail.com'],
            ['id' => 5, 'name' => 'from_name', 'value' => 'newtaxi'],
            ['id' => 6, 'name' => 'encryption', 'value' => 'tls'],
            ['id' => 7, 'name' => 'username', 'value' => 'newtaxi@gmail.com'],
            ['id' => 8, 'name' => 'password', 'value' => 'newtaxi'],
            ['id' => 9, 'name' => 'domain', 'value' => 'sandboxcc51fc42882e46ccbffd90316d4731e7.mailgun.org'],
            ['id' => 10, 'name' => 'secret', 'value' => 'key-3160b23116332e595b861f60d77fa720'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('email_settings')->insert($chunk);
        }
    }
}
