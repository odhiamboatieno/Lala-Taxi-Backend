<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyNetworkPartnersTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'partner_company_id' => 2, 'status' => 'Active', 'notes' => 'Birmigham Partner', 'created_at' => '2026-05-23 19:38:57', 'updated_at' => '2026-05-23 19:38:57'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_network_partners')->insert($chunk);
        }
    }
}
