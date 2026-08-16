<?php

use Illuminate\Database\Seeder;
use App\Models\ParcelCategory;
use App\Models\CarType;

class ParcelFareSeeder extends Seeder
{
    public function run()
    {
        DB::table('parcel_fares')->truncate();

        $categories = ParcelCategory::where('is_active', true)->get();
        $carTypes = CarType::where('status', 'Active')->get();

        $insertData = [];

        foreach ($categories as $category) {
            foreach ($carTypes as $carType) {
                $perKmRate = $carType->price_per_km ?? 15.00;

                $insertData[] = [
                    'parcel_category_id' => $category->id,
                    'car_type_id' => $carType->id,
                    'location_id' => null,
                    'base_fare' => $category->base_fare,
                    'per_km_rate' => $perKmRate,
                    'min_fare' => $category->base_fare,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($insertData)) {
            DB::table('parcel_fares')->insert($insertData);
        }
    }
}
