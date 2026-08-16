<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaytmPaymentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10033, 'payment_id' => '6485e550883fa', 'amount' => 300.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-11 15:16:32', 'updated_at' => null],
            ['id' => 2, 'user_id' => 10033, 'payment_id' => '6485e683a9eb6', 'amount' => 100.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-11 15:21:39', 'updated_at' => null],
            ['id' => 3, 'user_id' => 10033, 'payment_id' => '6485e68ed6f4a', 'amount' => 5454.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-11 15:21:50', 'updated_at' => null],
            ['id' => 4, 'user_id' => 10029, 'payment_id' => '6485ee8eda470', 'amount' => 20.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 15:55:58', 'updated_at' => null],
            ['id' => 5, 'user_id' => 10029, 'payment_id' => '6485efbfae104', 'amount' => 65.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 16:01:03', 'updated_at' => null],
            ['id' => 6, 'user_id' => 10029, 'payment_id' => '6485eff04b44d', 'amount' => 39.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 16:01:52', 'updated_at' => null],
            ['id' => 7, 'user_id' => 10029, 'payment_id' => '6485f0248e783', 'amount' => 60.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 16:02:44', 'updated_at' => null],
            ['id' => 8, 'user_id' => 10029, 'payment_id' => '6485f98d38647', 'amount' => 20.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 16:42:53', 'updated_at' => null],
            ['id' => 9, 'user_id' => 10029, 'payment_id' => '6485fc7d5b840', 'amount' => 68.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 16:55:25', 'updated_at' => null],
            ['id' => 10, 'user_id' => 10029, 'payment_id' => '6485fd20bf3f6', 'amount' => 50.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-11 16:58:08', 'updated_at' => null],
            ['id' => 11, 'user_id' => 10035, 'payment_id' => '64860b3f9b38a', 'amount' => 10.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-11 17:58:23', 'updated_at' => null],
            ['id' => 12, 'user_id' => 10035, 'payment_id' => '64860b6c3ab7f', 'amount' => 500.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-11 17:59:08', 'updated_at' => null],
            ['id' => 13, 'user_id' => 10035, 'payment_id' => '64860bad21379', 'amount' => 50.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-11 18:00:13', 'updated_at' => null],
            ['id' => 14, 'user_id' => 10002, 'payment_id' => '648c6dbb84fd5', 'amount' => 1.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-16 14:12:11', 'updated_at' => null],
            ['id' => 15, 'user_id' => 10002, 'payment_id' => '648c716639212', 'amount' => 100.0, 'pay_for' => 'pay_to_admin', 'status' => 'Pending', 'created_at' => '2023-06-16 14:27:50', 'updated_at' => null],
            ['id' => 16, 'user_id' => 10004, 'payment_id' => '648c79931e147', 'amount' => 100.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-16 15:02:43', 'updated_at' => null],
            ['id' => 17, 'user_id' => 10004, 'payment_id' => '648c7a0243a28', 'amount' => 255.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-16 15:04:34', 'updated_at' => null],
            ['id' => 18, 'user_id' => 10004, 'payment_id' => '648c7a19da6d9', 'amount' => 200.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-16 15:04:57', 'updated_at' => null],
            ['id' => 19, 'user_id' => 10004, 'payment_id' => '648c7a25002c1', 'amount' => 555.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-16 15:05:09', 'updated_at' => null],
            ['id' => 20, 'user_id' => 10004, 'payment_id' => '648cd6532bbfb', 'amount' => 10.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2023-06-16 21:38:27', 'updated_at' => null],
            ['id' => 21, 'user_id' => 10010, 'payment_id' => '6a046497ad400', 'amount' => 1000.0, 'pay_for' => 'wallet', 'status' => 'Pending', 'created_at' => '2026-05-13 11:46:31', 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('paytm_payments')->insert($chunk);
        }
    }
}
