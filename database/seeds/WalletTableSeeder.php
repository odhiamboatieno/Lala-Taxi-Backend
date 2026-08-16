<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['user_id' => 10001, 'amount' => 1367.6, 'currency_code' => 'INR', 'paykey' => null],
            ['user_id' => 10004, 'amount' => 8107.0, 'currency_code' => 'USD', 'paykey' => 'RFH19NC7RX'],
            ['user_id' => 10010, 'amount' => 17800.0, 'currency_code' => 'KES', 'paykey' => 'pi_3TezB2JbZQ9xXEmX0ZRiW34H'],
            ['user_id' => 10019, 'amount' => 99999.99, 'currency_code' => 'KES', 'paykey' => 'pi_3TeHdBJbZQ9xXEmX1KqB9VsJ'],
            ['user_id' => 10025, 'amount' => 2000.0, 'currency_code' => 'USD', 'paykey' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('wallet')->insert($chunk);
        }
    }
}
