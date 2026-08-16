<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayoutCredentialsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10002, 'preference_id' => '1', 'default' => 'yes', 'type' => 'BankTransfer', 'payout_id' => '123456789', 'created_at' => '2021-12-18 16:13:31', 'updated_at' => '2021-12-18 16:13:31'],
            ['id' => 2, 'user_id' => 10003, 'preference_id' => '2', 'default' => 'no', 'type' => 'Paypal', 'payout_id' => 'admin@admin.com', 'created_at' => '2022-11-12 07:07:08', 'updated_at' => '2026-05-10 11:03:29'],
            ['id' => 3, 'user_id' => 10003, 'preference_id' => '3', 'default' => 'yes', 'type' => 'BankTransfer', 'payout_id' => '127262626', 'created_at' => '2026-05-10 11:03:24', 'updated_at' => '2026-05-10 11:03:29'],
            ['id' => 4, 'user_id' => 10024, 'preference_id' => '4', 'default' => 'yes', 'type' => 'BankTransfer', 'payout_id' => '23332333', 'created_at' => '2026-05-26 22:45:51', 'updated_at' => '2026-05-26 22:45:51'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('payout_credentials')->insert($chunk);
        }
    }
}
