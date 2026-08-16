<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverOweAmountPaymentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10003, 'transaction_id' => 'pi_3L1fHDJbZQ9xXEmX18Ej0TVF', 'amount' => 1.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'user_id' => 10002, 'transaction_id' => 'RFH495SWG4', 'amount' => 1.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'user_id' => 10002, 'transaction_id' => 'rave648d1d4279a9a', 'amount' => 20.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'user_id' => 10002, 'transaction_id' => 'rave648d1d4279a9a', 'amount' => 20.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 5, 'user_id' => 10002, 'transaction_id' => 'rave648d301ceab76', 'amount' => 9.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 6, 'user_id' => 10002, 'transaction_id' => 'rave648d335e0c874', 'amount' => 5.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 7, 'user_id' => 10002, 'transaction_id' => 'RFH29FKJ3Q', 'amount' => 1.0, 'currency_code' => 'USD', 'status' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 8, 'user_id' => 10019, 'transaction_id' => 'pi_3TZGyzJbZQ9xXEmX1sBPo8KY', 'amount' => 9868.0, 'currency_code' => 'KES', 'status' => 1, 'created_at' => null, 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_owe_amount_payments')->insert($chunk);
        }
    }
}
