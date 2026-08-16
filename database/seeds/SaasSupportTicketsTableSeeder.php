<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaasSupportTicketsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'created_by_type' => 'company', 'created_by_id' => 3, 'subject' => 'Action Required: Complete Your  Dashboard Setup', 'category' => 'technical', 'priority' => 'high', 'status' => 'open', 'description' => 'Test Support Ticket', 'assigned_admin_id' => null, 'last_message_at' => '2026-05-23 19:27:10', 'resolved_at' => null, 'created_at' => '2026-05-23 19:27:10', 'updated_at' => '2026-05-23 19:27:10'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('saas_support_tickets')->insert($chunk);
        }
    }
}
