<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'English', 'value' => 'en', 'status' => 'Active', 'default_language' => '1'],
            ['id' => 2, 'name' => 'Persian', 'value' => 'fa', 'status' => 'Active', 'default_language' => '0'],
            ['id' => 3, 'name' => 'Arabic', 'value' => 'ar', 'status' => 'Active', 'default_language' => '0'],
            ['id' => 4, 'name' => 'Spanish', 'value' => 'es', 'status' => 'Active', 'default_language' => '0'],
            ['id' => 5, 'name' => 'Português', 'value' => 'pt', 'status' => 'Active', 'default_language' => '0'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('language')->insert($chunk);
        }
    }
}
