<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentsLangsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'documents_id' => 3, 'document_name' => 'بیمه', 'locale' => 'fa'],
            ['id' => 2, 'documents_id' => 3, 'document_name' => 'التأمين', 'locale' => 'ar'],
            ['id' => 3, 'documents_id' => 3, 'document_name' => 'Seguro', 'locale' => 'pt'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('documents_langs')->insert($chunk);
        }
    }
}
