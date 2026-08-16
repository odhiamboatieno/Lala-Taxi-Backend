<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterOptionsTranslationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'filter_option_id' => 1, 'name' => 'تفضل الفرسان الإناث فقط', 'locale' => 'ar', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 2, 'filter_option_id' => 1, 'name' => 'Preferir solo mujeres jinetes', 'locale' => 'es', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 3, 'filter_option_id' => 1, 'name' => 'فقط زن سوارکار را ترجیح دهید', 'locale' => 'fa', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 4, 'filter_option_id' => 1, 'name' => 'Prefira apenas mulheres', 'locale' => 'pt', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 5, 'filter_option_id' => 2, 'name' => 'تفضل الوصول للمعاقين', 'locale' => 'ar', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 6, 'filter_option_id' => 2, 'name' => 'Prefiero la accesibilidad para discapacitados', 'locale' => 'es', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 7, 'filter_option_id' => 2, 'name' => 'دسترسی معلولیت را ترجیح دهید', 'locale' => 'fa', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 8, 'filter_option_id' => 2, 'name' => 'Prefira acessibilidade para deficientes', 'locale' => 'pt', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 9, 'filter_option_id' => 3, 'name' => 'تفضل الوصول إلى مقعد الطفل', 'locale' => 'ar', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 10, 'filter_option_id' => 3, 'name' => 'Prefiero la accesibilidad del asiento para niños', 'locale' => 'es', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 11, 'filter_option_id' => 3, 'name' => 'دسترسی صندلی کودک را ترجیح دهید', 'locale' => 'fa', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 12, 'filter_option_id' => 3, 'name' => 'Acessibilidade preferencial para cadeirinha de criança', 'locale' => 'pt', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 13, 'filter_option_id' => 4, 'name' => 'تفضل السائقات الإناث فقط', 'locale' => 'ar', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 14, 'filter_option_id' => 4, 'name' => 'Prefiero solo mujeres conductoras', 'locale' => 'es', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 15, 'filter_option_id' => 4, 'name' => 'فقط رانندگان زن را ترجیح دهید', 'locale' => 'fa', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 16, 'filter_option_id' => 4, 'name' => 'Prefira apenas motoristas', 'locale' => 'pt', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('filter_options_translations')->insert($chunk);
        }
    }
}
