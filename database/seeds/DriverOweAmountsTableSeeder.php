<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverOweAmountsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10002, 'amount' => 59.0, 'currency_code' => 'USD'],
            ['id' => 8, 'user_id' => 10003, 'amount' => 809.88, 'currency_code' => 'GBP'],
            ['id' => 9, 'user_id' => 10005, 'amount' => 0.0, 'currency_code' => 'USD'],
            ['id' => 11, 'user_id' => 10008, 'amount' => 0.0, 'currency_code' => 'USD'],
            ['id' => 12, 'user_id' => 10009, 'amount' => 10.0, 'currency_code' => 'USD'],
            ['id' => 13, 'user_id' => 10012, 'amount' => 0.0, 'currency_code' => 'USD'],
            ['id' => 14, 'user_id' => 10014, 'amount' => 40.5, 'currency_code' => 'USD'],
            ['id' => 15, 'user_id' => 10015, 'amount' => 0.0, 'currency_code' => 'ZAR'],
            ['id' => 16, 'user_id' => 10019, 'amount' => 184416.79, 'currency_code' => 'KES'],
            ['id' => 17, 'user_id' => 10021, 'amount' => 0.0, 'currency_code' => 'KES'],
            ['id' => 19, 'user_id' => 10023, 'amount' => 0.0, 'currency_code' => 'KES'],
            ['id' => 20, 'user_id' => 10024, 'amount' => 0.0, 'currency_code' => 'KES'],
            ['id' => 21, 'user_id' => 10026, 'amount' => 1192.46, 'currency_code' => 'AUD'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_owe_amounts')->insert($chunk);
        }
    }
}
