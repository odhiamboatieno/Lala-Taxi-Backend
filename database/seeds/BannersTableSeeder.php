<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannersTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Book LalaTaxi Van', 'type' => 'Ride', 'title' => 'Book LalaTaxi Van', 'subtitle' => 'Book Quick Large space Van now man', 'link' => '', 'image' => 'banner-1777679792.png', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Test', 'type' => 'Ride', 'title' => 'Book Smarter', 'subtitle' => 'Card, Mpesa, Airtel Money', 'link' => '', 'image' => 'banner-1777679986.png', 'status' => 'Active'],
            ['id' => 3, 'name' => 'Test', 'type' => 'Parcel', 'title' => 'Parcel Delivery', 'subtitle' => 'First Time Customer', 'link' => '', 'image' => 'banner-1777680559.png', 'status' => 'Active'],
            ['id' => 4, 'name' => 'Super Admin', 'type' => 'Parcel', 'title' => 'Airport Bag Back', 'subtitle' => 'Deliver Your Airport Rejected Parcels back Home', 'link' => '', 'image' => 'banner-1777680695.png', 'status' => 'Active'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('banners')->insert($chunk);
        }
    }
}
