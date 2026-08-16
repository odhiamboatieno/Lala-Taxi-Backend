<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiderLocationTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10001, 'home' => 'Nairobi, Kenya', 'work' => 'Mombasa, Kenya', 'home_latitude' => '-1.2920659', 'home_longitude' => '36.8219462', 'work_latitude' => '-4.0434771', 'work_longitude' => '39.6682065', 'latitude' => '19.14430794923723', 'longitude' => '72.83747524023056'],
            ['id' => 2, 'user_id' => 10004, 'home' => 'PR35+G42, Ngong Rd, Nairobi, Kenya', 'work' => 'Murang\'a, Kenya', 'home_latitude' => '-1.2962359', 'home_longitude' => '36.8077602', 'work_latitude' => '-0.7236857999999999', 'work_longitude' => '37.1606968', 'latitude' => '43.07034264312149', 'longitude' => '47.338916435837746'],
            ['id' => 3, 'user_id' => 10016, 'home' => '', 'work' => '', 'home_latitude' => '', 'home_longitude' => '', 'work_latitude' => '', 'work_longitude' => '', 'latitude' => '0.91441172850229', 'longitude' => '36.25840052962303'],
            ['id' => 4, 'user_id' => 10017, 'home' => 'Ronald Ngala St, Nairobi, Kenya', 'work' => 'Garden Estate Rd, Nairobi, Kenya', 'home_latitude' => '-1.2859871', 'home_longitude' => '36.8275693', 'work_latitude' => '-1.2179226', 'work_longitude' => '36.85334', 'latitude' => '', 'longitude' => ''],
            ['id' => 5, 'user_id' => 10018, 'home' => 'EPZ Plaza, EPZ Rd, Kitengela, Kenya', 'work' => 'Westlands, Nairobi, Kenya', 'home_latitude' => '-1.4729134', 'home_longitude' => '36.96762020000001', 'work_latitude' => '-1.2675001', 'work_longitude' => '36.812022', 'latitude' => '', 'longitude' => ''],
            ['id' => 6, 'user_id' => 10010, 'home' => '00100 Mamlaka Rd, Nairobi, Kenya', 'work' => 'Chiromo Ln, Nairobi, Kenya', 'home_latitude' => '-1.2855694477520196', 'home_longitude' => '36.812899969518185', 'work_latitude' => '-1.2695603', 'work_longitude' => '36.8096978', 'latitude' => '', 'longitude' => ''],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('rider_location')->insert($chunk);
        }
    }
}
