<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualBookingDraftsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => null, 'dispatcher_type' => 'admin', 'dispatcher_id' => null, 'name' => 'Manual Booking', 'payload' => '{"booking_experience_tab":"later","booking_mode":"asap","dispatch_mode":"auto","pickup_location":"Kitengela, Kenya","pickup_latitude":"-1.4791088","pickup_longitude":"36.9583914","drop_location":"400 Tom Mboya St, Nairobi, Kenya","drop_latitude":"-1.2849541","drop_longitude":"36.8260631","via_location":"","via_latitude":"","via_longitude":"","date_time":"2026-05-25 19:22","vehicle_type":"1","priority":"normal","first_name":"","last_name":"","email":"","mobile_number":"","country_code":"254","country_id":"110","gender":"","filter":[],"dispatcher_note":"","driver_note":"","payment_method":"Card","customer_type":"","company_name":"","company_phone":"","flight_number":"","airline":"","terminal":"","airport_direction":"","return_trip":{"enabled":"true","date":null,"time":null,"pickup_location":null,"drop_location":null},"recurring":{"enabled":"true","pattern":"weekly","occurrences":"4"}}', 'status' => 'Active', 'created_at' => '2026-05-25 14:27:17', 'updated_at' => '2026-05-25 14:27:17'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('manual_booking_drafts')->insert($chunk);
        }
    }
}
