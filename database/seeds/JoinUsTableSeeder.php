<?php

use Illuminate\Database\Seeder;

class JoinUsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('join_us')->delete();

        DB::table('join_us')->insert([
                ['name' => 'facebook', 'value' => 'https://www.facebook.com/'],
                ['name' => 'google_plus', 'value' => ''],
                ['name' => 'twitter', 'value' => 'https://twitter.com/'],
                ['name' => 'linkedin', 'value' => 'https://www.linkedin.com/'],
                ['name' => 'pinterest', 'value' => 'https://in.pinterest.com/'],
                ['name' => 'youtube', 'value' => 'https://www.youtube.com/'],
                ['name' => 'instagram', 'value' => 'https://www.instagram.com/'],
                ['name' => 'app_store_rider', 'value' => 'https://itunes.apple.com/'],
                ['name' => 'app_store_driver', 'value' => 'https://itunes.apple.com/'],
                ['name' => 'play_store_rider', 'value' => 'https://play.google.com/'],
                ['name' => 'play_store_driver', 'value' => 'https://play.google.com/'],
            ]);
    }
}
