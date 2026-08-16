<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferralSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'apply_referral', 'value' => '1', 'user_type' => 'Driver'],
            ['id' => 2, 'name' => 'number_of_trips', 'value' => '5', 'user_type' => 'Driver'],
            ['id' => 3, 'name' => 'number_of_days', 'value' => '3', 'user_type' => 'Driver'],
            ['id' => 4, 'name' => 'currency_code', 'value' => 'USD', 'user_type' => 'Driver'],
            ['id' => 5, 'name' => 'referral_amount', 'value' => '10', 'user_type' => 'Driver'],
            ['id' => 6, 'name' => 'apply_referral', 'value' => '1', 'user_type' => 'Rider'],
            ['id' => 7, 'name' => 'number_of_trips', 'value' => '5', 'user_type' => 'Rider'],
            ['id' => 8, 'name' => 'number_of_days', 'value' => '3', 'user_type' => 'Rider'],
            ['id' => 9, 'name' => 'currency_code', 'value' => 'USD', 'user_type' => 'Rider'],
            ['id' => 10, 'name' => 'referral_amount', 'value' => '10', 'user_type' => 'Rider'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('referral_settings')->insert($chunk);
        }
    }
}
