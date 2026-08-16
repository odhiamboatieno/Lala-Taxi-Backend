<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverAddressTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10002, 'address_line1' => 'Nairobi, Kennya', 'address_line2' => '', 'city' => 'Nairobi', 'state' => 'Kenya', 'postal_code' => '00100'],
            ['id' => 2, 'user_id' => 10003, 'address_line1' => 'Nairobi', 'address_line2' => '00100', 'city' => 'Nairobi', 'state' => 'Nairobi', 'postal_code' => '00100'],
            ['id' => 3, 'user_id' => 10005, 'address_line1' => '', 'address_line2' => '', 'city' => 'Nairobi', 'state' => '', 'postal_code' => ''],
            ['id' => 4, 'user_id' => 10007, 'address_line1' => '', 'address_line2' => '', 'city' => 'Nairobi', 'state' => '', 'postal_code' => ''],
            ['id' => 5, 'user_id' => 10008, 'address_line1' => '', 'address_line2' => '', 'city' => 'Nairobi', 'state' => '', 'postal_code' => ''],
            ['id' => 6, 'user_id' => 10009, 'address_line1' => 'Nairobi', 'address_line2' => 'Donhnom', 'city' => 'Nairobi', 'state' => 'kenya', 'postal_code' => '00100'],
            ['id' => 7, 'user_id' => 10012, 'address_line1' => '', 'address_line2' => '', 'city' => 'Johannesburg', 'state' => '', 'postal_code' => ''],
            ['id' => 8, 'user_id' => 10014, 'address_line1' => '', 'address_line2' => '', 'city' => 'Johannesburg South', 'state' => '', 'postal_code' => ''],
            ['id' => 9, 'user_id' => 10015, 'address_line1' => 'Admin', 'address_line2' => '6yjbdd', 'city' => 'jhb', 'state' => 'Sddd', 'postal_code' => '161y'],
            ['id' => 10, 'user_id' => 10019, 'address_line1' => 'Dohnhom', 'address_line2' => '-', 'city' => 'Nairobi', 'state' => 'Nairobi', 'postal_code' => 'Nairobi'],
            ['id' => 11, 'user_id' => 10021, 'address_line1' => 'Kitengela', 'address_line2' => 'Milimani', 'city' => 'Nairobi', 'state' => 'Kenya', 'postal_code' => 'Kenya'],
            ['id' => 12, 'user_id' => 10022, 'address_line1' => 'gs', 'address_line2' => 'gd', 'city' => 'Nairobi', 'state' => 'Ksu', 'postal_code' => 'Ksu'],
            ['id' => 13, 'user_id' => 10023, 'address_line1' => 'Nairobi', 'address_line2' => '-', 'city' => 'Nairobi', 'state' => 'Nairobi', 'postal_code' => 'Nairobi'],
            ['id' => 14, 'user_id' => 10024, 'address_line1' => 'Nairobi', 'address_line2' => '-', 'city' => 'Nairobi', 'state' => 'Nairobi', 'postal_code' => 'Nairobi'],
            ['id' => 15, 'user_id' => 10026, 'address_line1' => 'Imara Daima', 'address_line2' => '-', 'city' => 'Nairobi', 'state' => 'Kenya', 'postal_code' => 'Kenya'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_address')->insert($chunk);
        }
    }
}
