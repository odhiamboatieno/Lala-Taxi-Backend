<?php

use Illuminate\Database\Seeder;

class ParcelSettingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('parcel_settings')->truncate();

        DB::table('parcel_settings')->insert([
            [
                'key' => 'platform_fee_percent',
                'value' => '10',
                'type' => 'int',
                'description' => 'Platform service fee percentage applied to parcel fares',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'request_timeout',
                'value' => '120',
                'type' => 'int',
                'description' => 'Time in seconds before a parcel request times out',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'signature_required',
                'value' => 'true',
                'type' => 'bool',
                'description' => 'Whether a signature is required for proof of delivery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'photo_required',
                'value' => 'true',
                'type' => 'bool',
                'description' => 'Whether a photo is required for proof of delivery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_weight_kg',
                'value' => '50',
                'type' => 'int',
                'description' => 'Maximum allowed package weight in kilograms',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'driver_commission_percent',
                'value' => '15',
                'type' => 'int',
                'description' => 'Driver commission percentage for parcel deliveries',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
