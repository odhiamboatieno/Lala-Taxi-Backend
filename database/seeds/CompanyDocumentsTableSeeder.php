<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyDocumentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'document_id' => '', 'document' => 'https://taxi.lalacabs.co.ke/images/companies/4/insurance_1779838284.png', 'original_name' => 'ChatGPT Image May 25, 2026, 07_41_18 PM.png', 'mime_type' => 'image/png', 'file_size' => 1393669, 'expired_date' => null, 'status' => '1', 'rejection_reason' => null, 'uploaded_at' => '2026-05-26 21:31:24', 'reviewed_at' => '2026-06-05 10:10:17', 'reviewed_by' => 1],
            ['id' => 2, 'company_id' => 4, 'document_id' => '', 'document' => 'https://taxi.lalacabs.co.ke/images/companies/4/insurance_1779838381.png', 'original_name' => 'screencapture-drivemond-admin-codemond-admin-promotion-send-notification-2026-05-26-02_17_28.png', 'mime_type' => 'image/png', 'file_size' => 321180, 'expired_date' => null, 'status' => '1', 'rejection_reason' => null, 'uploaded_at' => '2026-05-26 21:33:01', 'reviewed_at' => '2026-05-26 22:34:04', 'reviewed_by' => 1],
            ['id' => 3, 'company_id' => 1, 'document_id' => '4', 'document' => 'https://taxi.lalacabs.co.ke/images/companies/1/insurance_1780661398.png', 'original_name' => null, 'mime_type' => null, 'file_size' => null, 'expired_date' => null, 'status' => '1', 'rejection_reason' => null, 'uploaded_at' => null, 'reviewed_at' => null, 'reviewed_by' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_documents')->insert($chunk);
        }
    }
}
