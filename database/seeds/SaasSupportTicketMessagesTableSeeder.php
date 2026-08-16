<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaasSupportTicketMessagesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'ticket_id' => 1, 'sender_type' => 'company', 'sender_id' => 3, 'message' => 'Test Support Ticket', 'read_at' => null, 'created_at' => '2026-05-23 19:27:10', 'updated_at' => '2026-05-23 19:27:10'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('saas_support_ticket_messages')->insert($chunk);
        }
    }
}
