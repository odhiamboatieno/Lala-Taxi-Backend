<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 12, 'trip_id' => 41, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 13, 'trip_id' => 43, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 14, 'trip_id' => 44, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 15, 'trip_id' => 45, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => '', 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Paid'],
            ['id' => 16, 'trip_id' => 47, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 17, 'trip_id' => 46, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 18, 'trip_id' => 48, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => '', 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Paid'],
            ['id' => 19, 'trip_id' => 49, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 20, 'trip_id' => 50, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 21, 'trip_id' => 51, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 22, 'trip_id' => 52, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 23, 'trip_id' => 53, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 24, 'trip_id' => 54, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 25, 'trip_id' => 55, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 26, 'trip_id' => 56, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 27, 'trip_id' => 57, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 28, 'trip_id' => 58, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 29, 'trip_id' => 59, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 30, 'trip_id' => 61, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 31, 'trip_id' => 67, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 32, 'trip_id' => 68, 'correlation_id' => '', 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 33, 'trip_id' => 69, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 34, 'trip_id' => 70, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 35, 'trip_id' => 71, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 36, 'trip_id' => 72, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 37, 'trip_id' => 73, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 38, 'trip_id' => 74, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 39, 'trip_id' => 75, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 40, 'trip_id' => 76, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 41, 'trip_id' => 77, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 42, 'trip_id' => 78, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 43, 'trip_id' => 79, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 44, 'trip_id' => 80, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 45, 'trip_id' => 81, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 46, 'trip_id' => 82, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 47, 'trip_id' => 83, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 48, 'trip_id' => 84, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 49, 'trip_id' => 85, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 50, 'trip_id' => 86, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 51, 'trip_id' => 87, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 52, 'trip_id' => 89, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 53, 'trip_id' => 91, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 54, 'trip_id' => 92, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 55, 'trip_id' => 93, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 56, 'trip_id' => 94, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 57, 'trip_id' => 95, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 58, 'trip_id' => 96, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 59, 'trip_id' => 97, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 60, 'trip_id' => 98, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 61, 'trip_id' => 99, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 62, 'trip_id' => 100, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
            ['id' => 63, 'trip_id' => 101, 'correlation_id' => null, 'admin_transaction_id' => null, 'driver_transaction_id' => null, 'admin_payout_status' => 'Pending', 'driver_payout_status' => 'Pending'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('payment')->insert($chunk);
        }
    }
}
