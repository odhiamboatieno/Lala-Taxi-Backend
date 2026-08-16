<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyCorporateAccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'account_name' => 'Querysoft Technologies', 'contact_name' => 'Onono Gordon', 'contact_email' => 'info@querysofts.com', 'contact_phone' => '0703803051', 'billing_cycle' => 'weekly', 'credit_limit' => 50000.0, 'status' => 'Active', 'notes' => 'Test Corporate Account', 'created_at' => '2026-05-23 19:36:37', 'updated_at' => '2026-05-23 19:36:37'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_corporate_accounts')->insert($chunk);
        }
    }
}
