<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyCorporateInvoicesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'corporate_account_id' => 1, 'invoice_number' => 'INV-3-00001', 'invoice_date' => '2026-05-24', 'due_date' => '2026-05-30', 'subtotal' => 50000.0, 'tax_amount' => 8000.0, 'total_amount' => 58000.0, 'status' => 'Draft', 'notes' => 'Please Pay the invoice in time', 'created_at' => '2026-05-23 19:37:55', 'updated_at' => '2026-05-23 19:37:55'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_corporate_invoices')->insert($chunk);
        }
    }
}
